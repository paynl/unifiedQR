<?php
/**
 * Created by PhpStorm.
 * User: jorn
 * Date: 12-2-19
 * Time: 9:43
 */

use PHPUnit\Framework\TestCase;

class DonateUUIDTest extends TestCase
{
    public function testEncodeWithoutServiceId()
    {
        $this->expectException(\Paynl\QR\Error\InvalidArgument::class);
        \Paynl\QR\DonateUUID::encode([
            'secret'        => UUIDTest::SECRET,
            'amount'        => 1,
            'reference'     => '12345678',
            'referenceType' => \Paynl\QR\UUID::REFERENCE_TYPE_STRING
        ]);
    }

    public function testEncodeWithoutSecret()
    {
        $this->expectException(\Paynl\QR\Error\InvalidArgument::class);
        \Paynl\QR\DonateUUID::encode([
            'serviceId'     => 'SL-1234-1234',
            'amount'        => 1,
            'reference'     => '12345678',
            'referenceType' => \Paynl\QR\UUID::REFERENCE_TYPE_STRING
        ]);
    }

    public function testEncodeWithoutAmount()
    {
        $this->expectException(\Paynl\QR\Error\InvalidArgument::class);
        \Paynl\QR\DonateUUID::encode([
            'serviceId'     => 'SL-1234-1234',
            'secret'        => UUIDTest::SECRET,
            'reference'     => '12345678',
            'referenceType' => \Paynl\QR\UUID::REFERENCE_TYPE_STRING
        ]);
    }

    public function testEncodeWithoutReference()
    {
        $this->expectException(\Paynl\QR\Error\InvalidArgument::class);
        \Paynl\QR\DonateUUID::encode([
            'serviceId'     => 'SL-1234-1234',
            'secret'        => UUIDTest::SECRET,
            'amount'        => 1,
            'referenceType' => \Paynl\QR\UUID::REFERENCE_TYPE_STRING
        ]);
    }

    public function testEncodeWithoutReferenceType()
    {
        $this->assertRegExp(UUIDTest::VALID_UUID_FORMAT_DONATE, \Paynl\QR\DonateUUID::encode([
            'serviceId' => 'SL-1234-1234',
            'secret'    => UUIDTest::SECRET,
            'amount'    => 1,
            'reference' => '12345678'
        ]));
    }

    public function testInvalidEncoding()
    {
        $this->assertNotRegExp(UUIDTest::VALID_UUID_FORMAT_DONATE, \Paynl\QR\StaticUUID::encode([
            'serviceId'     => 'SL-1234-1234',
            'secret'        => UUIDTest::SECRET,
            'amount'        => 1,
            'reference'     => '12345678',
            'referenceType' => \Paynl\QR\UUID::REFERENCE_TYPE_STRING
        ]));
    }

    public function testEncodeRefTypeStringInvalid()
    {
        $this->expectException(\Paynl\QR\Error\Error::class);
        $this->assertRegExp(UUIDTest::VALID_UUID_FORMAT_DONATE, \Paynl\QR\DonateUUID::encode([
            'serviceId'     => 'SL-1234-1234',
            'secret'        => UUIDTest::SECRET,
            'amount'        => 1,
            'reference'     => 'thisstringistoolong',
            'referenceType' => \Paynl\QR\UUID::REFERENCE_TYPE_STRING
        ]));
    }

    public function testEncodeRefTypeString()
    {
        $this->assertRegExp(UUIDTest::VALID_UUID_FORMAT_DONATE, \Paynl\QR\DonateUUID::encode([
            'serviceId'     => 'SL-1234-1234',
            'secret'        => UUIDTest::SECRET,
            'amount'        => 1,
            'reference'     => '12345678',
            'referenceType' => \Paynl\QR\UUID::REFERENCE_TYPE_STRING
        ]));
    }

    public function testEncodeRefTypeHexInvalid()
    {
        $this->expectException(\Paynl\QR\Error\Error::class);
        $this->assertRegExp(UUIDTest::VALID_UUID_FORMAT_DONATE, \Paynl\QR\DonateUUID::encode([
            'serviceId'     => 'SL-1234-1234',
            'secret'        => UUIDTest::SECRET,
            'amount'        => 1,
            'reference'     => '12345678z',
            'referenceType' => \Paynl\QR\UUID::REFERENCE_TYPE_HEX
        ]));
    }

    public function testEncodeRefTypeHex()
    {
        $this->assertRegExp(UUIDTest::VALID_UUID_FORMAT_DONATE, \Paynl\QR\DonateUUID::encode([
            'serviceId'     => 'SL-1234-1234',
            'secret'        => UUIDTest::SECRET,
            'amount'        => 1,
            'reference'     => '12345678',
            'referenceType' => \Paynl\QR\UUID::REFERENCE_TYPE_HEX
        ]));
    }

    public function testDecodeInvalidUUID()
    {
        $this->expectException(\Paynl\QR\Error\Error::class);
        \Paynl\QR\DonateUUID::decode([
            'uuid'   => "1169b218-1234-1234-3132-333435363738",
            'secret' => UUIDTest::SECRET
        ]);
    }

    public function testDecodeInvalidSecret()
    {
        $this->expectException(\Paynl\QR\Error\Error::class);
        \Paynl\QR\DonateUUID::decode([
            'uuid'   => "d169a218-1234-1234-3132-333435363738",
            'secret' => ''
        ]);
    }

    public function testDecodeInvalidPrefix()
    {
        $this->expectException(\Paynl\QR\Error\Error::class);
        \Paynl\QR\DonateUUID::decode([
            'uuid'   => "a1197bf1-1234-1234-3132-333435363738",
            'secret' => UUIDTest::SECRET
        ]);
    }

    public function testDecode()
    {
        $decoded = \Paynl\QR\DonateUUID::decode([
            'uuid'   => "d1197bf1-1234-1234-3132-333435363738",
            'secret' => UUIDTest::SECRET
        ]);

        $this->assertArrayHasKey('amount', $decoded);
        $this->assertArrayHasKey('serviceId', $decoded);
        $this->assertArrayHasKey('reference', $decoded);
    }
}