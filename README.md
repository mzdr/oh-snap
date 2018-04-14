# 💥 Oh Snap!

A pretty formatter for [BooBoo].

## Installation

It is recommended that you install this library using [Composer].

```bash
composer require mzdr/oh-snap
```

Don’t forget to check out the official documentation of [BooBoo] on how to use and install it.

## Usage

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use League\BooBoo\BooBoo;
use mzdr\OhSnap\Formatter\PrettyFormatter;

// Default options… adjust to your liking.
// See below for more information.
$options = [

    // Path to custom theme CSS file
    'theme' => null,

    // Path to custom template file
    'template' => null,

    // If set to true, code preview will not
    // contain the whole file but the amount
    // of lines defined in excerptSize
    'excerptOnly' => false,

    // Amount of lines the code preview should have…
    'excerptSize' => 20
];

$booboo = new BooBoo([new PrettyFormatter($options)]);
$booboo->register();

throw new RuntimeException('Hi there! 👋');
```

## License

This project is licensed under [MIT license].

[BooBoo]: https://github.com/thephpleague/booboo
[Composer]: https://getcomposer.org/doc/00-intro.md
[MIT license]: ./LICENSE
