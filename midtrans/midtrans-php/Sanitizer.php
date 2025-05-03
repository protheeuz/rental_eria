<?php
namespace Midtrans;

class Sanitizer
{
    public static function sanitize($params)
    {
        $sanitized = [];
        foreach ($params as $key => $value) {
            if(is_array($value)) {
                $sanitized[$key] = self::sanitize($value);
            } else {
                $sanitized[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
        }
        return $sanitized;
    }
}
?>