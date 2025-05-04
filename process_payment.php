process_payment.php:
<?php
session_start();
include('includes/config.php');
include('includes/format_rupiah.php');
include('includes/library.php');

if (!isset($_SESSION['booking_data']) || !isset($_SESSION['ulogin'])) {
    die("<script>
        alert('Sesi tidak valid!');
        window.location = 'booking.php';
        </script>");
}

// Validasi field wajib
$required_fields = ['vid', 'email', 'fromdate', 'todate', 'durasi', 'biayadriver', 'total'];
foreach ($required_fields as $field) {
    if (!isset($_SESSION['booking_data'][$field])) {
        $params = http_build_query([
            'vid' => $_SESSION['booking_data']['vid'],
            'mulai' => $_SESSION['booking_data']['fromdate'],
            'selesai' => $_SESSION['booking_data']['todate'],
            'driver' => $_SESSION['booking_data']['biayadriver'] > 0 ? 1 : 0,
            'pickup' => $_SESSION['booking_data']['pickup']
        ]);

        die("<script>
            alert('Data booking tidak lengkap!');
            window.location = 'booking_ready.php?$params';
            </script>");
    }
}

// Generate kode booking
try {
    $kode = buatKode("booking", "TRX");
} catch (Exception $e) {
    die("<script>
        alert('Gagal generate kode booking!');
        window.location = 'booking_ready.php';
        </script>");
}

// Simpan ke database
// $sql = "INSERT INTO booking 
//         (kode_booking, id_mobil, tgl_mulai, tgl_selesai, durasi, 
//         driver, status, email, pickup, tgl_booking, total_harga)
//         VALUES(
//             '$kode',
//             " . (int)$_SESSION['booking_data']['vid'] . ",
//             '" . $_SESSION['booking_data']['fromdate'] . "',
//             '" . $_SESSION['booking_data']['todate'] . "',
//             " . (int)$_SESSION['booking_data']['durasi'] . ",
//             " . (int)$_SESSION['booking_data']['driver'] . ", 
//             'pending',
//             '" . mysqli_real_escape_string($koneksidb, $_SESSION['booking_data']['email']) . "',
//             '" . mysqli_real_escape_string($koneksidb, $_SESSION['booking_data']['pickup']) . "',
//             NOW(),
//             " . (int)$_SESSION['booking_data']['total'] . "
//         )";
$sql = "INSERT INTO booking 
        (kode_booking, id_mobil, tgl_mulai, tgl_selesai, durasi, 
        driver, status, email, pickup, tgl_booking, total_harga, snap_token, midtrans_data)
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
            " . (int)$_SESSION['booking_data']['total'] . ",
            '',
            ''
        )";

// Di bagian error handling
if (!mysqli_query($koneksidb, $sql)) {
    error_log("MySQL Error: " . mysqli_error($koneksidb));
    $params = http_build_query([
        'vid' => $_SESSION['booking_data']['vid'],
        'mulai' => $_SESSION['booking_data']['fromdate'],
        'selesai' => $_SESSION['booking_data']['todate'],
        'driver' => $_SESSION['booking_data']['driver'],
        'pickup' => urlencode($_SESSION['booking_data']['pickup'])
    ]);

    die("<script>
        alert('Gagal menyimpan data booking: " . addslashes(mysqli_error($koneksidb)) . "');
        window.location = 'booking_ready.php?$params';
        </script>");
}
// Setup Midtrans
try {
    // Validasi total
    $gross_amount = (int)$_SESSION['booking_data']['total'];
    if ($gross_amount < 1000) {
        throw new Exception("Jumlah pembayaran tidak valid: Rp " . format_rupiah($gross_amount));
    }

    // Data customer
    $useremail = $_SESSION['ulogin'];
    $query_user = mysqli_query($koneksidb, "SELECT * FROM users WHERE email = '$useremail'");
    $user_data = mysqli_fetch_array($query_user);

    // Data transaksi
    $transaction = [
        'transaction_details' => [
            'order_id' => $kode,
            'gross_amount' => $gross_amount,
            'currency' => 'IDR'
        ],
        'item_details' => [
            [
                'id' => 'MOBIL-' . (int)$_SESSION['booking_data']['vid'],
                'price' => $gross_amount,
                'quantity' => 1,
                'name' => 'Sewa Mobil (' . (int)$_SESSION['booking_data']['durasi'] . ' Hari)',
                'category' => 'Car Rental'
            ]
        ],
        'customer_details' => [
            'first_name' => $user_data['nama_user'],
            'email' => $user_data['email'],
            'phone' => $user_data['telp'],
            'billing_address' => [
                'address' => 'Alamat Pengguna',
                'city' => 'Kota Pengguna'
            ]
        ],
        'callbacks' => [
            'finish' => 'http://' . $_SERVER['HTTP_HOST'] . '/booking_detail.php?kode=' . $kode
        ],
        'enable_callback' => true,
        'callback_url' => 'http://' . $_SERVER['HTTP_HOST'] . '/midtrans_notification.php'
    ];

    // Log data transaksi
    error_log("Midtrans Request Data: " . print_r($transaction, true));

    // Dapatkan Snap Token
    $snapToken = \Midtrans\Snap::getSnapToken($transaction);
    $_SESSION['snap_token'] = $snapToken;

    // Update database dengan data Midtrans
    $update_sql = "UPDATE booking 
                  SET snap_token = '" . mysqli_real_escape_string($koneksidb, $snapToken) . "',
                      midtrans_data = '" . mysqli_real_escape_string($koneksidb, json_encode($transaction)) . "'
                  WHERE kode_booking = '$kode'";
    mysqli_query($koneksidb, $update_sql);

    header('Location: payment_page.php?kode=' . $kode);
    exit();
} catch (Exception $e) {
    // Rollback transaksi
    mysqli_query($koneksidb, "DELETE FROM booking WHERE kode_booking='$kode'");
    unset($_SESSION['booking_data']);
    // Log error
    error_log("Midtrans Error: " . $e->getMessage());
    // Tampilkan pesan error
    die("<script>
        alert('Gagal memproses pembayaran: " . addslashes($e->getMessage()) . "');
        window.location = 'booking_ready.php';
        </script>");
}
