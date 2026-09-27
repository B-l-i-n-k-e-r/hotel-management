<?php
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__FILE__));

// Since your server root is the public folder, strip out subfolder paths
define("SROOT", "http://" . $_SERVER['HTTP_HOST']);
define("IMAGEROOT", SROOT . "/media/images/rooms/");

// Simple native .env loader to parse environment variables
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
define("DB_NAME", getenv("DB_DATABASE")); // database name
define("DB_USER", getenv("DB_USERNAME")); // database user
define("DB_PASSWORD", getenv("DB_PASSWORD")); // database password
define("DB_HOST", getenv("DB_HOST")); // database host

// FTP
define("FTPSERVER", getenv("env_ftp_server"));
define("FTPUSER", getenv("env_ftp_user"));
define("FTPPASS", getenv("env_ftp_pass"));

require_once(ROOT.DS."core".DS."db.php");
require_once(ROOT.DS."core".DS."utils.php");
require_once(ROOT.DS."core".DS."upload_image.php");