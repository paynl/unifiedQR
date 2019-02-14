<?php
/**
 * Created by PhpStorm.
 * User: jorn
 * Date: 8-2-19
 * Time: 14:58
 */

use PHPUnit\Framework\TestCase;

class UUIDTest extends TestCase
{
    CONST VALID_UUID_FORMAT_DYNAMIC = '/^(b)[0-9a-f]{7}-[0-9]{4}-[0-9]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';
    CONST VALID_UUID_FORMAT_STATIC = '/^[0-9]{1}[0-9a-f]{7}-[0-9]{4}-[0-9]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';
    CONST VALID_UUID_FORMAT_DONATE = '/^(d)[0-9a-f]{7}-[0-9]{4}-[0-9]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';
    CONST VALID_UUID_FORMAT_TRANSACTION = '/^(a)[0-9a-f]{7}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9]{4}-[0-9a-f]{12}$/i';

    CONST SECRET = 'abcdefabcdefabcdefabcdefabcdefabcdefabcd';
    CONST ENTRANCECODE = 'abcdefabcdefabcdefabcdefabcdefabcdefabcd';

    public function testEncodeUnknownType()
    {
        $this->expectException(\Paynl\QR\Error\InvalidArgument::class);
        \Paynl\QR\UUID::encode(8, []);
    }

    public function testDecodeEmpty()
    {
        $this->expectException(\Paynl\QR\Error\InvalidArgument::class);
        \Paynl\QR\UUID::decode([]);
    }

    public function testDecodeInvalidUUID()
    {
        $this->expectException(\Paynl\QR\Error\InvalidArgument::class);
        \Paynl\QR\UUID::decode(['uuid' => '']);
    }

    public function testDecodeInvalidTypeString()
    {
        $this->expectException(\Paynl\QR\Error\InvalidArgument::class);
        \Paynl\QR\UUID::decode([
            'uuid' => '123456789012345678901234567890123456',
            'type' => 'q'
        ]);
    }

    public function testDecodeInvalidTypeInt()
    {
        $this->expectException(\Paynl\QR\Error\InvalidArgument::class);
        \Paynl\QR\UUID::decode([
            'uuid' => '123456789012345678901234567890123456',
            'type' => 8
        ]);
    }


    public function testDecodeInvalidTypeDetection()
    {
        $this->expectException(\Paynl\QR\Error\InvalidArgument::class);
        \Paynl\QR\UUID::decode([
            'uuid' => 'q23456789012345678901234567890123456'
        ]);
    }

    public function testValidateSecretEmpty()
    {
        $this->expectException(\Paynl\QR\Error\Error::class);
        \Paynl\QR\UUID::validateSecret('');
    }

    public function testValidateSecretInvalidHex()
    {
        $this->expectException(\Paynl\QR\Error\Error::class);
        \Paynl\QR\UUID::validateSecret('x123456789012345678901234567890123456789');
    }

    public function testValidateSecret()
    {
        $this->assertNull(\Paynl\QR\UUID::validateSecret('1234567890123456789012345678901234567890'));
    }

    public function testValidateServiceIdEmpty()
    {
        $this->expectException(\Paynl\QR\Error\Error::class);
        \Paynl\QR\UUID::validateServiceId('');
    }

    public function testValidateServiceIdInvalid()
    {
        $this->expectException(\Paynl\QR\Error\Error::class);
        \Paynl\QR\UUID::validateServiceId('SD-1234-1234');
    }

    public function testValidateServiceId()
    {
        $this->assertNull(\Paynl\QR\UUID::validateServiceId('SL-1234-1234'));
    }

    public function testValidateReferenceStringInvalid()
    {
        $this->expectException(\Paynl\QR\Error\Error::class);
        \Paynl\QR\UUID::validateReferenceString('%MyInvalidRef%');
    }

    public function testValidateReferenceString()
    {
        $this->assertNull(\Paynl\QR\UUID::validateReferenceString('ValidRef'));
    }

    public function testValidateReferenceHexInvalid()
    {
        $this->expectException(\Paynl\QR\Error\Error::class);
        \Paynl\QR\UUID::validateReferenceHex('abcdef123456789p');
    }

    public function testValidateReferenceHex()
    {
        $this->assertNull(\Paynl\QR\UUID::validateReferenceHex('abcdef1234567890'));
    }

    public function testValidateAmountNegative()
    {
        $this->expectException(\Paynl\QR\Error\Error::class);
        \Paynl\QR\UUID::validateAmount(-1);
    }

    public function testValidateAmountTooMuch()
    {
        $this->expectException(\Paynl\QR\Error\Error::class);
        \Paynl\QR\UUID::validateAmount(1000000);
    }

    public function testValidateAmountAsString()
    {
        $this->assertNull(\Paynl\QR\UUID::validateAmount('123'));
    }

    public function testValidatAmountAsInt()
    {
        $this->assertNull(\Paynl\QR\UUID::validateAmount(123));
    }

