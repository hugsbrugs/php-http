<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Hug\Http\Http as Http;

// https://stackoverflow.com/questions/10288130/php-curl-following-redirects
// Infinite redirect
$url = 'https://www.pharmacieanglofrancaise.fr';
$url = 'https://www.hotelsbarriere.com/content/hotels/fr.html';

// $url = 'https://universpharmacie.fr';

$redir = Http::get_all_redirects($url);
print_r($redir);

// $redir = Http::get_final_url($url);
// echo $redir;

// $redir = Http::get_redirect_url($url);
// echo $redir;
