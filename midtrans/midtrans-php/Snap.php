<?php

namespace Midtrans;

use Exception;

class Snap
{
    public static function getSnapToken($params)
    {
        try {
            if (
                empty($params['transaction_details']['order_id']) ||
                empty($params['transaction_details']['gross_amount'])
            ) {
                throw new Exception("Invalid transaction details");
            }

            $payload = [
                'transaction_details' => [
                    'order_id' => $params['transaction_details']['order_id'],
                    'gross_amount' => (int)$params['transaction_details']['gross_amount']
                ],
                'credit_card' => [
                    'secure' => true
                ]
            ];

            $optionalFields = [
                'item_details',
                'customer_details',
                'callbacks',
                'enabled_payments'
            ];

            foreach ($optionalFields as $field) {
                if (isset($params[$field])) {
                    $payload[$field] = $params[$field];
                }
            }

            $result = ApiRequestor::post(
                Config::getSnapBaseUrl() . '/transactions',
                Config::$serverKey,
                $payload
            );

            return $result['token'];
        } catch (Exception $e) {
            throw new Exception('Snap API Error: ' . $e->getMessage());
        }
    }
}
