<?php

use Pimcore\Bootstrap;

include dirname(__DIR__) . '/vendor/autoload.php';

Bootstrap::setProjectRoot();
Bootstrap::bootstrap();
