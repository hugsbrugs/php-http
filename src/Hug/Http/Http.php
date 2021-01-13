<?php

namespace Hug\Http;

use LayerShifter\TLDExtract\Extract;
use TrueBV\Punycode;

/**
 *
 */
class Http
{
    /**
     * Execute shell nslookup command
     *
     * This function is used to accelerate Domain Name Availability : if a domain name responds to nslookup command then it's not available for purchase !
     *
     * @param string $url
     *
     * @return string|null Url corresponding IP address or null
     *
     */
    public static function nslookup($url)
    {
        $ip = null;
        $ret = shell_exec('nslookup '.$url);
        $ret = explode("\n", $ret);
        $ret = array_reverse($ret);
        foreach ($ret as $r)
        {
            if(substr($ret[2], 0, 9)=='Address: ')
            {
                $ip = substr($ret[2], 9); break;
            } 
        }
        return $ip;
    }

    /**
     * Check if an url is accessible (means not a 404)
     * 
     * @param string $url
     * @return bool is_url_accessible
     * 
     * @todo check speed against is_url_accessible2
     */
    public static function is_url_accessible($url)
    {
        $ch = curl_init($url);
        # SET NOBODY TO SPEED
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        # 400 means not found, 200 means found.
        curl_close($ch);
        if($retcode===200 || $retcode===301 || $retcode===302 || $retcode===307)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Returns HTTP code for given URL
     *
     * @param string $utl
     * @return int HTTP code
     */
    public static function get_http_code($url)
    {
        $ch = curl_init($url);
        # SET NOBODY TO SPEED
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        # 400 means not found, 200 means found.
        curl_close($ch);
        return $retcode;
    }

    /**
     * Cleans an url from its query parameters
     *
     * Example : 
     * input : http://www.monsite.fr/fr/coucou/index.php?id=3&num=50
     * output : http://www.monsite.fr/fr/coucou/index.php
     *
     * @param string $url
     * @return string $clean_url
     */
    public static function url_remove_query($url)
    {
        $url_pieces = parse_url($url);
        $clean_url = (isset($url_pieces['scheme']) ? $url_pieces['scheme'] : 'http') . '://' . (isset($url_pieces['host']) ? $url_pieces['host'] : '');
        if(isset($url_pieces['path']))
        {
            $clean_url .= $url_pieces['path'];
        }
        return $clean_url;
    }

    /**
     * Cleans an url from its query parameters and path
     *
     * Example : 
     * input : http://www.monsite.fr/fr/coucou/index.php?id=3&num=50
     * output : http://www.monsite.fr
     *
     * @param string $url
     * @return string $clean_url
     */
    public static function url_remove_query_and_path($url)
    {
        $url_pieces = parse_url($url);
        //$clean_url = $url_pieces['scheme'] . '://' . $url_pieces['host'];
        $clean_url = (isset($url_pieces['scheme']) ? $url_pieces['scheme'] : 'http') . '://' . (isset($url_pieces['host']) ? $url_pieces['host'] : '');
        if($clean_url==='http://')
        {
            $clean_url = '';
        }
        return $clean_url;
    }

    /**
     * Quick and dirty function to save an image (or any binary data transfered by url) from the internet
     *
     * @link http://stackoverflow.com/questions/6476212/save-image-from-url-with-curl-php
     *
     * @link http://stackoverflow.com/questions/724391/saving-image-from-php-url
     *
     * @param string $url
     * @param string $save_to
     * @return bool 
     */
    public static function grab_image($url, $save_to)
    {
        $grab = false;

        try
        {
            # Basic Curl Request to download image
            $ch = curl_init ($url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
            # Set User-Agent because lots of servers deny request with empty UA
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:84.0) Gecko/20100101 Firefox/84.0');
            $raw = curl_exec($ch);
            $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close ($ch);

            // error_log('retcode : ' . $retcode);
            // error_log('raw : ' . $raw);

            if($retcode===200)
            {
                # Remove existing file
                if(file_exists($save_to))
                {
                    unlink($save_to);
                }

                # Save 
                $fp = fopen($save_to, 'x');
                fwrite($fp, $raw);
                fclose($fp);

                $grab = true;
            }
            /*else
            {
                // error_log('grab_image '. $url .' returns ' . $retcode);
                # Throw warning
                trigger_error('InvalidImageHttpCode', E_USER_NOTICE);
            }*/
        }
        catch(Exception $e)
        {
            error_log($e->getMessage());
        }
        return $grab;
    }

    /**
     * Returns basic HTTP headers for a CURL request
     *
     * @param strnig $host (ex: www.google.fr)
     * @return array $headers (Connection, Accept, Accept-Charset, Keep-Alive, Accept-Language, Host)
     */
    public static function get_default_headers($host)
    {
        $headers = [
            'Connection' => 'keep-alive',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Charset' => 'ISO-8859-1,utf-8;q=0.7,*;q=0.7',
            'Keep-Alive' => '115',
            'Accept-Language' => 'fr;q=0.8,en-us;q=0.5,en;q=0.3',
            'Host' => $host,
        ];
        
        $compiledHeaders = [];
        foreach($headers as $k=>$v)
        {
            $compiledHeaders[] = $k.': '.$v;
        }

        return $compiledHeaders;
    }

    /**
     * TODO
     */
    public static function get_compiled_headers($headers, $url)
    {
        # Extract host from URL
        $host = self::extract_domain_from_url(self::url_idn_to_ascii($url));
        $headers['Host'] = $host;

        $compiledHeaders = [];
            foreach($headers as $k=>$v)
                $compiledHeaders[] = $k.': '.$v;

        return $compiledHeaders;
    }


    /**
     * Extracts a TLD (Top Level Domain) from an URL
     *
     * @param string $url
     * @return string $tld 
     */
    public static function extract_extension_from_url($url)
    {
        $extension = '';
        
        $components = tld_extract($url);
        if($components->suffix!='')
        {
            $extension = $components->suffix;
        }
        

        return $extension;
    }

    /**
     * Extracts scheme (ftp, http) from an URL
     *
     * Example : 
     * input : https://www.monsite.fr/fr/coucou/index.php?id=3&num=50
     * output : https
     *
     * @param string $url
     * @return string $scheme
     */
    public static function extract_scheme_from_url($url)
    {
        $scheme = '';
        $url_pieces = parse_url($url);
        $scheme = isset($url_pieces['scheme']) ? $url_pieces['scheme'] : '';
        return $scheme;
    }
    

    /**
     * Extracts a TLD (Top Level Domain) from an URL
     *
     * @param string $url
     * @return string $tld 
     */
    public static function extract_tld_from_url($url)
    {
        $tld = '';
        
        $components = tld_extract($url);
        if($components->hostname!='')
        {
            $tld .= $components->hostname . '.';
        }
        $tld .= $components->suffix;

        return $tld;
    }

    /**
     * In PHP : http://w-shadow.com/blog/2012/08/28/tldextract/
     * In nodejs : https://github.com/oncletom/tld.js
     *
     * @param string $url
     * @return string $domain
     */
    public static function extract_domain_from_url($url)
    {
        $domain = '';

        $components = tld_extract($url);
        if($components->subdomain!='')
        {
            $domain = $components->subdomain.'.';
        }
        if($components->hostname!='')
        {
            $domain .= $components->hostname.'.';
        }
        $domain .= $components->suffix;

        return $domain;
    }

    /**
     * Extracts a sub-domain from an URL
     *
     * @param string $url
     * @return string $tld 
     */
    public static function extract_subdomain_from_url($url)
    {
        $subdomain = '';

        $components = tld_extract($url);
        if($components->subdomain!='')
        {
            $subdomain = $components->subdomain;
        }

        return $subdomain;
    }

    /**
     * Separates Headers from Body in CURL response
     *
     * @param string $html_with_headers
     * @return array 
     *
     * @link http://stackoverflow.com/questions/9183178/php-curl-retrieving-response-headers-and-body-in-a-single-request
     *
     * @todo test with proxy request
     */
    public static function extract_request_headers_body($html_with_headers)
    {
        // $parts = explode("\r\n\r\nHTTP/", $html_with_headers);
        $parts = explode("\n\nHTTP/", $html_with_headers);

        $headers_count = substr_count($html_with_headers, "\n\nHTTP/");
        $headers_count +=1;
        // error_log('headers_count : ' . $headers_count);

        $pos = strpos($html_with_headers, "\n\nHTTP/");
        $pos2 = strpos($html_with_headers, "\n\n", $pos+1);
        error_log('pos end last header : ' . $pos2);

        $headers = substr($html_with_headers, 0, $pos2);
        $body = substr($html_with_headers, $pos2);
        // error_log('headers : ' . $headers);
        // error_log('body : ' . $body);

        if($headers_count>1)
        {
            $heads = [];
            $headers = explode("\n\n", $headers);
            foreach ($headers as $key => $head)
            {
                $heads[] = Http::http_parse_headers($head);
            }
        }
        else
        {
            $heads = Http::http_parse_headers($headers);
        }

        $response = [
            "headers" => $heads, 
            "body" => $body
        ];


        # Many headers (proxy, redirects)
        /*if(count($parts) > 1)
        {
            error_log('many headers');
            $first_headers = $parts[0];
            $last_parts = array_pop($parts);
            // $parts = implode("\r\n\r\n", [$first_headers, $last_parts]);
            $parts = implode("\n\n", [$first_headers, $last_parts]);
        }
        else
        {
            # One Header
            $parts = $parts[0];
        }
        
        # Separate HTML from Headers
        // list($headers, $body) = explode("\r\n\r\n", $parts, 2);
        list($headers, $body) = explode("\n\n", $parts, 2);

        $response = [
            "header" => Http::http_parse_headers($headers), 
            "body" => $body
        ];*/

        return $response;
    }

    
    /*public static function http_parse_headers($header)
    {
         $retVal = array();
         $fields = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $header));
         foreach( $fields as $field ) {
             if( preg_match('/([^:]+): (.+)/m', $field, $match) ) {
                 $match[1] = preg_replace('/(?<=^|[\x09\x20\x2D])./e', 'strtoupper("\0")', strtolower(trim($match[1])));
                 if( isset($retVal[$match[1]]) ) {
                     $retVal[$match[1]] = array($retVal[$match[1]], $match[2]);
                 } else {
                     $retVal[$match[1]] = trim($match[2]);
                 }
             }
         }
         return $retVal;
    }*/

    public static function http_parse_headers($raw_headers)
    {
        $headers = array();
        $key = '';

        foreach(explode("\n", $raw_headers) as $i => $h)
        {
            $h = explode(':', $h, 2);

            if (isset($h[1]))
            {
                if (!isset($headers[$h[0]]))
                {
                    $headers[$h[0]] = trim($h[1]);
                }
                elseif (is_array($headers[$h[0]]))
                {
                    $headers[$h[0]] = array_merge($headers[$h[0]], array(trim($h[1])));
                }
                else
                {
                    $headers[$h[0]] = array_merge(array($headers[$h[0]]), array(trim($h[1])));
                }

                $key = $h[0];
            }
            else
            { 
                if (substr($h[0], 0, 1) == "\t")
                {
                    $headers[$key] .= "\r\n\t".trim($h[0]);
                }
                elseif (!$key) 
                {
                    $headers[0] = trim($h[0]); 
                }
            }
        }

        $status_codes = ['100','101','102','200','201','202','203','204','205','206','207','300','301','302','303','304','305','307','400','401','402','403','404','405','406','407','408','409','410','411','412','413','414','415','416','417','422','423','424','426','500','501','502','503','504','505','506','507','509','510'];
        if(isset($headers[0]))
        {
            foreach ($status_codes as $key => $status_code)
            {
                // error_log($status_code);
                if(strpos($headers[0], $status_code)>-1)
                {
                    $headers['Status'] = intval($status_code);
                    break;
                }
            }
        }

        return $headers;
    }

    # METHODE 1
    /*public static function extract_request_headers_body_1($html_with_headers)
    {
        list($header, $body) = explode("\r\n\r\n", $html_with_headers, 2);
        
        $response = [
            "header" => $header, 
            "body" => $body
        ];

        return $response;
    }*/

    # METHODE 2 : http://stackoverflow.com/questions/9183178/php-curl-retrieving-response-headers-and-body-in-a-single-request
    /*public static function extract_request_headers_body_2($html_with_headers)
    {        
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        // error_log("header_size : ".$header_size);
        $header = substr($response, 0, $header_size);
        $body = substr($html_with_headers, $header_size);

        $response = [
            "header" => $header, 
            "body" => $body
        ];

        return $response;    
    }*/

    # METHODE 3 : http://stackoverflow.com/questions/11359276/php-curl-exec-returns-both-http-1-1-100-continue-and-http-1-1-200-ok-separated-b
    /*public static function extract_request_headers_body_3($html_with_headers)
    {
        $header = [];
        $body = [];

        foreach(explode("\r\n\r\n", $html_with_headers) as $frag)
        {
            if(preg_match('/^HTTP\/[0-9\.]+ [0-9]+/', $frag))
            {
               $header[] = $frag;
            }
            else
            {
                $body[] = $frag;
            }
        }

        $header = implode("\r\n", $header);
        $body = implode($body);

        $response = [
            "header" => http_parse_headers($headers), 
            "body" => $body
        ];

        return $response;
    }*/

    /**
     * Sets a php script desired status code (usefull for API)
     * 
     * @link http://stackoverflow.com/questions/4162223/how-to-send-500-internal-server-error-error-from-a-php-script
     *
     * @param int $status_code
     * 
     * @return bool $response Has header status been set or not
     */
    public static function header_status($statusCode)
    {
        static $status_codes = null;

        if ($status_codes === null)
        {
            $status_codes = [
                100 => 'Continue',
                101 => 'Switching Protocols',
                102 => 'Processing',
                200 => 'OK',
                201 => 'Created',
                202 => 'Accepted',
                203 => 'Non-Authoritative Information',
                204 => 'No Content',
                205 => 'Reset Content',
                206 => 'Partial Content',
                207 => 'Multi-Status',
                300 => 'Multiple Choices',
                301 => 'Moved Permanently',
                302 => 'Found',
                303 => 'See Other',
                304 => 'Not Modified',
                305 => 'Use Proxy',
                307 => 'Temporary Redirect',
                400 => 'Bad Request',
                401 => 'Unauthorized',
                402 => 'Payment Required',
                403 => 'Forbidden',
                404 => 'Not Found',
                405 => 'Method Not Allowed',
                406 => 'Not Acceptable',
                407 => 'Proxy Authentication Required',
                408 => 'Request Timeout',
                409 => 'Conflict',
                410 => 'Gone',
                411 => 'Length Required',
                412 => 'Precondition Failed',
                413 => 'Request Entity Too Large',
                414 => 'Request-URI Too Long',
                415 => 'Unsupported Media Type',
                416 => 'Requested Range Not Satisfiable',
                417 => 'Expectation Failed',
                422 => 'Unprocessable Entity',
                423 => 'Locked',
                424 => 'Failed Dependency',
                426 => 'Upgrade Required',
                500 => 'Internal Server Error',
                501 => 'Not Implemented',
                502 => 'Bad Gateway',
                503 => 'Service Unavailable',
                504 => 'Gateway Timeout',
                505 => 'HTTP Version Not Supported',
                506 => 'Variant Also Negotiates',
                507 => 'Insufficient Storage',
                509 => 'Bandwidth Limit Exceeded',
                510 => 'Not Extended'
            ];
        }

        if(isset($_SERVER['SERVER_PROTOCOL']))
        {
            if ($status_codes[$statusCode] !== null)
            {
                $status_string = $statusCode . ' ' . $status_codes[$statusCode];
                header($_SERVER['SERVER_PROTOCOL'] . ' ' . $status_string, true, $statusCode);
                return true;
            }
            else
            {
                # Throw warning
                trigger_error('StatusCodeNotFound', E_USER_WARNING);
            }
        }
        else
        {
            # Throw warning
            trigger_error('ServerProtocolNotFound', E_USER_WARNING);
        }
        return false;
    }


    /**
     * Gets the address that the provided URL redirects to,
     * or false if there's no redirect. 
     *
     * @param string $url
     * @return string
     *
     * @link http://stackoverflow.com/questions/3799134/how-to-get-final-url-after-following-http-redirections-in-pure-php
     */
    public static function get_redirect_url($url, $timeout = 5)
    {
        $url = str_replace("&amp;", "&", urldecode(trim($url)));

        // $cookie = tempnam("/tmp", "CURLCOOKIE");
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:84.0) Gecko/20100101 Firefox/84.0" );
        curl_setopt($ch, CURLOPT_URL, $url );
        // curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // important !!
        curl_setopt($ch, CURLOPT_ENCODING, "" );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt($ch, CURLOPT_AUTOREFERER, true );
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout );
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout );
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, Http::get_default_headers(Http::extract_domain_from_url($url)));

        $content = curl_exec($ch);
        $response = curl_getinfo($ch);
        curl_close ( $ch );
        
        return $response['url'];
    }

    /**
     * get_all_redirects()
     * Follows and collects all redirects, in order, for the given URL. 
     *
     * @param string $url
     * @return array
     */
    public static function get_all_redirects($url, $timeout = 5, $redirects = [])
    {
        $url = str_replace("&amp;", "&", urldecode(trim($url)));

        // $cookie = tempnam("/tmp", "CURLCOOKIE");
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:84.0) Gecko/20100101 Firefox/84.0" );
        curl_setopt($ch, CURLOPT_URL, $url );
        // curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // important !!
        curl_setopt($ch, CURLOPT_ENCODING, "" );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt($ch, CURLOPT_AUTOREFERER, true );
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout );
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout );
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, Http::get_default_headers(Http::extract_domain_from_url($url)));

        $content = curl_exec( $ch );
        $response = curl_getinfo( $ch );
        curl_close ( $ch );
        
        // error_log('http : ' . $response['http_code']);
        $redirects[] = [
            'url' => $response['url'],
            'code' => $response['http_code']
        ];

        if ($response['http_code'] == 301 || $response['http_code'] == 302)
        {
            ini_set("user_agent", "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:84.0) Gecko/20100101 Firefox/84.0");
            $headers = get_headers($response['url']);

            $location = "";
            foreach($headers as $value)
            {
                if (substr(strtolower($value), 0, 9) == "location:")
                {
                    return Http::get_all_redirects(trim(substr($value, 9, strlen($value))), $timeout, $redirects);
                }
            }
        }

        if(preg_match("/window\.location\.replace\('(.*)'\)/i", $content, $value) || preg_match("/window\.location\=\"(.*)\"/i", $content, $value))
        {
            return Http::get_all_redirects($value[1], $timeout, $redirects);
        }
        else
        {
            return $redirects;
        }
    }

    /**
     * get_final_url()
     * Gets the address that the URL ultimately leads to. 
     * Returns $url itself if it isn't a redirect.
     *
     * @param string $url
     * @param string $return url|code|all
     * @return string| array
     */
    public static function get_final_url($url, $return = 'url')
    {
        $ch = curl_init($url);
        # SET NOBODY TO SPEED
        // curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:84.0) Gecko/20100101 Firefox/84.0');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,0); 
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        // curl_setopt($curl, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, Http::get_default_headers(Http::extract_domain_from_url($url)));

        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);

        if($return==='url')
        {
            $response = $url;
        }
        if($return==='code')
        {
            $response = $code;
        }
        if($return==='all')
        {
            $response = [
                'code' => $code,
                'url' => $url
            ];  
        }
        return $response;
    }

    /**
     * Check a TXT record in domain zone file
     * 
     * @param string $domain Domain to check TXT record
     * @param string $txt value of TXT record to check for
     *
     * @return bool $exist 
     */
    public static function check_txt_record($domain, $txt)
    {
        $exist = false;
        $res = dns_get_record($domain, DNS_TXT);
        // error_log("check_txt_record : ".print_r($res, true));
        if(isset($res[0]['txt']) && $res[0]['txt']==$txt)
        {
            $exist = true;
        }
        return $exist;
    }

    /**
     * Waits and tests every minute if domain zone has correct IP adress and TXT record set
     *
     * @param string $domain
     * @param string $ip
     * @param string $txt_record
     * @param int $wait_minutes
     * @return bool $is_zone_ok
     */
    public static function wait_for_zone_ok($domain, $ip, $txt_record, $wait_minutes = 15)
    {
        $is_zone_ok = false;

        $ping = false;
        $loops = 0;
        do{
            if(Http::is_zone_ok($domain, $ip, $txt_record))
            {
                $is_zone_ok = true;
                $ping = true;   
            }
            else
            {
                $loops++;
                if($loops > $wait_minutes)
                {
                    $ping = true;       
                }
                else
                {
                    sleep(60);
                }
            }
        } while(!$ping);

        return $is_zone_ok;
    }

    /**
     * Tests if domain zone has correct IP adress and TXT record set
     *
     * @param string $domain
     * @param string $ip
     * @param string $txt_record
     * @return bool $is_zone_ok
     * @todo Mark this function unix and dig command dependant. Look if PHP functions could be used instead (dns_get_record($domain))
     */
    public static function is_zone_ok($domain, $ip, $txt_record)
    {
        $is_zone_ok = false;

        $is_txt_record_ok = false;
        $res = shell_exec('dig -t txt +short ' . $domain);
        $res = explode("\n", $res);
        foreach ($res as $one_txt_record)
        {
            if(trim($one_txt_record, '"')===$txt_record)
            {
                $is_txt_record_ok = true;
                break;
            }

        }
        //error_log(print_r($res, true));

        $is_a_record_ok = true;
        $res = shell_exec('dig -t A +short ' . $domain);
        $res = explode("\n", $res);
        foreach ($res as $one_a_record)
        {
            if(trim($one_a_record, '"')===$ip)
            {
                $is_a_record_ok = true;
                break;
            }

        }
        //error_log(print_r($res, true));

        if($is_txt_record_ok && $is_a_record_ok)
        {
            $is_zone_ok = true; 
        }

        return $is_zone_ok;
    }

    /**
     * Get Name Servers of given domain
     *
     * @param string $domain (ex:eurovision.tv)
     * @return array $name_servers
     */
    public static function get_name_servers($domain)
    {
        $name_servers = [];
        $dnss = dns_get_record($domain, DNS_NS);
        foreach ($dnss as $dns)
        {
            if(isset($dns['target']) && !empty($dns['target']))
            {
                $name_servers[] = $dns['target'];
            }
        }
        return $name_servers;
    }


    /**
     * Add espaced fragment to URL
     *
     * @param string $url
     * @return string $url URL with escaped fragment
     */
    public static function add_escaped_fragment($url)
    {
        $parsed_url = parse_url($url);

        $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : ''; 
        $host     = isset($parsed_url['host']) ? $parsed_url['host'] : ''; 
        $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : ''; 
        $user     = isset($parsed_url['user']) ? $parsed_url['user'] : ''; 
        $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : ''; 
        $pass     = ($user || $pass) ? "$pass@" : ''; 
        $path     = isset($parsed_url['path']) ? $parsed_url['path'] : ''; 
        $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : ''; 
        
        # Add escaped fragment
        if($query==='')
            $escaped_fragment = '?_escaped_fragment_=';
        else
            $escaped_fragment = '&_escaped_fragment_=';

        $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : ''; 
        
        return "$scheme$user$pass$host$port$path$query$escaped_fragment$fragment";

    }


    /**
     *  An example CORS-compliant method.  It will allow any GET, POST, or OPTIONS requests from any origin.
     *
     *  In a production environment, you probably want to be more restrictive, but this gives you the general idea of what is involved.  For the nitty-gritty low-down, read:
     *
     * @link https://developer.mozilla.org/en/HTTP_access_control
     * @link http://www.w3.org/TR/cors/
     *
     */
    public static function cors()
    {
        # Allow from any origin
        if (isset($_SERVER['HTTP_ORIGIN']))
        {
            # Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one you want to allow, and if so:
            header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
            header('Access-Control-Allow-Credentials: true');
            # cache for 1 day
            //header('Access-Control-Max-Age: 86400');
        }

        # Access-Control headers are received during OPTIONS requests
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS')
        {

            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
            {
                # may also be using PUT, PATCH, HEAD etc
                header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, HEAD, DELETE, OPTIONS");
            }

            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
            {
                header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
            }

            exit(0);
        }

        // echo "You have CORS!";
    }

    /**
     *
     * @param string $url
     * @return string $filename
     */
    public static function url_2_filename($url)
    {
        $filename = '';

        $path = parse_url($url); 
        // error_log('parse : '.print_r($path, true));
        $components = tld_extract($url);
        // var_dump($components);
        
        if(isset($path['scheme']) && strlen($path['scheme'])>0)
        {
            $filename = $path['scheme'].'-';
        }

        if($components->subdomain!='')
        {
            $filename .= $components->subdomain.'.';
        }
        if($components->hostname!='')
        {
            $filename .= $components->hostname.'.';
        }
        $filename .= $components->suffix;
        
        $path = parse_url($url); 
        
        if(isset($path['path']) && strlen($path['path'])>0)
        {
            $filename .= str_replace(['/', ' '], '-', $path['path']);
        }
        
        return $filename;
    }

    /**
     * Transforms an URL encoded in IDN to an UTF-8 encoded URL
     *
     * @param string $url
     * @param string $rtrim_slash
     * @return string $new_url
     */
    public static function url_idn_to_utf8($url, $rtrim_slash = false)
    {
        $Punycode = new Punycode();

        $parts = parse_url($url);
        $new_url = http_build_url($url, [
            // 'host' => idn_to_utf8($parts['host'])
            'host' => $Punycode->decode($parts['host'])
            ]);
        if($rtrim_slash)
            $new_url = rtrim($new_url, '/');
        return $new_url;
    }

    /**
     * Transforms an URL encoded in IDN to an ASCII encoded URL
     *
     * @param string $url
     * @param string $rtrim_slash
     * @return string $new_url
     */
    public static function url_idn_to_ascii($url, $rtrim_slash = false)
    {
        /*error_log('url_idn_to_ascii : ' . $url);*/

        $parts = parse_url($url);
        if(isset($parts['host']))
        {
            $Punycode = new Punycode();

            $new_url = http_build_url($url, [
                // 'host' => idn_to_ascii($parts['host'])
                'host' => $Punycode->encode($parts['host'])
            ]);
        }
        else
        {
            $new_url = $url;
        }

        if($rtrim_slash)
        {
            $new_url = rtrim($new_url, '/');
        }
        
        return $new_url;
    }

}

