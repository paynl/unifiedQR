<?php
/**
 * Created by PhpStorm.
 * User: jorn
 * Date: 12-2-19
 * Time: 9:43
 */

use PHPUnit\Framework\TestCase;

class TransactionUUIDTest extends TestCase
{
    public function testEncodeWithoutOrderId()
    {
        $this->expectException(\Paynl\QR\Error\InvalidArgument::class);
        \Paynl\QR\TransactionUUID::encode([
            'entranceCode' => UUIDTest::ENTRANCECODE
        ]);
    }

    public function testEncodeWithoutEntranceCode()
    {
        $this->expectException(\Paynl\QR\Error\InvalidArgument::class);
        \Paynl\QR\TransactionUUID::encode([
            'orderId' => '1010101010Xabcde'
        ]);
    }

    public function testEncodeInvalidOrderId()
    {
        $this->expectException(\Paynl\QR\Error\InvalidArgument::class);
        \Paynl\QR\TransactionUUID::encode([
            'entranceCode' => UUIDTest::ENTRANCECODE,
            'orderId'      => '1010101010Xabcd'
        ]);
    }

    public function testEncodeInvalidEntranceCode()
    {
        $this->expectException(\Paynl\QR\Error\InvalidArgument::class);
        \Paynl\QR\TransactionUUID::encode([
            'entranceCode' => 'xyzxyzxyzxyz',
            'orderId'      => '1010101010Xabcde'
        ]);
    }

    public function testEncode()
    {
        $this->assertRegExp(UUIDTest::VALID_UUID_FORMAT_TRANSACTION, \Paynl\QR\TransactionUUID::encode([
            'entranceCode' => UUIDTest::ENTRANCECODE,
            'orderId'      => '1010101010Xabcde'
        ]));
    }

    public function testInvalidEncoding()
    {
        $this->assertNotRegExp(UUIDTest::VALID_UUID_FORMAT_TRANSACTION, \Paynl\QR\DynamicUUID::encode([
            'serviceId'     => 'SL-1234-1234',
            'secret'        => UUIDTest::SECRET,
            'reference'     => '12345678',
            'referenceType' => \Paynl\QR\UUID::REFERENCE_TYPE_STRING
        ]));
    }

    public function testDecodeInvalidUUID()
    {
        $this->expectException(\Paynl\QR\Error\Error::class);
        \Paynl\QR\TransactionUUID::decode([
            'uuid'   => "1169b218-1234-1234-3132-333435363738",
            'secret' => UUIDTest::SECRET
        ]);
    }

    public function testDecodeInvalidSecret()
    {
        $this->expectException(\Paynl\QR\Error\Error::class);
        \Paynl\QR\TransactionUUID::decode([
            'uuid'   => "d169a218-1234-1234-3132-333435363738",
            'secret' => ''
        ]);
    }

    public function testDecodeInvalidPrefix()
    {
        $this->expectException(\Paynl\QR\Error\Error::class);
        \Paynl\QR\TransactionUUID::decode([
            'uuid'   => "b1197bf1-1234-1234-3132-333435363738",
            'secret' => UUIDTest::SECRET
        ]);
    }

    public function testDecode()
    {
        $decoded = \Paynl\QR\TransactionUUID::decode([
            'uuid'   => "ac6defab-cdef-abcd-1010-101010aabcde",
            'secret' => UUIDTest::SECRET
        ]);

        $this->assertArrayHasKey('orderId', $decoded);
    }
}