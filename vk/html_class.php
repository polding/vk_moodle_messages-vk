<?php
#############################################################################
# Author: consros 2011                                                      #
#############################################################################

class HttpTools {    
	const DEFAULT_HEADER = "User-Agent: Mozilla/5.0+(Windows+NT+6.2;+WOW64)+AppleWebKit/537.11+(KHTML,+like+Gecko)+Chrome/23.0.1271.95+Safari/537.11\r\nConnection: Close\r\n";
   
    protected $defaultHeaders;
    protected $cookie;

    public function __construct($headers = self::DEFAULT_HEADER) {
        $this->defaultHeaders = $headers;
        $this->cookie = '';
    }

    public function getCookie() {
        return $this->cookie;
    }
    public function setCookie($cookie) {
        $this->cookie = null == $cookie || '' == $cookie ? $cookie :
            'Cookie: ' . (is_array($cookie) ?
                implode("\r\nCookie: ", $cookie) :
                str_replace('Cookie: ', '', $cookie)) . "\r\n";
    }

    public function sendGetRequest($url, $headerExtra = '', $returnHeaders = false, $redirects = null) {
        $parsedUrl = parse_url($url);
        $host = $parsedUrl['host'];
        //$url  = $this->resolveNames($url, $host);

        $header  = "Host: $host\r\n";
        $header .= $this->defaultHeaders;
        $header .= $headerExtra;
        if (isset($this->cookie) && '' != $this->cookie) {
            $header .= $this->cookie;
        }

        $http = array('method'  => 'GET', 'header'  => $header);
        if (isset($redirects)) {
            $http['max_redirects'] = $redirects;
        }
        $context = stream_context_create(array('http' => $http));

        $response = @file_get_contents($url, false, $context);
//	echo $response;die;
        if (false === $response || '' === $response) {
            if (! isset($http_response_header)) {
                throw new NetworkException($host);
            }
            if (isset($redirects) && $redirects > 0) {
                $headers = $this->formatHeadersArray($http_response_header);
                $location = trim(@$headers['Location']);
                if (! empty($location)) {
                    if (0 !== strpos($location, 'http')) {
                        $location = 'http://' . $host . $location;
                    }
                    return $this->sendGetRequest($location, $headerExtra, 
                        $returnHeaders, $redirects-1);
                }
            }
            $response = '<error>' . $this->getHttpCode($http_response_header) . '</error>';
        }
        return ! $returnHeaders ? $response :
            array($http_response_header, $response);
    }

    public function sendPostRequest($url, $headerExtra = '', $returnHeaders = false) {
        $parsedUrl = parse_url($url);
        $host   = $parsedUrl['host'];
        $params = isset($parsedUrl['query']) ? $parsedUrl['query'] : null;
        //$url    = $this->resolveNames($url, $host);

        $header  = "Host: $host\r\n";
        $header .= $this->defaultHeaders;
        $header .= $headerExtra;
        if (isset($this->cookie) && '' != $this->cookie) {
            $header .= $this->cookie;
        }

        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $context = stream_context_create(array(
            'http' => array(
                'protocol_version' => 1.1,
                'max_redirects' => 1,
                'method'  => 'POST',
                'timeout' => 50,
                'content' => $params,
                'header'  => $header)));

        $url = str_replace("?$params", '', $url);
        $response = @file_get_contents($url, false, $context);
        if (false === $response || '' === $response) {
            if (! isset($http_response_header)) {
                throw new NetworkException($host);
            }
            $response = '<error>' . $this->getHttpCode($http_response_header) . '</error>';
        }
        return ! $returnHeaders ? $response :
            array($http_response_header, $response);
    }

    public function sendMixedPostRequest($url, $params, $headerExtra = '', $returnHeaders = false) {
        $parsedUrl = parse_url($url);
        $host = $parsedUrl['host'];

        $header  = "Host: $host\r\n";
        $header .= $this->defaultHeaders;
        $header .= $headerExtra;
        if (isset($this->cookie) && '' != $this->cookie) {
            $header .= $this->cookie;
        }

        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $context = stream_context_create(array(
            'http' => array(
                'protocol_version' => 1.1,
                'method'  => 'POST',
                'timeout' => 50,
                'content' => $params,
                'header'  => $header)));

        $response = @file_get_contents($url, false, $context);
        if (false === $response || '' === $response) {
            $response = '<error>' . $this->getHttpCode($http_response_header) . '</error>';
        }
        return ! $returnHeaders ? $response :
            array($http_response_header, $response);
    }

    protected function getHttpCode($headers, $takeLast = true) {
        if ($takeLast) {
            $headers = array_reverse($headers);
        }
        foreach ($headers as $header) {
            if (0 === strpos($header, 'HTTP/')) {
                return $header;
            }
        }
        return null;
    }

    # temporary solution for vk.com problem
    protected function resolveNames($url, $host) {
        if (false !== strpos($host, 'vk.com')) {
            $ip = @gethostbyname($host);
            if (empty($ip) || $ip == $host) {
                $url = str_replace('login.vk.com', '93.186.224.244', $url);
                $url = str_replace('vk.com', '87.240.131.97', $url);
            }
        }
        return $url;
    }

    public function getPageCookies($headers) {
        $cookies = array();
        foreach ($headers as $header) {
            if (0 === strpos($header, 'Set-Cookie: ')) {
                $cookie = substr($header, strlen('Set-Cookie: '));
                if (false !== strpos($cookie, ';')) {
                    $cookie = substr($cookie, 0, strpos($cookie, ';'));
                }
                list($name, $value) = explode('=', $cookie);
                if (isset($cookies[$name])) {
                    for ($i = 1; isset($cookies[$name . $i]); $i++);
                    $name .= $i;
                }
                $cookies[$name] = $value;
            }
        }
        return $cookies;
    }

    public function formatHeadersArray($headers) {
        $newHeaders = array();
        foreach ($headers as $header) {
            if (false === strpos($header, ': ')) {
                $newHeaders[] = $header;
            } else {
                list($key, $value) = explode(': ', $header, 2);
                $newHeaders[$key] = $value;
            }
        }
        return $newHeaders;
    }

    public static function checkUrl($url) {
        $headers = get_headers($url);
        return ! empty($headers) && false !== strpos($headers[0], "200 OK");
    }
	
	public function parseParam($scope, $prefix, $suffix, $default = null, $occurence = 1) {
		if (! isset($scope) || ! is_string($scope)) {
            return $default;
        }
        for ($start = 0; $occurence > 0; $occurence--) {
            $start = null == $prefix || '' == $prefix ? 0 :
               strpos($scope, $prefix, $start);
            if (false === $start) {
                return $default;
            }
            $start += strlen($prefix);
        }
        $stop =  null == $suffix || '' == $suffix ? strlen($scope) :
            strpos($scope, $suffix, $start);
        if (false === $stop) {
            return $default;
        }		
        return substr($scope, $start, $stop - $start);
    }	
}
?>
