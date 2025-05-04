<?php
session_start();
include('includes/config.php');
include('includes/format_rupiah.php');
include('includes/library.php');

header('Content-Type: application/json');

try {
    // [1] Validasi Sesi dan Data
    if (!isset($_SESSION['booking_data']) || !isset($_SESSION['ulogin'])) {
        throw new Exception('Sesi tidak valid');
    }

    // [2] Generate Kode Booking
    $kode = buatKode("booking", "TRX");

    // [3] Simpan ke Database
    $sql = "INSERT INTO booking 
            (kode_booking, id_mobil, tgl_mulai, tgl_selesai, durasi, 
            driver, status, email, pickup, tgl_booking, total_harga)
            VALUES(
                '$kode',
                " . (int)$_SESSION['booking_data']['vid'] . ",
                '" . $_SESSION['booking_data']['fromdate'] . "',
                '" . $_SESSION['booking_data']['todate'] . "',
                " . (int)$_SESSION['booking_data']['durasi'] . ",
                " . (int)$_SESSION['booking_data']['driver'] . ", 
                'pending',
                '" . mysqli_real_escape_string($koneksidb, $_SESSION['booking_data']['email']) . "',
                '" . mysqli_real_escape_string($koneksidb, $_SESSION['booking_data']['pickup']) . "',
                NOW(),
                " . (int)$_SESSION['booking_data']['total'] . "
            )";

    if (!mysqli_query($koneksidb, $sql)) {
        throw new Exception('Gagal menyimpan booking: ' . mysqli_error($koneksidb));
    }

    // [4] Setup Transaksi Midtrans
    $gross_amount = (int)$_SESSION['booking_data']['total'];
    $user = mysqli_fetch_array(mysqli_query($koneksidb, 
        "SELECT * FROM users WHERE email = '" . $_SESSION['ulogin'] . "'"));

    $transaction = [
        'transaction_details' => [
            'order_id' => $kode,
            'gross_amount' => $gross_amount,
            'currency' => 'IDR'
        ],
        'customer_details' => [
            'first_name' => $user['nama_user'],
            'email' => $user['email'],
            'phone' => $user['telp']
        ]
    ];

    // [5]  Snap Token
    $snapToken = \Midtrans\Snap::getSnapToken($transaction);

    // [6] Update Snap Token ke Database
    mysqli_query($koneksidb, 
        "UPDATE booking SET snap_token = '$snapToken' 
         WHERE kode_booking = '$kode'");

    // [7] Return Response JSON
    echo json_encode([
        'success' => true,
        'snap_token' => $snapToken,
        'kode' => $kode
    ]);

} catch (Exception $e) {
    // [8] Handle Error
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}