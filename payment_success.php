<?php
session_start();
include('includes/config.php');

$kode = $_GET['kode'] ?? '';

if (empty($kode)) {
    header('Location: booking_ready.php');
    exit();
}

// Verifikasi ke Midtrans API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.sandbox.midtrans.com/v2/$kode/status");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Authorization: Basic ' . base64_encode(MIDTRANS_SERVER_KEY . ':')
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($httpCode == 200) {
    $statusData = json_decode($response, true);

    // Map status Midtrans ke status lokal
    $statusMapping = [
        'capture' => 'Sudah Dibayar',
        'settlement' => 'Sudah Dibayar',
        'pending' => 'Menunggu Pembayaran',
        'expire' => 'Gagal',
        'cancel' => 'Gagal'
    ];

    // Handle null case bila input adalah null
    $localStatus = isset($statusData['transaction_status'])
        ? $statusMapping[$statusData['transaction_status']]
        : 'Menunggu Pembayaran'; 

    // Update status booking
    $update_sql = "UPDATE booking SET status = '$localStatus' WHERE kode_booking = '$kode'";

    if (mysqli_query($koneksidb, $update_sql)) {
        // Hanya insert ke cek_booking jika status sukses
        if ($localStatus == 'Sudah Dibayar') {
            $bookingQuery = mysqli_query(
                $koneksidb,
                "SELECT id_mobil, tgl_mulai, tgl_selesai 
                 FROM booking 
                 WHERE kode_booking = '$kode'"
            );

            if ($bookingData = mysqli_fetch_assoc($bookingQuery)) {
                $startDate = new DateTime($bookingData['tgl_mulai']);
                $endDate = new DateTime($bookingData['tgl_selesai']);
                $endDate->modify('+1 day');

                $interval = new DateInterval('P1D');
                $period = new DatePeriod($startDate, $interval, $endDate);

                foreach ($period as $date) {
                    $tglBooking = $date->format('Y-m-d');

                    // Cek duplikasi data
                    $checkSql = "SELECT kode_booking FROM cek_booking 
                                WHERE kode_booking = '$kode' 
                                AND tgl_booking = '$tglBooking'";
                    $checkResult = mysqli_query($koneksidb, $checkSql);
                    if (mysqli_num_rows($checkResult) == 0) {
                        $insertSql = "INSERT INTO cek_booking 
                (kode_booking, id_mobil, tgl_booking, status)
                VALUES (
                    '$kode',
                    '{$bookingData['id_mobil']}',
                    '$tglBooking',
                    '$localStatus'
                )";
                        if (!mysqli_query($koneksidb, $insertSql)) {
                            error_log("Gagal insert cek_booking: " . mysqli_error($koneksidb));
                        }
                    }
                }
            }
        }
    } else {
        error_log("Gagal update status booking: " . mysqli_error($koneksidb));
    }

    try {
        $startDate = new DateTime($bookingData['tgl_mulai']);
        $endDate = new DateTime($bookingData['tgl_selesai']);
    } catch (Exception $e) {
        error_log("Format tanggal invalid: " . $e->getMessage());
        exit();
    }
}

curl_close($ch);

unset($_SESSION['booking_data']);
unset($_SESSION['snap_token']);

header("Location: booking_detail.php?kode=$kode");
exit();
