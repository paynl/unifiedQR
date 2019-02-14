<?php


namespace Paynl\QR;

use Paynl\QR\Error\Error;
use Paynl\QR\Error\InvalidArgument;

class DynamicUUID
{
    private static $prefix = 'b';
    private static $padChar = '0';

    /**
     * Generate a UUID
     *
     * @param array $parameters
     *
     * @return string The UUID
     */
    public static function encode(array $parameters)
    {
        self::validateParameters($parameters);

        if ($parameters['referenceType'] == UUID::REFERENCE_TYPE_STRING) {
            $parameters['reference'] = UUID::asciiToHex($parameters['reference']);
        }

        $serviceId = preg_replace('/\D/', '', $parameters['serviceId']);
        $reference = str_pad(strtolower($parameters['reference']), 16, self::$padChar, STR_PAD_LEFT);
        $UUIDData  = $serviceId . $reference;

        $hash = hash_hmac(UUID::HASH_METHOD, $UUIDData, $parameters['secret']);

        $UUID = self::$prefix . substr($hash, 0, 7) . $UUIDData;

        return UUID::formatUUID($UUID);
    }

    private static function validateParameters(array &$parameters)
    {
        if ( ! isset($parameters['serviceId']) || ! isset($parameters['secret']) || ! isset($parameters['reference'])) {
            throw new InvalidArgument("Invalid arguments; required: serviceId, secret, reference");
        }

        UUID::validateServiceId($parameters['serviceId']);
        UUID::validateSecret($parameters['secret']);

        if ( ! isset($parameters['referenceType'])) {
            $parameters['referenceType'] = UUID::REFERENCE_TYPE_STRING;
        }

        if ($parameters['referenceType'] == UUID::REFERENCE_TYPE_STRING) {
            UUID::validateReferenceString($parameters['reference']);
        } else {
            UUID::validateReferenceHex($parameters['reference']);
        }
    }

    /**
     * Decode a UUID
     *
     * @param array $parameters
     *
     * @return array Array with serviceId and reference
     * @throws Error
     */
    public static function decode(array $parameters)
    {
        UUID::validateSecret($parameters['secret']);

        $isValid = self::validate($parameters);
        if ( ! $isValid) {
            throw new Error('Incorrect signature');
        }

        $uuid = $parameters['uuid'];

        $uuidData = preg_replace('/[^0-9a-z]/i', '', $uuid);
        $uuidData = substr($uuidData, 8);

        $serviceId = "SL-" . substr($uuidData, 0, 4) . '-' . substr($uuidData, 4, 4);
        $reference = substr($uuidData, 8);

        $reference = ltrim($reference, self::$padChar);

        return array(
            'serviceId' => $serviceId,
            'reference' => $reference
        );
    }

    /**
     * Validate a UUID with supplied secret
     *
     * @param array $parameters
     *
     * @return bool
     */
    public static function validate($parameters)
    {
        $uuid     = preg_replace('/[^0-9a-z]/i', '', $parameters['uuid']);
        $uuidData = substr($uuid, 8);

        $hash     = hash_hmac(UUID::HASH_METHOD, $uuidData, $parameters['secret']);
        $checksum = self::$prefix . substr($hash, 0, 7);

        return $checksum == substr($uuid, 0, 8);
    }
}
