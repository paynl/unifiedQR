<?php


namespace Paynl\QR;


use Paynl\QR\Error\Error;

class StaticUUID
{

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
        $reference = $parameters['reference'];
        if ($parameters['referenceType'] == UUID::REFERENCE_TYPE_STRING) {
            $reference = UUID::asciiToHex($reference);
        }

        $amount       = round($parameters['amount']);
        $serviceId    = preg_replace('/\D/', '', $parameters['serviceId']);
        $amountLength = strlen($amount);
        $secret       = $parameters['secret'];

        $UUIDData  = $amountLength . $amount . $serviceId;
        $reference = str_pad(strtolower($reference), 16, self::$padChar, STR_PAD_LEFT);

        $hash = hash_hmac(UUID::HASH_METHOD, $UUIDData, $secret);

        $UUID = str_pad($amountLength . $amount, 8, $hash, STR_PAD_RIGHT);
        $UUID .= $serviceId . $reference;

        return UUID::formatUUID($UUID);
    }

    /**
     * @param array $parameters
     *
     * @throws Error
     */
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

        $isValid = self::validate($parameters);
        if ( ! $isValid) {
            throw new Error('Incorrect signature');
        }


        $uuid = preg_replace('/[^0-9a-z]/i', '', $parameters['uuid']);

        $amountLength = substr($uuid, 0, 1);
        $amount       = substr($uuid, 1, $amountLength);

        $serviceId = substr($uuid, 8, 8);
        $serviceId = "SL-" . substr($serviceId, 0, 4) . '-' . substr($serviceId, 4, 4);

        $reference = substr($uuid, 16);

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
    public static function validate(array $parameters)
    {
        $uuid = preg_replace('/[^0-9a-f]/i', '', $parameters['uuid']);
        if ( ! ctype_digit(substr($uuid, 0, 1))) {
            return false;
        }

        $amountLength = substr($uuid, 0, 1);
        $amount       = substr($uuid, 1, $amountLength);

        $serviceId       = substr($uuid, 8, 8);
        $strChecksumUUID = substr($uuid, ($amountLength + 1), (7 - $amountLength));
        $hash            = hash_hmac(UUID::HASH_METHOD, $amountLength . $amount . $serviceId, $parameters['secret']);

        return substr($hash, 0, strlen($strChecksumUUID)) == $strChecksumUUID;
    }
}