<?php
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__FILE__));

// Base URL — includes /public because that's the document root on local
define("SROOT", "http://" . $_SERVER['HTTP_HOST'] . "/public");
define("IMAGEROOT", SROOT . "/media/images/rooms/");

// Simple native .env loader
$envPath = dirname(ROOT) . DS . '.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv("{$name}={$value}");
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

// Database
define("DB_NAME", getenv("DB_DATABASE"));
define("DB_USER", getenv("DB_USERNAME"));
define("DB_PASSWORD", getenv("DB_PASSWORD"));
define("DB_HOST", getenv("DB_HOST"));

// Mail
define("MAIL_HOST",      getenv("MAIL_HOST"));
define("MAIL_PORT",      (int)getenv("MAIL_PORT"));
define("MAIL_USERNAME",  getenv("MAIL_USERNAME"));
define("MAIL_PASSWORD",  getenv("MAIL_PASSWORD"));
define("MAIL_FROM_NAME", getenv("MAIL_FROM_NAME"));
define("MAIL_FROM",      getenv("MAIL_FROM"));
define("MAIL_ADMIN",     getenv("MAIL_ADMIN"));

// FTP
define("FTPSERVER", getenv("env_ftp_server"));
define("FTPUSER", getenv("env_ftp_user"));
define("FTPPASS", getenv("env_ftp_pass"));

require_once(ROOT.DS."core".DS."db.php");
require_once(ROOT.DS."core".DS."utils.php");
require_once(ROOT.DS."core".DS."upload_image.php");