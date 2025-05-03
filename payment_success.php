<?php
session_start();
include('includes/config.php');

$kode = $_GET['kode'] ?? '';

if (empty($kode)) {
    header('Location: booking_ready.php');
    exit();
}

$query = mysqli_query(
    $koneksidb,
    "SELECT * FROM booking 
    WHERE kode_booking = '$kode' 
    AND status = 'pending'"
);

if (mysqli_num_rows($query) == 0) {
    die("<script>
        alert('Transaksi tidak valid atau sudah diproses!');
        window.location = 'booking_ready.php';
        </script>");
}

mysqli_query(
    $koneksidb,
    "UPDATE booking 
    SET status = 'sukses', 
        tgl_pembayaran = NOW() 
    WHERE kode_booking = '$kode'"
);

unset($_SESSION['booking_data']);
unset($_SESSION['snap_token']);

header("Location: booking_detail.php?kode=$kode");
exit();
