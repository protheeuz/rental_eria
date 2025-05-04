<?php
namespace Midtrans;

require_once __DIR__.'/midtrans-php/Config.php';
require_once __DIR__.'/midtrans-php/ApiRequestor.php';
require_once __DIR__.'/midtrans-php/Sanitizer.php';
require_once __DIR__.'/midtrans-php/Snap.php';
require_once __DIR__.'/midtrans-php/Notification.php';
require_once __DIR__.'/midtrans-php/Transaction.php';

class Midtrans {
    public static function config($params) {
        \Midtrans\Config::$serverKey = $params['server_key'];
        \Midtrans\Config::$isProduction = $params['production'];
        \Midtrans\Config::$isSanitized = $params['sanitized'];
        \Midtrans\Config::$is3ds = $params['3ds'];
    }
}
?>