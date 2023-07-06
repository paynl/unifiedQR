# Pay.nl PHP - UnifiedQR

Library to encode and decode payment UUID

---

- [Installation](#installation)
- [Quick start and examples](#quick-start-and-examples)

---

### Installation

This library uses composer.

Composer is a tool for dependency management in PHP. It allows you to declare the libraries your project depends on and it will manage (install/update) them for you.

For more information on how to use/install composer, please visit: [https://github.com/composer/composer](https://github.com/composer/composer)

To install the Pay.nl PHP sdk into your project, simply

	$ composer require paynl/unifiedQR


### Quick start and examples

Encode dynamic QR 

```php
require __DIR__ . '/vendor/autoload.php';

$UUID = \Paynl\QR\UUID::encode(\Paynl\QR\UUID::QR_TYPE_DYNAMIC, [
    'serviceId'     => 'SL-1234-5678',
    'secret'        => '0e8debc04c0dce170a1de4205053bd3ed6fd132f',
    'reference'     => '0123456',
    'referenceType' => \Paynl\QR\UUID::REFERENCE_TYPE_STRING
]);

var_dump($UUID);
```

Decode dynamic QR

```php
require __DIR__ . '/vendor/autoload.php';

$UUID = \Paynl\QR\UUID::decode([
    'secret' => '0e8debc04c0dce170a1de4205053bd3ed6fd132f',
    'uuid'   => $UUID
]);

var_dump($UUID);
```
