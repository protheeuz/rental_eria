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
		<title>Rental Mobil</title>
		<link rel="stylesheet" href="assets/css/bootstrap.min.css" type="text/css">
		<link rel="stylesheet" href="assets/css/style.css" type="text/css">
		<link rel="stylesheet" href="assets/css/owl.carousel.css" type="text/css">
		<link rel="stylesheet" href="assets/css/owl.transitions.css" type="text/css">
		<link href="assets/css/slick.css" rel="stylesheet">
		<link href="assets/css/bootstrap-slider.min.css" rel="stylesheet">
		<link href="assets/css/font-awesome.min.css" rel="stylesheet">
		<link rel="stylesheet" id="switcher-css" type="text/css" href="assets/switcher/css/switcher.css" media="all">
		<link href="https://fonts.googleapis.com/css?family=Lato:300,400,700,900" rel="stylesheet">
	</head>

	<body>
		<?php include('includes/colorswitcher.php'); ?>
		<?php include('includes/header.php'); ?>

		<?php
		// Ambil biaya driver per hari
		$sqldriver = "SELECT * FROM tblpages WHERE id='0'";
		$resultdriver = mysqli_fetch_array(mysqli_query($koneksidb, $sqldriver));
		$driver_cost_per_day = (int)str_replace('.', '', $resultdriver['detail']);
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

					$sql = "SELECT booking.*, mobil.nama_mobil, merek.nama_merek, mobil.harga 
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

							// Perhitungan biaya
							$harga = (int)str_replace('.', '', $row['harga']);
							$durasi = $row['durasi'];
							$jumlah_driver = $row['driver'];

							$totalmobil = $harga * $durasi;
							$drivercharges = $jumlah_driver * $driver_cost_per_day * $durasi;
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
									<?php
									// Cek jika status Sudah Dibayar atau success
									if ($row['status'] == 'Sudah Dibayar' || $row['status'] == 'sukses') {
									?>
										<a href="booking_detail.php?kode=<?= $row['kode_booking'] ?>"
											class="btn btn-primary btn-xs"
											title="Lihat Detail">
											<i class="glyphicon glyphicon-eye-open"></i>
										</a>
									<?php } else {
										// Untuk status selain Sudah Dibayar/success
									?>
										<a href="payment_page.php?kode=<?= $row['kode_booking'] ?>"
											class="btn btn-warning btn-xs"
											title="Lanjutkan Pembayaran"
											onclick="return confirm('Lanjutkan pembayaran?')">
											<i class="fa fa-credit-card"></i>
										</a>
									<?php } ?>
								</td>
							</tr>
					<?php
						}
					} else {
						echo "<tr><td colspan='11' align='center'><b>Belum ada riwayat sewa.</b></td></tr>";
					}
					?>
				</table>
			</div>
		</section>

		<?php include('includes/footer.php'); ?>

		<script src="assets/js/jquery.min.js"></script>
		<script src="assets/js/bootstrap.min.js"></script>
		<script src="assets/js/interface.js"></script>
		<script src="assets/switcher/js/switcher.js"></script>
	</body>

	</html>
<?php } ?>