    public function testValidateBrandlockTooMuch()
    {
        $this->expectException(\Paynl\QR\Error\Error::class);
        \Paynl\QR\UUID::validateBrandlock(100);
    }

    public function testValidatBrandlockAsInt()
    {
        $this->assertNull(\Paynl\QR\UUID::validateBrandlock(77));
    }

    public function testFormatUUIDTooShort()
    {
        $this->assertFalse(\Paynl\QR\UUID::formatUUID('abcdefabcdefabcdefabcdef'));

    }

    public function testFormatUUID()
    {
        $this->assertRegExp('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            \Paynl\QR\UUID::formatUUID("d11fe552349043203132333435363738"));
    }

    public function testEncodeDonateUUIDStringRef()
    {
        $serviceId = 'SL-1234-1234';
        $amount    = 1;
        $reference = '12345678';

        $uuid = \Paynl\QR\UUID::encode(\Paynl\QR\UUID::QR_TYPE_DONATE, [
            'serviceId'     => $serviceId,
            'secret'        => UUIDTest::SECRET,
            'amount'        => $amount,
            'reference'     => $reference,
            'referenceType' => \Paynl\QR\UUID::REFERENCE_TYPE_STRING
        ]);

        $this->assertRegExp(self::VALID_UUID_FORMAT_DONATE, $uuid);

        $decoded = \Paynl\QR\UUID::decode([
            'uuid'   => $uuid,
            'secret' => self::SECRET
        ]);

        $this->assertArrayHasKey("serviceId", $decoded);
        $this->assertArrayHasKey("amount", $decoded);
        $this->assertArrayHasKey("reference", $decoded);

        $this->assertEquals($serviceId, $decoded['serviceId']);
        $this->assertEquals($amount, $decoded['amount']);
        $this->assertEquals($reference, \Paynl\QR\UUID::hexToString($decoded['reference']));
    }

    public function testEncodeStaticUUIDStringRef()
    {
        $serviceId = 'SL-1234-1234';
        $amount    = 1;
        $reference = '12345678';

        $uuid = \Paynl\QR\UUID::encode(\Paynl\QR\UUID::QR_TYPE_STATIC, [
            'serviceId'     => $serviceId,
            'secret'        => UUIDTest::SECRET,
            'amount'        => $amount,
            'reference'     => $reference,
            'referenceType' => \Paynl\QR\UUID::REFERENCE_TYPE_STRING
        ]);

        $this->assertRegExp(self::VALID_UUID_FORMAT_STATIC, $uuid);

        $decoded = \Paynl\QR\UUID::decode([
            'uuid'   => $uuid,
            'secret' => self::SECRET
        ]);

        $this->assertArrayHasKey("serviceId", $decoded);
        $this->assertArrayHasKey("amount", $decoded);
        $this->assertArrayHasKey("reference", $decoded);

        $this->assertEquals($serviceId, $decoded['serviceId']);
        $this->assertEquals($amount, $decoded['amount']);
        $this->assertEquals($reference, \Paynl\QR\UUID::hexToString($decoded['reference']));
    }

    public function testEncodeDynamicUUIDStringRef()
    {
        $serviceId = 'SL-1234-1234';
        $reference = '12345678';

        $uuid = \Paynl\QR\UUID::encode(\Paynl\QR\UUID::QR_TYPE_DYNAMIC, [
            'serviceId'     => $serviceId,
            'secret'        => UUIDTest::SECRET,
            'reference'     => $reference,
            'referenceType' => \Paynl\QR\UUID::REFERENCE_TYPE_STRING
        ]);

        $this->assertRegExp(self::VALID_UUID_FORMAT_DYNAMIC, $uuid);

        $decoded = \Paynl\QR\UUID::decode([
            'uuid'   => $uuid,
            'secret' => self::SECRET
        ]);

        $this->assertArrayHasKey("serviceId", $decoded);
        $this->assertArrayHasKey("reference", $decoded);

        $this->assertEquals($serviceId, $decoded['serviceId']);
        $this->assertEquals($reference, \Paynl\QR\UUID::hexToString($decoded['reference']));
    }


    public function testEncodeTransactionUUID()
    {
        $entranceCode = UUIDTest::ENTRANCECODE;
        $orderId      = '1010101010Xabcde';

        $uuid = \Paynl\QR\UUID::encode(\Paynl\QR\UUID::QR_TYPE_TRANSACTION, [
            'entranceCode' => $entranceCode,
            'orderId'      => $orderId
        ]);

        $this->assertRegExp(self::VALID_UUID_FORMAT_TRANSACTION, $uuid);

        $decoded = \Paynl\QR\UUID::decode([
            'uuid' => $uuid
        ]);

        $this->assertArrayHasKey('orderId', $decoded);
        $this->assertEquals($orderId, $decoded['orderId']);
    }
}