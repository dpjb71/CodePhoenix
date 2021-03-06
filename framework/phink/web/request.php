<?php
/*
 * Copyright (C) 2019 David Blanchard
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Phink\Web;

use Phink\Registry\TRegistry;

/**
 * Description of pagehandler
 *
 * @author David
 */


class TRequest extends \Phink\Core\TObject
{
    //put your code here

    private $_queryArguments = null;
    private $_isAJAX = false;
    private $_isJSONP = false;
    private $_isEncrypted = false;
    private $_isPartialView = false;
    private $_callbackAction = '';
    private $_contents = array();
    private $_subRequests = array();
    private $_isView = '';

    public function __construct()
    {
        $this->_queryArguments = $_REQUEST;
        $callback = '';
        if (strstr(HTTP_ACCEPT, 'application/json, text/javascript') || $this->getQueryArguments('ajax')) {
            if (strstr(HTTP_ACCEPT, 'request/partialview') || $this->getQueryArguments('partial')) {
                $this->_isPartialView = true;
            }
            if (strstr(HTTP_ACCEPT, 'request/view')) {
                $this->_isView = true;
            }
            $this->_isAJAX = true;
            $callback = $this->getCallbackAction();
        }
        $this->_isJSONP = ($callback != '');
    }

    private function _getRedirection($url): string
    {
        $result = '';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($ch);
        if (preg_match('#Location: (.*)#', $res, $r)) {
            $result = trim($r[1]);
        }

        return $result;
    }

    private function _getHeader(string $method, string $uri): array
    {
        $cookie = session_id();
        $host = HTTP_HOST;
        $ua = HTTP_USER_AGENT;

        $url = parse_url($uri);
        if ($url['host'] === '') {
            $uri = SERVER_ROOT . '/' . $uri;
        }

        $result = [
            // "$method $uri HTTP/1.0",
            "Content-Type:text/html; charset=UTF-8",
            "Accept:text/html, */*; q=0.01",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "User-Agent: $ua"
        ];

        if (HTTP_ORIGIN !== '') {
            array_push($result, 'Origin:' . HTTP_ORIGIN);
        }

        return $result;
    }

    private function _getCustomHeaders(string $method, string $uri, array $headers): array
    {
        $result = [];
        $cookie = session_id();
        $host = HTTP_HOST;
        $ua = HTTP_USER_AGENT;

        $url = parse_url($uri);
        if ($url['host'] === '') {
            $uri = SERVER_ROOT . '/' . $uri;
        }

        array_push($result, "$method $uri HTTP/1.0");

        foreach ($result as $key => $value) {
            array_push($result, "$key: $value");
        }

        array_push($result, "User-Agent:$ua");

        return $result;
    }

    private function _getViewHeader(string $page): array
    {
        $cookie = session_id();
        $host = HTTP_HOST;
        $ua = HTTP_USER_AGENT;

        $url = parse_url($page);
        if ($url['host'] === '') {
            $page = SERVER_ROOT . '/' . $page;
        }

        $result = [
            "POST " . $page . " HTTP/1.0",
            "Content-Type:application/x-www-form-urlencoded; charset=UTF-8",
            "Accept:application/json, text/javascript, request/view, */*; q=0.01",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            //            , "Cookie:PHPSESSID=$cookie"
            //            , "Host:$host"
            "User-Agent:$ua"
        ];

        if (HTTP_ORIGIN !== '') {
            array_push($result, 'Origin:' . HTTP_ORIGIN);
        }

        return $result;
    }

    public function addSubRequest(string $name, string $method, string $uri, ?array $headers = [], $data = null): void
    {
        if (count($headers) > 0) {
            $headers = $this->_getCustomHeaders($method, $uri, $headers);;
        } else {
            $headers = $this->_getHeader($method, $uri);
        }
        $this->_subRequests[$name] = ['uri' => $uri, 'header' => $headers, 'data' => $data];
    }

    public function addViewSubRequest(string $name, string $uri, $data = null): void
    {
        $header = $this->_getViewHeader($uri);
        $data['action'] = 'getViewHtml';
        $this->_subRequests[$name] = ['uri' => $uri, 'header' => $header, 'data' => $data];
    }

