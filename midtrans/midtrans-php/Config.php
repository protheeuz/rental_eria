<?php
namespace Midtrans;

class Config
{
    public static $serverKey;
    public static $isProduction = false;
    public static $isSanitized = true;
    public static $is3ds = true;
    
    const SANDBOX_BASE_URL = 'https://api.sandbox.midtrans.com/v2';
    const PRODUCTION_BASE_URL = 'https://api.midtrans.com/v2';
    const SNAP_SANDBOX_BASE_URL = 'https://app.sandbox.midtrans.com/snap/v1';
    const SNAP_PRODUCTION_BASE_URL = 'https://app.midtrans.com/snap/v1';
    
    public static function getBaseUrl()
    {
        return self::$isProduction ? 
            self::PRODUCTION_BASE_URL : 
            self::SANDBOX_BASE_URL;
    }
    
    public static function getSnapBaseUrl()
    {
        return self::$isProduction ? 
            self::SNAP_PRODUCTION_BASE_URL : 
            self::SNAP_SANDBOX_BASE_URL;
    }
}
?>