<?php
// set os_encoding
if (PHP_OS == 'Linux') {
    $tmp = explode('.', setlocale(LC_CTYPE, 0));
    $os_encoding = $tmp[1];
} else if (substr(PHP_OS, 0, 3) == 'WIN') {
    $os_encoding = 'CP949';
} else {
    $os_encoding = 'utf-8';
}
define('OS_ENCODING', $os_encoding);

// query_string
if (!isset($_SERVER['QUERY_STRING'])) {
    $_SERVER['QUERY_STRING'] = '';
}

define('ROOT', dirname(__FILE__));
define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . str_replace('/?' . $_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']));
define('BACKUP_PATH', ROOT . '/backup');

// set timezone. Select from http://www.php.net/manual/en/timezones.php
define('TIMEZONE', 'asia/seoul');
date_default_timezone_set(TIMEZONE);
