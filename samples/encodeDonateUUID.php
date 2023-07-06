<?php

use \Paynl\QR\UUID;

include '../vendor/autoload.php';

try {
    $UUID = UUID::encode(UUID::QR_TYPE_DONATE, [
        'serviceId'     => '',
        'secret'        => '',
        'amount'        => 1,
        'reference'     => '12345678',
        'referenceType' => UUID::REFERENCE_TYPE_STRING
    ]);

    echo $UUID;

} catch (\Paynl\QR\Error\Error $error) {
    echo $error->getMessage();
}
