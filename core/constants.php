<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Phoenix\Core;

define ('TMP_DIR', 'tmp');
define ('CACHE_DIR', 'cache');

$PWD = '';
if(isset($_SERVER['DOCUMENT_ROOT'])) {
    if(PHP_OS == 'WINNT') {
        define ('CR_LF', "\r\n");
        define('DOCUMENT_ROOT', str_replace('\\\\', '\\', $_SERVER['DOCUMENT_ROOT']) . '\\');
    } elseif(PHP_OS == 'Linux') {
        define ('CR_LF', "\r");
        define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT'] . '/');    
    } else {
        define ('CR_LF', "\r");
        define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT'] . '/');    
    }
    if(strstr($_SERVER['SERVER_SOFTWARE'], 'IIS')) {
        define('HTTP_PROTOCOL', ($_SERVER['HTTPS'] == 'off') ? 'http' : 'https');
    } elseif(strstr($_SERVER['SERVER_SOFTWARE'], 'Apache')) {
        define('HTTP_PROTOCOL', $_SERVER['REQUEST_SCHEME']);
    }

    define('LOG_PATH', DOCUMENT_ROOT . 'logs/');
} else {
    define('DOCUMENT_ROOT', '');
    define('LOG_PATH', DOCUMENT_ROOT . './');
}

define('PAGE_NUMBER', 'pagen');
define('PAGE_COUNT', 'pagec');
define('PAGE_NUMBER_DEFAULT', 1);
define('PAGE_COUNT_DEFAULT', 20);
define('MAIN_VIEW', 'main');
define('LOGIN_VIEW', 'login');
define('MASTER_VIEW', 'master');
define('HOME_VIEW', 'home');
define('MAIN_PAGE', '/' . MAIN_VIEW . '.html');
define('LOGIN_PAGE', '/' . LOGIN_VIEW . '.html');
define('MASTER_PAGE', '/' . MASTER_VIEW . '.html');
define('HOME_PAGE', '/' . HOME_VIEW . '.html');
define('LOG_FILE', LOG_PATH . 'debug.log');
define('APP_DATA', DOCUMENT_ROOT . 'data/');
define('APP_BUSINESS', DOCUMENT_ROOT . 'app/business/');
define('STARTER_FILE', 'starter.php');
define('HTTP_ACCEPT', $_SERVER['HTTP_ACCEPT']);
define('HTTP_PORT', $_SERVER['SERVER_PORT']);
define('REQUEST_URI', $_SERVER['REQUEST_URI']);
define('QUERY_STRING', $_SERVER['QUERY_STRING']);
define('SERVER_NAME', $_SERVER['SERVER_NAME']);
define('SERVER_ROOT', HTTP_PROTOCOL . '://' . SERVER_NAME . ((HTTP_PORT != '80') ? ':' . HTTP_PORT : ''));
define('ROOT_NAMESPACE', 'Phoenix');
define('ROOT_PATH', 'phoenix');
define('DEFALT_MODEL', ROOT_NAMESPACE . '\\MVC\\TModel');
define('DEFAULT_CONTROLLER', ROOT_NAMESPACE . '\\MVC\\TController');
define('DEFAULT_PARTIAL_CONTROLLER', ROOT_NAMESPACE . '\\MVC\\TPartialController');
define('DEFAULT_CONTROL', ROOT_NAMESPACE . '\\Web\\UI\\TControl');
define('CONTROLLER', 'TController');
define('PARTIAL_CONTROLLER', 'TPartialController');
define('CONTROL', 'TControl');
define('CLASS_EXTENSION', '.class.php');
define('PREHTML_EXTENSION', '.phtml');
define('PATTERN_EXTENSION', '.pattern' . PREHTML_EXTENSION);
define('JS_EXTENSION', '.js');
define('JSON_EXTENSION', '.json');
define('PHX_TERMINATOR', '<phx:eof />');
define('CREATIONS_PLACEHOLDER', '<phx:creationsPlaceHolder />');
define('ADDITIONS_PLACEHOLDER', '<phx:additionsPlaceHolder />');
define('HTML_PLACEHOLDER', '<phx:htmlPlaceHolder />');
define('CONTROL_ADDITIONS', CR_LF . "\tpublic function createObjects() {" . CR_LF . CREATIONS_PLACEHOLDER . CR_LF . "\t}" . CR_LF . CR_LF . "\tpublic function declareObjects() {" . CR_LF . ADDITIONS_PLACEHOLDER . CR_LF . "\t}" . CR_LF . CR_LF . "\tpublic function displayHtml() {" . CR_LF . "?>" . CR_LF . HTML_PLACEHOLDER . CR_LF . "<?php" . CR_LF . "\t}" . CR_LF . '}' . CR_LF);
//if(file_exists(STARTER_FILE)) {
//    unlink(STARTER_FILE);
//    file_put_contents(STARTER_FILE, "<?php\n", FILE_APPEND);
//    
//}