<?php
session_start();
error_reporting(0);
include('includes/config.php');
include('includes/format_rupiah.php');
include('includes/library.php');

if (strlen($_SESSION['ulogin']) == 0) {
	header('location:index.php');
} else {
?>
	<!DOCTYPE HTML>
	<html lang="en">

	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width,initial-scale=1">
		<meta name="keywords" content="">
		<meta name="description" content="">
		<title> Rental Mobil</title>
		<!--Bootstrap -->
		<link rel="stylesheet" href="assets/css/bootstrap.min.css" type="text/css">
		<!--Custome Style -->
		<link rel="stylesheet" href="assets/css/style.css" type="text/css">
		<!--OWL Carousel slider-->
		<link rel="stylesheet" href="assets/css/owl.carousel.css" type="text/css">
		<link rel="stylesheet" href="assets/css/owl.transitions.css" type="text/css">
		<!--slick-slider -->
		<link href="assets/css/slick.css" rel="stylesheet">
		<!--bootstrap-slider -->
		<link href="assets/css/bootstrap-slider.min.css" rel="stylesheet">
		<!--FontAwesome Font Style -->
		<link href="assets/css/font-awesome.min.css" rel="stylesheet">

		<!-- SWITCHER -->
		<link rel="stylesheet" id="switcher-css" type="text/css" href="assets/switcher/css/switcher.css" media="all" />
		<link rel="alternate stylesheet" type="text/css" href="assets/switcher/css/red.css" title="red" media="all" data-default-color="true" />
		<link rel="alternate stylesheet" type="text/css" href="assets/switcher/css/orange.css" title="orange" media="all" />
		<link rel="alternate stylesheet" type="text/css" href="assets/switcher/css/blue.css" title="blue" media="all" />
		<link rel="alternate stylesheet" type="text/css" href="assets/switcher/css/pink.css" title="pink" media="all" />
		<link rel="alternate stylesheet" type="text/css" href="assets/switcher/css/green.css" title="green" media="all" />
		<link rel="alternate stylesheet" type="text/css" href="assets/switcher/css/purple.css" title="purple" media="all" />

		<!-- Fav and touch icons -->
		<link rel="apple-touch-icon-precomposed" sizes="144x144" href="assets/images/favicon-icon/apple-touch-icon-144-precomposed.png">
		<link rel="apple-touch-icon-precomposed" sizes="114x114" href="assets/images/favicon-icon/apple-touch-icon-114-precomposed.html">
		<link rel="apple-touch-icon-precomposed" sizes="72x72" href="assets/images/favicon-icon/apple-touch-icon-72-precomposed.png">
		<link rel="apple-touch-icon-precomposed" href="assets/images/favicon-icon/apple-touch-icon-57-precomposed.png">
		<link rel="shortcut icon" href="assets/images/favicon-icon/favicon.png">
		<!-- Google-Font-->
		<link href="https://fonts.googleapis.com/css?family=Lato:300,400,700,900" rel="stylesheet">
		<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
		<!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
	</head>

	<body>

		<!-- Start Switcher -->
		<?php include('includes/colorswitcher.php'); ?>
		<!-- /Switcher -->

		<!--Header-->
		<?php include('includes/header.php'); ?>

		<?php
		$email = $_SESSION['ulogin'];
		$sql1 	= "SELECT booking.*,mobil.*, merek.*, users.* FROM booking,mobil,merek,users WHERE booking.id_mobil=mobil.id_mobil 
			AND merek.id_merek=mobil.id_merek and booking.email=users.email and booking.email='$email'";
		$query1 = mysqli_query($koneksidb, $sql1);
		$result = mysqli_fetch_array($query1);
		$harga	= $result['harga'];
		$durasi = $result['durasi'];
		$totalmobil = $durasi * $harga;
		$drivercharges = $result['driver'];
		$totalsewa = $totalmobil + $drivercharges;
		$tglmulai = strtotime($result['tgl_mulai']);
		$jmlhari  = 86400 * 1;
		$tgl	  = $tglmulai - $jmlhari;
		$tglhasil = date("Y-m-d", $tgl);
		?>
		<section class="user_profile inner_pages">
			<center>
				<h3>Riwayat Sewa</h3>
			</center>
			<div class="container">
				<table class="table table-striped table-bordered">
					<thead>
						<tr>
							<th width="23" align="center">No</th>
							<th width="100">Kode Sewa</th>
							<th width="132">Nama Mobil</th>
							<th width="80">Tgl. Mulai</th>
							<th width="100">Tgl. Selesai</th>
							<th width="50">Durasi</th>
							<th width="100">Biaya Mobil</th>
							<th width="110">Biaya Driver</th>
							<th width="100">Total Biaya</th>
							<th width="100">Status</th>
							<th width="50">Opsi</th>
						</tr>
					</thead>
					<?php
					$email = $_SESSION['ulogin'];
					$nomor = 0;

					$sql = "SELECT booking.*, mobil.nama_mobil, merek.nama_merek 
            FROM booking 
            JOIN mobil ON booking.id_mobil = mobil.id_mobil
            JOIN merek ON mobil.id_merek = merek.id_merek
            WHERE booking.email = ? 
            ORDER BY booking.tgl_booking DESC";

					$stmt = $koneksidb->prepare($sql);
					$stmt->bind_param("s", $email);
					$stmt->execute();
					$result = $stmt->get_result();

					if ($result->num_rows > 0) {
						while ($row = $result->fetch_assoc()) {
							$nomor++;

							// Hitung ulang biaya
							$harga = (int)str_replace('.', '', $row['harga']);
							$durasi = $row['durasi'];
							$totalmobil = $harga * $durasi;
							$drivercharges = $row['driver'];
							$totalsewa = $totalmobil + $drivercharges;
					?>
							<tr>
								<td align="center"><?= $nomor ?></td>
								<td><?= htmlspecialchars($row['kode_booking']) ?></td>
								<td><?= htmlspecialchars($row['nama_merek']) ?> <?= htmlspecialchars($row['nama_mobil']) ?></td>
								<td><?= IndonesiaTgl($row['tgl_mulai']) ?></td>
								<td><?= IndonesiaTgl($row['tgl_selesai']) ?></td>
								<td><?= $durasi ?> Hari</td>
								<td><?= format_rupiah($totalmobil) ?></td>
								<td><?= format_rupiah($drivercharges) ?></td>
								<td><?= format_rupiah($totalsewa) ?></td>
								<td><?= ucfirst($row['status']) ?></td>
								<td align="center">
									<?php if ($row['status'] == 'sukses') { ?>
										<a href="booking_detail.php?kode=<?= $row['kode_booking'] ?>"
											class="btn btn-primary btn-sm"
											title="Lihat Detail">
											<i class="glyphicon glyphicon-eye-open"></i>
										</a>
									<?php } else { ?>
										<a href="payment_page.php?kode=<?= $row['kode_booking'] ?>"
											class="btn btn-warning btn-sm"
											title="Lanjutkan Pembayaran"
											onclick="return confirm('Lanjutkan pembayaran?')">
											<i class="fa fa-credit-card"></i>
										</a>
										<a href="booking_detail.php?kode=<?= $row['kode_booking'] ?>"
											class="btn btn-info btn-sm"
											title="Lihat Detail">
											<i class="glyphicon glyphicon-eye-open"></i>
										</a>
									<?php } ?>
								</td>
							</tr>
						<?php
						}
					} else {
						?>
						<tr>
							<td colspan="11" align="center"><b>Belum ada riwayat sewa.</b></td>
						</tr>
					<?php } ?>
				</table>
			</div>
		</section>

		<?php include('includes/footer.php'); ?>

		<!-- Scripts -->
		<script src="assets/js/jquery.min.js"></script>
		<script src="assets/js/bootstrap.min.js"></script>
		<script src="assets/js/interface.js"></script>
		<!--Switcher-->
		<script src="assets/switcher/js/switcher.js"></script>
		<!--bootstrap-slider-JS-->
		<script src="assets/js/bootstrap-slider.min.js"></script>
		<!--Slider-JS-->
		<script src="assets/js/slick.min.js"></script>
		<script src="assets/js/owl.carousel.min.js"></script>
	</body>

	</html>
<?php } ?>