<?php


namespace Paynl\QR;


use Paynl\Error\Error;
use Paynl\QR\Error\InvalidArgument;

class DonateUUID
{
    private static $prefix = 'd';
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

        $amount       = round($parameters['amount']);
        $amountLength = strlen($amount);
        $serviceId    = preg_replace('/\D/', '', $parameters['serviceId']);
        $reference    = str_pad(strtolower($parameters['reference']), 16, self::$padChar, STR_PAD_LEFT);

        $UUIDData = self::$prefix . $amountLength . $amount . $serviceId;

        $hash = hash_hmac(UUID::HASH_METHOD, $UUIDData, $parameters['secret']);

        $UUID = self::$prefix . str_pad($amountLength . $amount, 7, $hash, STR_PAD_RIGHT);
        $UUID .= $serviceId . $reference;

        return UUID::formatUUID($UUID);
    }

    private static function validateParameters(array &$parameters)
    {
        if ( ! isset($parameters['serviceId']) || ! isset($parameters['secret']) || ! isset($parameters['amount']) || ! isset($parameters['reference'])) {
            throw new InvalidArgument("Invalid arguments; required: serviceId, secret, amount, reference");
        }

        UUID::validateServiceId($parameters['serviceId']);
        UUID::validateSecret($parameters['secret']);
        UUID::validateAmount($parameters['amount']);

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
     * @return array Array with serviceId, reference and amount
     * @throws Error
     */
    public static function decode(array $parameters)
    {
        UUID::validateSecret($parameters['secret']);
        $isValid = self::validate($parameters);
        if ( ! $isValid) {
            throw new Error('Incorrect signature');
        }

        $uuidData = preg_replace('/[^0-9a-z]/i', '', $parameters['uuid']);

        $amountLength = substr($uuidData, 1, 1);
        $amount       = substr($uuidData, 2, $amountLength);

        $serviceId = substr($uuidData, 8, 8);
        $serviceId = "SL-" . substr($serviceId, 0, 4) . '-' . substr($serviceId, 4, 4);

        $reference = substr($uuidData, 16);

        return array(
            'amount'    => $amount,
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
        $uuidData = preg_replace('/[^0-9a-f]/i', '', $parameters['uuid']);

        $amountLength = substr($uuidData, 1, 1);
        $amount       = substr($uuidData, 2, $amountLength);
        $serviceId    = substr($uuidData, 8, 8);

        $strChecksumUUID = substr($uuidData, ($amountLength + 2), (6 - $amountLength));
        $hash            = hash_hmac(UUID::HASH_METHOD, self::$prefix . $amountLength . $amount . $serviceId,
            $parameters['secret']);

        return substr($hash, 0, strlen($strChecksumUUID)) == $strChecksumUUID;
    }
}