<?php
namespace Midtrans;

use Exception;

class Transaction
{
    public static function status($orderId)
    {
        try {
            $url = Config::getBaseUrl() . "/$orderId/status";
            
            $headers = [
                'Accept: application/json',
                'Authorization: Basic ' . base64_encode(Config::$serverKey . ':')
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

            $response = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if(curl_errno($ch)) {
                throw new Exception(curl_error($ch));
            }

            curl_close($ch);

            $result = json_decode($response, true);
            
            if($httpcode >= 400) {
                throw new Exception($result['error_messages'][0] ?? 'Midtrans API Error');
            }

            return (object)$result;
            
        } catch (Exception $e) {
            throw new Exception("Transaction status error: " . $e->getMessage());
        }
    }
}