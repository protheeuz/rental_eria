<?php
namespace Midtrans;

use Exception;

class Snap
{
    public static function getSnapToken($params)
    {
        try {
            $params = Sanitizer::sanitize($params);
            
            $payloads = [
                'transaction_details' => [
                    'order_id' => $params['transaction_details']['order_id'],
                    'gross_amount' => $params['transaction_details']['gross_amount']
                ]
            ];

            if(isset($params['item_details'])) {
                $payloads['item_details'] = $params['item_details'];
            }

            if(isset($params['customer_details'])) {
                $payloads['customer_details'] = $params['customer_details'];
            }

            $result = ApiRequestor::post(
                Config::getSnapBaseUrl() . '/transactions',
                Config::$serverKey,
                $payloads
            );

            return $result['token'];
            
        } catch (Exception $e) {
            throw new Exception('Snap API Error: ' . $e->getMessage());
        }
    }
}
?>