    public function execSubRequests(): array
    {
        $result = array();

        foreach ($this->_subRequests as $name => $request) {

            //$certpath = DOCUMENT_ROOT . 'cert' . DIRECTORY_SEPARATOR . 'birdy.crt';
            $certpath = 'ca-bundle.crt';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $request['uri']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            //            curl_setopt($ch, CURLOPT_CAINFO, $certpath);
            //            curl_setopt($ch, CURLOPT_CAPATH, $certpath);
            if (count($request['header']) > 0) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $request['header']);
            }
            if (is_array($request['data'])) {
                $queryString = http_build_query($request['data']);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $queryString);
            }
            curl_setopt($ch, CURLINFO_HEADER_OUT, 1);

            $html = curl_exec($ch);
            $error = curl_error($ch);
            $errno = curl_errno($ch);

            $info = curl_getinfo($ch);

            $header = (isset($info['request_header'])) ? $info['request_header'] : '';

            if ($errno > 0) {
                throw new \Exception($error, $errno);
            }
            if ($header == '') {
                throw new \Exception("Curl is not working fine for some reason. Are you using Android ?");
            }

            $code = $info['http_code'];
            curl_close($ch);

            $result[$name] = (object) ['code' => (int) $code, 'header' => $header, 'html' => $html];

            self::$logger->dump('subrequests result', $result);
        }

        return $result;
    }

    public function execAsyncSubRequests(): array
    {
        $result = [];

        $mh = curl_multi_init();
        $certpath = DOCUMENT_ROOT . 'cert' . DIRECTORY_SEPARATOR . 'birdy.crt';
        $certpath = 'ca-bundle.crt';

        foreach ($this->_subRequests as $name => $request) {
            $ch = curl_init($request['uri']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
            //            curl_setopt($ch, CURLOPT_CAINFO, $certpath);
            //            curl_setopt($ch, CURLOPT_CAPATH, $certpath);
            if (is_array($request['data'])) {
                $queryString = http_build_query($request['data']);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $request['header']);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $queryString);
            }
            curl_multi_add_handle($mh, $ch);
        }

        $still_running = true;
        $this->_backgroundSubrequests($mh, $still_running); // start requests
        do { // "wait for completion"-loop
            curl_multi_select($mh); // non-busy (!) wait for state change
            $this->_backgroundSubrequests($mh, $still_running); // get new state
            while ($info = curl_multi_info_read($mh)) {
                // process completed request (e.g. curl_multi_getcontent($info['handle']))
                $ch = $info['handle'];
                $requestInfo = curl_getinfo($ch);
                $header = (isset($requestInfo['request_header'])) ? $requestInfo['request_header'] : '';

                if ($header == '') {
                    throw new \Exception("Curl is not working fine for some reason. Are you using Android ?");
                }

                $code = $requestInfo['http_code'];
                $html = curl_multi_getcontent($ch);

                $name = $this->_identifySubRequest($header);
                unset($this->_subRequests[$name]);
                $result[$name] = ['code' => $code, 'header' => $header, 'html' => $html];
            }
        } while ($still_running);

        return $result;
    }

    private function _backgroundSubrequests($mh, &$still_active): int
    {
        do {
            $status = curl_multi_exec($mh, $still_active);
        } while ($status === CURLM_CALL_MULTI_PERFORM || $still_active);

        return $status;
    }

    private function _identifySubRequest(string $header): string
    {
        $result = '';

        foreach ($this->_subRequests as $name => $request) {
            if (strstr($header, $request['uri'])) {
                $result = $name;
                break;
            }
        }

        return $result;
    }


    public function getToken(): string
    {
        return $this->getQueryArguments('token');
    }

    public static function getQueryStrinng(string $arg = null)
    {
        $result = false;

        if (!($result = filter_input(INPUT_POST, $arg, FILTER_DEFAULT))) {
            $result = filter_input(INPUT_GET, $arg, FILTER_DEFAULT);
        }

        return $result;
    }

    public static function getArgument($arg, $default = '')
    {
        $result = '';

        // mysql_escape_string
        if (isset($_POST[$arg])) {
            $result = filter_input(INPUT_POST, $arg, FILTER_SANITIZE_STRING);
            if (is_array($_POST[$arg])) {
                $result = filter_input(INPUT_POST, $arg, FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
            }
            return $result;
        }

        if (isset($_GET[$arg])) {
            $result = filter_input(INPUT_GET, $arg, FILTER_SANITIZE_STRING);
            return $result;
        }

        if ($result === '') {
            $result = $default;
        }
        return $result;
    }

    public function getQueryArguments(string $arg = null)
    {
        if (!isset($_REQUEST[$arg])) return false;

        return self::getQueryStrinng($arg);
    }

    public function getArgumentsNames(): array
    {
        return array_keys($_REQUEST);
    }

    public function isEncrypted()
    {
        return $this->_isEncrypted;
    }

    public function isJSONP(): bool
    {
        return $this->_isJSONP;
    }

    public function isAJAX(): bool
    {
        return $this->_isAJAX;
    }

    public function isView(): bool
    {
        return $this->_isView;
    }

    public function isPartialView(): bool
    {
        return $this->_isPartialView;
    }

    public function getCallbackAction(): string
    {
        if (empty($this->_callbackAction)) {
            $this->_callbackAction = (isset($_REQUEST['callback'])) ? $_REQUEST['callback'] : '';
        }
        return $this->_callbackAction;
    }

    public function registerContents($name, $content): void
    {
        TRegistry::write('contents', $name, $content);
    }

    public function getRegisteredContents($name)
    {
        return TRegistry::read('contents', $name);
    }
}
