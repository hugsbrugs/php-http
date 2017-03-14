<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Hug\Http\Http as Http;

$test = Http::grab_image('https://hugo.maugey.fr/img/hugo_maugey.jpg', __DIR__ . '/../data/hugo_maugey.jpg');
error_log($test);