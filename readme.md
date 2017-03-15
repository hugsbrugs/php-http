# php-http

This library provides PHP utilities functions to manage URLs. Read [PHP DOC](https://hugsbrugs.github.io/php-http)

[![Build Status](https://travis-ci.org/hugsbrugs/php-http.svg?branch=master)](https://travis-ci.org/hugsbrugs/php-http)
[![Coverage Status](https://coveralls.io/repos/github/hugsbrugs/php-http/badge.svg?branch=master)](https://coveralls.io/github/hugsbrugs/php-http?branch=master)

## Install

Install package with composer
```
composer require hugsbrugs/php-http
```

In your PHP code, load librairy
```
require_once __DIR__ . '/../vendor/autoload.php';
use Hug\Http\Http as Http;
```

## Usage

Execute shell nslookup command
```
Http::nslookup($url);
```

Check if an url is accessible (means not a 404)
```
Http::is_url_accessible($url);
```

Returns HTTP code for given URL
```
Http::get_http_code($url);
```

Cleans an url from its query parameters
```
Http::url_remove_query($url);
```

Cleans an url from its query parameters and path
```
Http::url_remove_query_and_path($url);
```

Quick and dirty function to save an image from the internet
```
Http::grab_image($url, $save_to);
```

Returns basic HTTP headers for a CURL request
```
Http::get_default_headers($host);
```

Extracts a TLD (Top Level Domain) from an URL
```
Http::extract_tld_from_url($url);
```

Extracts a sub domain from an URL
```
Http::extract_subdomain_from_url($url);
```

Extracts a domain name from an URL
```
Http::extract_domain_from_url($url);
```

```
Http::extract_request_headers_body($html_with_headers);
```

Sets a php script desired status code (usefull for API)
```
Http::header_status($statusCode);
```

Gets the address that the provided URL redirects to, or FALSE if there's no redirect.
```
Http::get_redirect_url($url);
```

Follows and collects all redirects, in order, for the given URL.
```
Http::get_all_redirects($url);
```

Gets the address that the URL ultimately leads to.
```
Http::get_final_url($url);
```


## Unit Tests

```
composer exec phpunit
```

## Author

Hugo Maugey [visit my website ;)](https://hugo.maugey.fr)