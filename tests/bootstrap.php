<?php

if (!$loader = @include __DIR__.'/../vendor/autoload.php') {
    echo 'Composer autoloader not found.'.PHP_EOL;
    echo 'Please run `composer install`.'.PHP_EOL;
    exit(1);
}

$loader->add('ActiveDoctrine', __DIR__);
