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
?>

<!DOCTYPE html>
<html>

<head>
    <title>Payment Page</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        .loader {
            border: 16px solid #f3f3f3;
            border-radius: 50%;
            border-top: 16px solid #3498db;
            width: 120px;
            height: 120px;
            animation: spin 2s linear infinite;
            margin: 20% auto;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .payment-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            text-align: center;
        }
    </style>
    <script type="text/javascript"
        src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key="<?= MIDTRANS_CLIENT_KEY ?>"></script>
</head>

<body>
    <?php include('includes/header.php'); ?>

    <div class="payment-container">
        <div class="loader"></div>
        <h3>Mengarahkan ke halaman pembayaran...</h3>
        <p>Kode Booking: <?= $kode ?></p>
        <p>Total Pembayaran: <?= format_rupiah($booking['total_harga']) ?></p>
    </div>

    <script type="text/javascript">
        setTimeout(() => {
            try {
                snap.pay('<?= $snapToken ?>', {
                    onSuccess: (res) => window.location.href = 'payment_success.php?kode=<?= $kode ?>',
                    onPending: (res) => window.location.href = 'payment_pending.php?kode=<?= $kode ?>',
                    onError: (res) => {
                        alert(`Pembayaran gagal: ${res.status_message}`);
                        window.location.href = 'booking_ready.php';
                    },
                    onClose: () => {
                        if (confirm('Batalkan pembayaran?')) {
                            window.location.href = 'booking_ready.php';
                        }
                    }
                });
            } catch (e) {
                alert('Terjadi kesalahan sistem!');
                window.location.href = 'booking_ready.php';
            }
        }, 2000);
    </script>

    <?php include('includes/footer.php'); ?>
</body>

</html>