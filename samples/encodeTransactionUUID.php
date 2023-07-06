<?php

use \Paynl\QR\UUID;

include '../vendor/autoload.php';

try {
    $UUID = UUID::encode(UUID::QR_TYPE_TRANSACTION, [
        'orderId'      => '',
        'entranceCode' => ''
    ]);

    echo $UUID;

} catch (\Paynl\QR\Error\Error $error) {
    echo $error->getMessage();
}
