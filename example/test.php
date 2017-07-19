<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Hug\Http\Http as Http;

# Curl Request with headers (test with proxy)

$filename = __DIR__ . '/free-headers.html';
$html = file_get_contents($filename);
$req = Http::extract_request_headers_body($html);
error_log(print_r($req['headers'], true));


exit;

$test = Http::grab_image('https://hugo.maugey.fr/img/hugo_maugey.jpg', __DIR__ . '/../data/hugo_maugey.jpg');
error_log($test);
