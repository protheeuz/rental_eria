<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('includes/config.php');
include('includes/format_rupiah.php');
include('includes/library.php');

// Validasi parameter GET
$required_params = ['vid', 'mulai', 'selesai', 'driver', 'pickup'];
foreach ($required_params as $param) {
	if (!isset($_GET[$param])) {
		die("<script>
            alert('Parameter tidak valid!');
            window.location = 'booking.php';
            </script>");
	}
}

if (strlen($_SESSION['ulogin']) == 0) {
	header('location:index.php');
	exit();
}

if (isset($_POST['submit'])) {
	$total_input = $_POST['total'];
	$total_numeric = (int)preg_replace('/[^0-9]/', '', $total_input);

	if ($total_numeric < 1000) {
		// Redirect kembali dengan parameter GET
		$params = http_build_query($_GET);
		die("<script>
            alert('Minimum pembayaran Rp 1.000');
            window.location = 'booking_ready.php?$params';
            </script>");
	}

	$_SESSION['booking_data'] = [
		'vid' => (int)$_POST['vid'],
		'email' => filter_var($_POST['email'], FILTER_SANITIZE_EMAIL),
		'fromdate' => $_POST['fromdate'],
		'todate' => $_POST['todate'],
		'durasi' => (int)$_POST['durasi'],
		'pickup' => $_POST['pickup'],
		'driver' => (int)$_GET['driver'],
		'biayadriver' => (int)$_POST['biayadriver'],
		'total' => $total_numeric
	];

	header('Location: process_payment.php');
	exit();
}

// Ambil parameter GET dengan sanitasi
$email = $_SESSION['ulogin'];
$vid = (int)$_GET['vid'];
$mulai = $_GET['mulai'];
$selesai = $_GET['selesai'];
$driver = ($_GET['driver'] == "1") ? 1 : 0;
$pickup = $_GET['pickup'];

// Validasi tanggal
try {
	$start = new DateTime($mulai);
	$finish = new DateTime($selesai);
	$durasi = $start->diff($finish)->days + 1;
} catch (Exception $e) {
	die("<script>
        alert('Format tanggal tidak valid!');
        window.location = 'booking.php';
        </script>");
}

// Validasi biaya driver
$sqldriver = "SELECT * FROM tblpages WHERE id='0'";
$resultdriver = mysqli_fetch_array(mysqli_query($koneksidb, $sqldriver));
if (!$resultdriver) {
	die("<script>
        alert('Gagal memuat biaya driver!');
        window.location = 'booking.php';
        </script>");
}
$driver_cost_per_day = (int)str_replace('.', '', $resultdriver['detail']);
$drivercharges = ($driver == 1) ? ($driver_cost_per_day * $durasi) : 0;

// Validasi data mobil
$sql1 = "SELECT mobil.*, merek.* 
        FROM mobil 
        JOIN merek ON merek.id_merek = mobil.id_merek 
        WHERE mobil.id_mobil = '$vid'";
$query1 = mysqli_query($koneksidb, $sql1);

if (!$query1 || mysqli_num_rows($query1) == 0) {
	die("<script>
        alert('Data mobil tidak ditemukan!');
        window.location = 'booking.php';
        </script>");
}

$result = mysqli_fetch_array($query1);
$harga = (int)str_replace('.', '', $result['harga']);
$totalmobil = $harga * $durasi;
$totalsewa = $totalmobil + $drivercharges;
?>

<!DOCTYPE HTML>
<html lang="en">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<meta name="keywords" content="">
	<meta name="description" content="">
	<title>Mutiara Motor Car Rental Portal</title>
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

	<div>
		<br>
		<center>
			<h3>Mobil Tersedia untuk disewa.</h3>
		</center>
		<hr>
	</div>

	<section class="user_profile inner_pages">
		<div class="container">
			<div class="col-md-6 col-sm-8">
				<div class="product-listing-img">
					<img src="admin/img/vehicleimages/<?= htmlentities($result['image1']) ?>"
						class="img-responsive" alt="Image">
				</div>
				<div class="product-listing-content">
					<h5><?= htmlentities($result['nama_merek']) ?>, <?= htmlentities($result['nama_mobil']) ?></h5>
					<p class="list-price"><?= format_rupiah($harga) ?> / Hari</p>
					<ul>
						<li><i class="fa fa-user"></i> <?= htmlentities($result['seating']) ?> Kursi</li>
						<li><i class="fa fa-calendar"></i> <?= htmlentities($result['tahun']) ?></li>
						<li><i class="fa fa-car"></i> <?= htmlentities($result['bb']) ?></li>
					</ul>
				</div>
			</div>

			<div class="user_profile_info">
				<div class="col-md-12 col-sm-10">
					<form method="post" action="">
						<input type="hidden" name="vid" value="<?= $vid ?>">
						<input type="hidden" name="email" value="<?= $email ?>">

						<div class="form-group">
							<label>Tanggal Mulai</label>
							<input type="date" class="form-control"
								name="fromdate" value="<?= $mulai ?>" readonly>
						</div>

						<div class="form-group">
							<label>Tanggal Selesai</label>
							<input type="date" class="form-control"
								name="todate" value="<?= $selesai ?>" readonly>
						</div>

						<div class="form-group">
							<label>Durasi</label>
							<input type="hidden" name="durasi" value="<?= $durasi ?>">
							<input type="text" class="form-control"
								value="<?= $durasi ?> Hari" readonly>
						</div>

						<div class="form-group">
							<label>Metode Pickup</label>
							<input type="text" class="form-control"
								name="pickup" value="<?= $pickup ?>" readonly>
						</div>

						<div class="form-group">
							<label>Biaya Mobil (<?= $durasi ?> Hari)</label>
							<input type="text" class="form-control"
								value="<?= format_rupiah($totalmobil) ?>" readonly>
						</div>

						<div class="form-group">
							<label>Biaya Driver (<?= $durasi ?> Hari)</label>
							<input type="hidden" name="biayadriver" value="<?= $drivercharges ?>">
							<input type="text" class="form-control"
								value="<?= format_rupiah($drivercharges) ?>" readonly>
						</div>

						<div class="form-group">
							<label>Total Biaya Sewa</label>
							<input type="hidden" name="total" value="<?= $totalsewa ?>">
							<input type="text" class="form-control"
								value="<?= format_rupiah($totalsewa) ?>"
								id="total_sewa_display"
								readonly>
						</div>

						<div class="form-group">
							<button type="submit" name="submit" class="btn btn-success btn-block">
								<i class="fa fa-credit-card"></i> Bayar Sekarang
							</button>
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

	<script>
		document.querySelector('form').addEventListener('submit', function(e) {
			const totalInput = document.querySelector('input[name="total"]');
			const totalValue = parseInt(totalInput.value);

			if (totalValue < 1000) {
				e.preventDefault();
				alert('Minimum pembayaran Rp 1.000');
				return false;
			}

			if (totalValue !== <?= $totalsewa ?>) {
				e.preventDefault();
				alert('Terjadi kesalahan perhitungan! Silahkan refresh halaman');
				return false;
			}
		});
	</script>
</body>

</html>