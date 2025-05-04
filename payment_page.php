<?php
session_start();
include('includes/config.php');

if (!isset($_GET['kode']) || !isset($_SESSION['snap_token'])) {
    header('Location: booking_ready.php');
    exit();
}

$kode = $_GET['kode'];
$snapToken = $_SESSION['snap_token'];

$useremail = $_SESSION['ulogin'];
$stmt = $koneksidb->prepare("SELECT * FROM booking 
                           WHERE kode_booking = ? 
                           AND snap_token = ? 
                           AND email = ?");
$stmt->bind_param("sss", $kode, $snapToken, $useremail);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    unset($_SESSION['snap_token']);
    die("<script>
        alert('Kode booking tidak valid!');
        window.location = 'booking_ready.php';
        </script>");
}
$booking = $result->fetch_assoc();

// Cek ketersediaan mobil
$checkCar = $koneksidb->prepare("SELECT id_mobil FROM mobil WHERE id_mobil = ?");
$checkCar->bind_param("i", $booking['id_mobil']);
$checkCar->execute();
$carResult = $checkCar->get_result();

if ($carResult->num_rows == 0) {
    die("<script>
        alert('Mobil tidak sudah tidak tersedia!');
        window.location = 'riwayatsewa.php';
        </script>");
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Payment Page</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script type="text/javascript"
        src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key="<?= MIDTRANS_CLIENT_KEY ?>"></script>
</head>

<body>
    <script type="text/javascript">
        try {
            snap.pay('<?= $snapToken ?>', {
                onSuccess: (res) => {
                    fetch(`payment_success.php?kode=<?= $kode ?>`)
                        .then(() => {
                            window.location.href = 'booking_detail.php?kode=<?= $kode ?>';
                        })
                },
                onPending: (res) => window.location.href = 'riwayatsewa.php?kode=<?= $kode ?>',
                onError: (res) => {
                    alert(`Pembayaran gagal: ${res.status_message}`);
                    window.location.href = 'booking.php';
                },
                onClose: () => {
                    if (confirm('Batalkan pembayaran?')) {
                        window.location.href = 'booking.php';
                    }
                }
            });
        } catch (e) {
            alert('Terjadi kesalahan sistem!');
            window.location.href = 'booking_ready.php';
        }
    </script>
</body>

</html>