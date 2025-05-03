<?php
session_start();
include('includes/config.php');
require_once 'midtrans/Midtrans.php';

use Midtrans\Notification;

$notification = new Notification();
$order_id = $notification->order_id;
$status = $notification->transaction_status;

// Update status pembayaran
$sql = "UPDATE booking SET 
        status = CASE 
            WHEN '$status' = 'capture' THEN 'sukses'
            WHEN '$status' = 'pending' THEN 'pending'
            ELSE 'gagal'
        END,
        payment_method = '{$notification->payment_type}',
        payment_time = NOW()
        WHERE kode_booking = '$order_id'";

mysqli_query($koneksidb, $sql);

http_response_code(200);
