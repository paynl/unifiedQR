<?php
/**
 * Created by PhpStorm.
 * User: jorn
 * Date: 7-2-19
 * Time: 14:23
 */

use \Paynl\QR\UUID;

include '../vendor/autoload.php';

try {
    $UUIDData = UUID::decode([
        'secret' => '',
        'uuid'   => ''
    ]);

    print_r($UUIDData);

} catch (\Paynl\QR\Error\Error $error) {
    echo $error->getMessage();
}