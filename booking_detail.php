<?php
session_start();
include('includes/config.php');
include('includes/format_rupiah.php');
include('includes/library.php');

if (strlen($_SESSION['ulogin']) == 0) {
	header('location:index.php');
	exit();
}

$kode = $_GET['kode'] ?? '';

// Validasi kode booking
$stmt = $koneksidb->prepare("SELECT booking.*, mobil.*, merek.* 
                            FROM booking 
                            JOIN mobil ON booking.id_mobil = mobil.id_mobil
                            JOIN merek ON mobil.id_merek = merek.id_merek
                            WHERE booking.kode_booking = ? 
                            AND booking.email = ?");
$stmt->bind_param("ss", $kode, $_SESSION['ulogin']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
	die("<script>
        alert('Transaksi tidak ditemukan!');
        window.location = 'riwayatsewa.php';
        </script>");
}

$booking = $result->fetch_assoc();

// Cek status pembayaran
if ($booking['status'] == 'pending') {
	require_once('midtrans/Midtrans.php');

	\Midtrans\Config::$serverKey = MIDTRANS_SERVER_KEY;
	\Midtrans\Config::$isProduction = false;

	try {
		$status = \Midtrans\Transaction::status($kode);

		if (in_array($status->transaction_status, ['capture', 'settlement'])) {
			$update_stmt = $koneksidb->prepare("UPDATE booking SET status = 'sukses' WHERE kode_booking = ?");

			if ($update_stmt === false) {
				throw new Exception("Error preparing statement: " . $koneksidb->error);
			}

			$update_stmt->bind_param("s", $kode);

			if (!$update_stmt->execute()) {
				throw new Exception("Execute error: " . $update_stmt->error);
			}

			$booking['status'] = 'sukses';
		}
	} catch (Exception $e) {
		error_log("Status update error: " . $e->getMessage());
		echo "<div class='alert alert-danger'>Error system: " . htmlentities($e->getMessage()) . "</div>";
	}
}

// Validasi biaya driver
$sqldriver = "SELECT * FROM tblpages WHERE id='0'";
$resultdriver = mysqli_fetch_array(mysqli_query($koneksidb, $sqldriver));
$driver_cost_per_day = (int)str_replace('.', '', $resultdriver['detail']);

// Hitung biaya
$harga = (int)str_replace('.', '', $booking['harga']);
$durasi = $booking['durasi'];
$totalmobil = $harga * $durasi;
$drivercharges = $booking['driver'] * $driver_cost_per_day * $durasi;
$totalsewa = $totalmobil + $drivercharges;

// Hitung tanggal jatuh tempo
$tglmulai = strtotime($booking['tgl_mulai']);
$jmlhari = 86400 * 1;
$tgl = $tglmulai - $jmlhari;
$tglhasil = date("Y-m-d", $tgl);
?>
<!DOCTYPE HTML>
<html lang="en">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<meta name="keywords" content="">
	<meta name="description" content="">
	<title>Rental Mobil</title>
	<link rel="stylesheet" href="assets/css/bootstrap.min.css" type="text/css">
	<link rel="stylesheet" href="assets/css/style.css" type="text/css">
	<link rel="stylesheet" href="assets/css/owl.carousel.css" type="text/css">
	<link rel="stylesheet" href="assets/css/owl.transitions.css" type="text/css">
	<link href="assets/css/slick.css" rel="stylesheet">
	<link href="assets/css/bootstrap-slider.min.css" rel="stylesheet">
	<link href="assets/css/font-awesome.min.css" rel="stylesheet">
	<link rel="stylesheet" id="switcher-css" type="text/css" href="assets/switcher/css/switcher.css" media="all">
	<link rel="alternate stylesheet" type="text/css" href="assets/switcher/css/red.css" title="red" media="all" data-default-color="true">
	<link rel="alternate stylesheet" type="text/css" href="assets/switcher/css/orange.css" title="orange" media="all">
	<link rel="alternate stylesheet" type="text/css" href="assets/switcher/css/blue.css" title="blue" media="all">
	<link rel="alternate stylesheet" type="text/css" href="assets/switcher/css/pink.css" title="pink" media="all">
	<link rel="alternate stylesheet" type="text/css" href="assets/switcher/css/green.css" title="green" media="all">
	<link rel="alternate stylesheet" type="text/css" href="assets/switcher/css/purple.css" title="purple" media="all">
	<link rel="apple-touch-icon-precomposed" sizes="144x144" href="assets/images/favicon-icon/apple-touch-icon-144-precomposed.png">
	<link rel="apple-touch-icon-precomposed" sizes="114x114" href="assets/images/favicon-icon/apple-touch-icon-114-precomposed.html">
	<link rel="apple-touch-icon-precomposed" sizes="72x72" href="assets/images/favicon-icon/apple-touch-icon-72-precomposed.png">
	<link rel="apple-touch-icon-precomposed" href="assets/images/favicon-icon/apple-touch-icon-57-precomposed.png">
	<link rel="shortcut icon" href="assets/images/favicon-icon/favicon.png">
	<link href="https://fonts.googleapis.com/css?family=Lato:300,400,700,900" rel="stylesheet">
	<!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body>
	<?php include('includes/colorswitcher.php'); ?>
	<?php include('includes/header.php'); ?>

	<section class="user_profile inner_pages">
		<center>
			<h3>Detail Sewa</h3>
		</center>
		<div class="container">
			<div class="user_profile_info">
				<div class="col-md-12 col-sm-10">
					<form method="post" name="sewa">
						<div class="form-group">
							<label>Kode Sewa</label>
							<input type="text" class="form-control" value="<?= htmlentities($booking['kode_booking']) ?>" readonly>
						</div>

						<div class="form-group">
							<label>Mobil</label>
							<input type="text" class="form-control"
								value="<?= htmlentities($booking['nama_merek']) ?>, <?= htmlentities($booking['nama_mobil']) ?>" readonly>
						</div>

						<div class="form-group">
							<label>Tanggal Mulai</label>
							<input type="date" class="form-control" value="<?= $booking['tgl_mulai'] ?>" readonly>
						</div>

						<div class="form-group">
							<label>Tanggal Selesai</label>
							<input type="date" class="form-control" value="<?= $booking['tgl_selesai'] ?>" readonly>
						</div>

						<div class="form-group">
							<label>Durasi</label>
							<input type="text" class="form-control" value="<?= $durasi ?> Hari" readonly>
						</div>

						<div class="form-group">
							<label>Biaya Mobil (<?= $durasi ?> Hari)</label>
							<input type="text" class="form-control" value="<?= format_rupiah($totalmobil) ?>" readonly>
						</div>

						<div class="form-group">
							<label>Biaya Driver (<?= $durasi ?> Hari)</label>
							<input type="text" class="form-control" value="<?= format_rupiah($drivercharges) ?>" readonly>
						</div>

						<div class="form-group">
							<label>Total Biaya Sewa</label>
							<input type="text" class="form-control" value="<?= format_rupiah($totalsewa) ?>" readonly>
						</div>

						<?php if ($booking['status'] == "Menunggu Pembayaran"): ?>
							<?php
							$sqlrek = "SELECT * FROM tblpages WHERE id='5'";
							$queryrek = mysqli_query($koneksidb, $sqlrek);
							$resultrek = mysqli_fetch_array($queryrek);
							?>
							<div class="alert alert-warning">
								<b>*Silahkan transfer total biaya sewa ke <?= $resultrek['detail'] ?>
									maksimal tanggal <?= IndonesiaTgl($tglhasil) ?>.</b>
							</div>
						<?php endif; ?>

						<div class="form-group">
							<a href="detail_cetak.php?kode=<?= $kode ?>" target="_blank" class="btn btn-primary">
								<i class="fa fa-print"></i> Cetak
							</a>
						</div>
					</form>
				</div>
			</div>
		</div>
	</section>

	<?php include('includes/footer.php'); ?>

	<script src="assets/js/jquery.min.js"></script>
	<script src="assets/js/bootstrap.min.js"></script>
	<script src="assets/js/interface.js"></script>
	<script src="assets/switcher/js/switcher.js"></script>
	<script src="assets/js/bootstrap-slider.min.js"></script>
	<script src="assets/js/slick.min.js"></script>
	<script src="assets/js/owl.carousel.min.js"></script>
</body>

</html>
<?php  ?>