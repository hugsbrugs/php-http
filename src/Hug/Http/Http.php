<?php

namespace Hug\Http;

# need tldextract
require_once __DIR__ . '/../../../vendor/autoload.php';
// require_once __DIR__ . '/../../../lib/tldextractphp/tldextract.php';
use LayerShifter\TLDExtract\Extract;

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
     * http://stackoverflow.com/questions/6476212/save-image-from-url-with-curl-php
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
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
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
            else
            {
                // error_log('grab_image '. $url .' returns ' . $retcode);
                # Throw warning
                trigger_error('InvalidImageHttpCode', E_USER_NOTICE);
            }
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
     * Extracts a TLD (Top Level Domain) from an URL
     *
     * @param string $url
     * @return string $tld 
     */
    public static function extract_tld_from_url($url)
    {
        $tld = '';
        
        $components = tld_extract($url);
        if($components->hostname!=='')
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
        if($components->subdomain!=='')
        {
            $domain = $components->subdomain.'.';
        }
        if($components->hostname!=='')
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
        if($components->subdomain!=='')
        {
            $subdomain = $components->subdomain;
        }

        return $subdomain;
    }

    /**
     *
     */
    public static function extract_request_headers_body($html_with_headers)
    {
        # METHODE 1
        //list($header, $body) = explode("\r\n\r\n", $html_with_headers, 2);
        
        # METHODE 2 : http://stackoverflow.com/questions/9183178/php-curl-retrieving-response-headers-and-body-in-a-single-request
        /*$header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        error_log("header_size : ".$header_size);
        $header = substr($response, 0, $header_size);
        saveIntoFile("header1.txt", "/home/backrub.fr/public_html/php", "", $header);
        $body = substr($html_with_headers, $header_size);*/

        # METHODE 3 : http://stackoverflow.com/questions/11359276/php-curl-exec-returns-both-http-1-1-100-continue-and-http-1-1-200-ok-separated-b
        // $header = array();
        // $body = array();
        // foreach(explode("\r\n\r\n", $html_with_headers) as $frag)
        // {
        //     if(preg_match('/^HTTP\/[0-9\.]+ [0-9]+/', $frag))
        //     {
        //        $header[] = $frag;
        //     }
        //     else
        //     {
        //         $body[] = $frag;
        //     }
        // }
        // $header = implode("\r\n", $header);
        // $body = implode($body);
        // $Response = array("HEADER" => http_parse_headers($header), "BODY" => $body);
        // return $Response;

        # METHODE 4 : http://stackoverflow.com/questions/9183178/php-curl-retrieving-response-headers-and-body-in-a-single-request
        $parts = explode("\r\n\r\nHTTP/", $html_with_headers);
        if(count($parts) > 1)
        {
            $first_headers = $parts[0];
            $last_parts = array_pop($parts);
            $parts = implode("\r\n\r\n", [$first_headers, $last_parts]);
        }
        else
        {
            $parts = $parts[0];
        }
        
        list($headers, $body) = explode("\r\n\r\n", $parts, 2);
        $Response = array("HEADER" => http_parse_headers($headers), "BODY" => $body);
        return $Response;
    }


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
    public static function get_redirect_url($url)
    {
        $redirect_url = null; 

        $url_parts = @parse_url($url);
        if (!$url_parts) return false;
        if (!isset($url_parts['host'])) return false; //can't process relative URLs
        if (!isset($url_parts['path'])) $url_parts['path'] = '/';

        $sock = fsockopen($url_parts['host'], (isset($url_parts['port']) ? (int)$url_parts['port'] : 80), $errno, $errstr, 30);
        if (!$sock) return false;

        $request = "HEAD " . $url_parts['path'] . (isset($url_parts['query']) ? '?'.$url_parts['query'] : '') . " HTTP/1.1\r\n"; 
        $request .= 'Host: ' . $url_parts['host'] . "\r\n"; 
        $request .= "User-Agent: Mozilla/5.0 (Linux; U; Android 4.0.3; ko-kr; LG-L160L Build/IML74K) AppleWebkit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30\r\n";
        $request .= "Connection: Close\r\n\r\n"; 
        fwrite($sock, $request);
        $response = '';

        while(!feof($sock)) $response .= fread($sock, 8192);
        fclose($sock);

        if (preg_match('/^Location: (.+?)$/m', $response, $matches))
        {
            if ( substr($matches[1], 0, 1) == "/" )
            {
                return $url_parts['scheme'] . "://" . $url_parts['host'] . trim($matches[1]);
            }
            else
            {
                return trim($matches[1]);
            }

        }
        else
        {
            return false;
        }

    }

    /**
     * get_all_redirects()
     * Follows and collects all redirects, in order, for the given URL. 
     *
     * @param string $url
     * @return array
     */
    public static function get_all_redirects($url)
    {
        $redirects = [];
        while ($newurl = Http::get_redirect_url($url))
        {
            if (in_array($newurl, $redirects))
            {
                break;
            }
            $redirects[] = $newurl;
            $url = $newurl;
        }
        return $redirects;
    }

    /**
     * get_final_url()
     * Gets the address that the URL ultimately leads to. 
     * Returns $url itself if it isn't a redirect.
     *
     * @param string $url
     * @return string
     */
    public static function get_final_url($url)
    {
        $redirects = Http::get_all_redirects($url);
        if (count($redirects)>0)
        {
            return array_pop($redirects);
        }
        else
        {
            return $url;
        }
    }

}

