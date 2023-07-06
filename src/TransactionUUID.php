<?php

namespace Paynl\QR;

use Paynl\QR\Error\Error;
use Paynl\QR\Error\InvalidArgument;

class TransactionUUID
{
    private static $prefix = 'a';

    /**
     * Generate a UUID
     *
     * @param $parameters
     * @return string
     * @throws Error
     * @throws InvalidArgument
     */
    public static function encode($parameters): string
    {
        self::validateParameters($parameters);

        $orderId = str_replace('x', 'a', strtolower($parameters['orderId']));
        $UUIDBase = substr($parameters['entranceCode'] . $orderId, -29);

        $hash = sha1($UUIDBase);
        $strCheckSum = substr($hash, 11, 2);
        $UUID = self::$prefix . $strCheckSum . $UUIDBase;

        return UUID::formatUUID($UUID);
    }

    /**
     * @param array $parameters
     * @return void
     * @throws Error
     * @throws InvalidArgument
     */
    private static function validateParameters(array &$parameters)
    {
        if (!isset($parameters['orderId']) || !isset($parameters['entranceCode'])) {
            throw new InvalidArgument("Invalid arguments; required: orderId, entranceCode");
        }

        self::validateOrderId($parameters['orderId']);
        self::validateEntranceCode($parameters['entranceCode']);
    }

    /**
     * @param string $entranceCode
     * @return void
     * @throws Error
     */
    private static function validateEntranceCode(string $entranceCode)
    {
        if (!preg_match('/^[0-9a-z]{40}$/', $entranceCode)) {
            throw new Error('Invalid entrance code');
        }
    }

    /**
     * @param string $orderId
     * @return void
     * @throws Error
     */
    private static function validateOrderId(string $orderId)
    {
        if (!preg_match('/^[0-9]{10}(X)[0-9a-z]{5}$/', $orderId)) {
            throw new Error('Invalid orderID');
        }
    }

    /**
     * Decode a UUID
     *
     * @param array $parameters
     * @return array
     * @throws Error
     */
    public static function decode(array $parameters): array
    {
        if (!self::validate($parameters)) {
            throw new Error('Incorrect signature');
        }

        $uuidData = preg_replace('/[^0-9a-z]/i', '', $parameters['uuid']);
        $orderId = preg_replace('/a/', 'X', substr($uuidData, -16), 1);

        self::validateOrderId($orderId);

        return [
            'orderId' => $orderId
        ];
    }

    /**
     * Validate a UUID with supplied secret
     *
     * @param array $parameters
     *
     * @return bool
     */
    public static function validate(array $parameters): bool
    {
        if (substr($parameters['uuid'], 0, 1) !== self::$prefix) {
            return false;
        }

        $uuid = preg_replace('/[^0-9a-z]/i', '', $parameters['uuid']);
        $uuidData = substr($uuid, 3);

        $hash = sha1($uuidData);
        $checksum = self::$prefix . substr($hash, 11, 2);

        return $checksum === substr($uuid, 0, strlen($checksum));
    }
}
