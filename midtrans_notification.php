<?php
require_once('includes/config.php');
require_once('midtrans/Midtrans.php');

\Midtrans\Config::$serverKey = MIDTRANS_SERVER_KEY;
\Midtrans\Config::$isProduction = false;

$notif = new \Midtrans\Notification();

$transaction = $notif->transaction_status;
$order_id = $notif->order_id;
$fraud = $notif->fraud_status;

$validStatus = ['capture', 'settlement'];
$failedStatus = ['deny', 'cancel', 'expire'];

try {
    $stmt = $koneksidb->prepare("UPDATE booking 
                                SET status = ?, 
                                    payment_method = ?,
                                    tgl_pembayaran = NOW() 
                                WHERE kode_booking = ?");

    if (in_array($transaction, $validStatus)) {
        $status = 'sukses';
        $stmt->bind_param("sss", $status, $notif->payment_type, $order_id);
    } else if (in_array($transaction, $failedStatus)) {
        $status = 'gagal';
        $stmt->bind_param("sss", $status, $notif->payment_type, $order_id);
    }

    if (isset($status)) {
        $stmt->execute();
    }

    http_response_code(200);
} catch (Exception $e) {
    error_log("Notification Error: " . $e->getMessage());
    http_response_code(500);
}
