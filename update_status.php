<?php
session_start();
include('includes/config.php');

if (!isset($_GET['kode']) || !isset($_GET['status'])) {
    die(json_encode(['error' => 'Parameter tidak valid']));
}

$kode = $_GET['kode'];
$status = $_GET['status'];
$allowedStatus = ['pending', 'success', 'failed', 'canceled'];

// Validasi status
if (!in_array($status, $allowedStatus)) {
    die(json_encode(['error' => 'Status tidak valid']));
}

// Update database dengan prepared statement
$stmt = $koneksidb->prepare("UPDATE booking SET status = ? WHERE kode_booking = ?");
$stmt->bind_param('ss', $status, $kode);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Gagal update status: ' . $stmt->error]);
}
