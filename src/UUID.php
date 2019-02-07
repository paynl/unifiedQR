<?php
/**
 * Created by PhpStorm.
 * User: jorn
 * Date: 6-2-19
 * Time: 15:37
 */

namespace Paynl\QR;

use Paynl\QR\Error\Error;
use Paynl\QR\Error\InvalidArgument;

class UUID
{
    const QR_TYPE_DYNAMIC = 0;
    const QR_TYPE_STATIC = 1;
    const QR_TYPE_TRANSACTION = 2;
    const QR_TYPE_DONATE = 3;

    const REFERENCE_TYPE_STRING = 0;
    const REFERENCE_TYPE_HEX = 1;

    const HASH_METHOD = 'sha256';

    /**
     * @param int $type
     * @param array $parameters
     *
     * @return string
     * @throws Error
     */
    public static function encode(int $type, Array $parameters)
    {
        switch ($type) {
            case self::QR_TYPE_DYNAMIC:
                return DynamicUUID::encode($parameters);
                break;
            case self::QR_TYPE_STATIC:
                return StaticUUID::encode($parameters);
                break;
            case self::QR_TYPE_TRANSACTION:
                return TransactionUUID::encode($parameters);
                break;
            case self::QR_TYPE_DONATE:
                return DonateUUID::encode($parameters);
                break;
            default:
                throw new Error("Invalid QR Type");
        }
    }

    public static function decode(array $parameters)
    {
        if ( ! isset($parameters['uuid']) || strlen($parameters['uuid']) != 36) {
            throw new InvalidArgument("Invalid UUID");
        }

        if ( ! isset($parrameters['type'])) {
            $type = self::getTypeFromUUID($parameters['uuid']);
        } else {
            $type = $parameters['type'];
        }

        switch ($type) {
            case self::QR_TYPE_DYNAMIC:
                return DynamicUUID::decode($parameters);
                break;
            case self::QR_TYPE_STATIC:
                return StaticUUID::decode($parameters);
                break;
            case self::QR_TYPE_TRANSACTION:
                return TransactionUUID::decode($parameters);
                break;
            case self::QR_TYPE_DONATE:
                return DonateUUID::decode($parameters);
                break;
            default:
                throw new InvalidArgument('Invalid Type');
        }
    }

    private static function getTypeFromUUID($UUID)
    {
        $firstChar = substr($UUID, 0, 1);
        switch ($firstChar) {
            case 'a':
                return self::QR_TYPE_TRANSACTION;
                break;
            case 'b':
                return self::QR_TYPE_DYNAMIC;
                break;
            case 'd':
                return self::QR_TYPE_DONATE;
                break;
            default:
                if ( ! ctype_digit($firstChar)) {
                    throw new Error('No valid type detected');
                }

                return self::QR_TYPE_STATIC;
        }
    }

    public static function asciiToHex($ascii)
    {
        $hex = '';
        for ($i = 0; $i < strlen($ascii); $i++) {
            $byte = strtoupper(dechex(ord($ascii{$i})));
            $byte = str_repeat('0', 2 - strlen($byte)) . $byte;
            $hex  .= $byte . "";
        }

        return $hex;
    }

    public static function hexToString($hex)
    {
        return hex2bin($hex);
    }

    public static function validateSecret($strSecret)
    {
        if ( ! preg_match('/^[0-9a-f]{40}$/i', $strSecret)) {
            throw new Error('Invalid secret');
        }
    }

    public static function validateServiceId($strServiceId)
    {
        if ( ! preg_match('/^SL-[0-9]{4}-[0-9]{4}$/', $strServiceId)) {
            throw new Error('Invalid service ID');
        }
    }

    public static function validateReferenceString($strReference)
    {
        if ( ! preg_match('/^[0-9a-zA-Z]{0,8}$/i', $strReference)) {
            throw new Error('Invalid reference: only alphanumeric chars are allowed, up to 8 chars long');
        }
    }

    public static function validateReferenceHex($strReference)
    {
        if ( ! preg_match('/^[0-9a-f]{0,16}$/i', $strReference)) {
            throw new Error('Invalid reference: only alphanumeric chars are allowed, up to 16 chars long');
        }
    }

    public static function validatePadChar($strPadChar)
    {
        if ( ! preg_match('/^[a-z0-9]{1}$/i', $strPadChar)) {
            throw new Error('Invalid pad char');
        }
    }

    public static function validateAmount($amount)
    {
        if ( ! is_int($amount) || $amount < 0 || $amount > 999999) {
            throw new Error('Invalid amount');
        }
    }

    public static function validateBrandlock($brandlock)
    {
        if ( ! preg_match('/^[0-9]{2}$/', $brandlock) && ! (int)$brandlock < 0 && ! (int)$brandlock > 99) {
            throw new Error('Invalid brandlock');
        }
    }

    /**
     * @param string $UUID
     *
     * @return string
     */
    public static function formatUUID($UUID)
    {
        return sprintf('%08s-%04s-%04s-%04s-%12s', substr($UUID, 0, 8), substr($UUID, 8, 4), substr($UUID, 12, 4),
            substr($UUID, 16, 4), substr($UUID, 20, 12));
    }
}