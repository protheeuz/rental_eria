<?php
include('includes/config.php');
include('includes/format_rupiah.php');
include('includes/library.php');
$kode = $_GET['kode'];
$sql1    = "SELECT booking.*,mobil.*, merek.*, users.* FROM booking,mobil,merek,users WHERE booking.id_mobil=mobil.id_mobil 
            AND merek.id_merek=mobil.id_merek and booking.email=users.email and booking.kode_booking='$kode'";
$query1 = mysqli_query($koneksidb, $sql1);
$result = mysqli_fetch_array($query1);
$harga    = $result['harga'];
$durasi = $result['durasi'];
$totalmobil = $durasi * $harga;
// $drivercharges = $result['driver'];
// $totalsewa = $totalmobil + $drivercharges;
$sqldriver = "SELECT * FROM tblpages WHERE id='0'";
$querydriver = mysqli_query($koneksidb, $sqldriver);
$resultdriver = mysqli_fetch_array($querydriver);
$driver_cost_per_day = (int)str_replace('.', '', $resultdriver['detail']);

// Hitung biaya
$harga = (int)str_replace('.', '', $result['harga']);
$durasi = $result['durasi'];
$totalmobil = $harga * $durasi;
$drivercharges = $result['driver'] * $driver_cost_per_day * $durasi;
$totalsewa = $totalmobil + $drivercharges;

// Format tanggal
$tglmulai = strtotime($result['tgl_mulai']);
$jmlhari  = 86400 * 1;
$tgl      = $tglmulai - $jmlhari;
$tglhasil = date("Y-m-d", $tgl);
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Invoice #<?php echo $result['kode_booking']; ?></title>
	<style>
		:root {
			--primary-color: #2c3e50;
			--secondary-color: #3498db;
		}

		body {
			font-family: 'Helvetica Neue', Arial, sans-serif;
			line-height: 1.6;
			color: #333;
			margin: 0;
			padding: 20px;
		}

		.invoice-container {
			max-width: 800px;
			margin: 0 auto;
			background: #fff;
			padding: 30px;
			border: 1px solid #eee;
			box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
		}

		.header {
			text-align: center;
			border-bottom: 2px solid var(--primary-color);
			padding-bottom: 20px;
			margin-bottom: 30px;
		}

		.logo {
			max-width: 150px;
			margin-bottom: 10px;
		}

		.invoice-title {
			color: var(--primary-color);
			margin: 10px 0;
			font-size: 28px;
		}

		.invoice-info {
			display: flex;
			justify-content: space-between;
			margin-bottom: 30px;
		}

		.badge {
			padding: 8px 15px;
			border-radius: 20px;
			font-weight: bold;
		}

		.pending {
			background: #f39c12;
			color: white;
		}

		.success {
			background: #2ecc71;
			color: white;
		}

		.failed {
			background: #e74c3c;
			color: white;
		}

		.details-section {
			display: grid;
			grid-template-columns: 1fr 1fr;
			gap: 30px;
			margin-bottom: 30px;
		}

		.detail-box {
			padding: 20px;
			background: #f9f9f9;
			border-radius: 5px;
		}

		.detail-box h3 {
			color: var(--secondary-color);
			margin-top: 0;
		}

		.payment-details {
			margin-top: 30px;
			background: #fff;
			padding: 20px;
			border-radius: 5px;
		}

		table {
			width: 100%;
			border-collapse: collapse;
			margin: 20px 0;
		}

		th,
		td {
			padding: 12px;
			text-align: left;
			border-bottom: 1px solid #ddd;
		}

		th {
			background-color: var(--primary-color);
			color: white;
		}

		.total-amount {
			font-size: 20px;
			color: var(--primary-color);
			font-weight: bold;
		}

		.footer {
			text-align: center;
			margin-top: 40px;
			padding-top: 20px;
			border-top: 1px solid #eee;
			color: #666;
		}

		.payment-instruction {
			background: #fff8e1;
			padding: 15px;
			border-left: 4px solid #f39c12;
			margin: 20px 0;
		}
	</style>
</head>

<body>
	<div class="invoice-container">
		<div class="header">
			<img src="assets/images/cat-profile.png" alt="Company Logo" class="logo">
			<h1 class="invoice-title">INVOICE</h1>
			<p>Jl. Kemanggisan Raya No.19, RT.4/RW.13, Kemanggisan, Kec. Palmerah<br>
				Kota Jakarta Barat, Daerah Khusus Ibukota Jakarta 11480<br>
				Telp: (021) 12345678 | Email: info@rentalmobil.com</p>
		</div>

		<div class="invoice-info">
			<div>
				<p><strong>Invoice #:</strong> <?php echo $result['kode_booking']; ?></p>
				<p><strong>Tanggal Invoice:</strong> <?php echo IndonesiaTgl(date('Y-m-d')); ?></p>
			</div>
			<div class="status">
				<span class="badge <?php echo strtolower($result['status']); ?>">
					<?php echo $result['status']; ?>
				</span>
			</div>
		</div>

		<div class="details-section">
			<div class="detail-box">
				<h3>Informasi Pelanggan</h3>
				<p><strong>Nama:</strong> <?php echo $result['nama_user']; ?><br>
					<strong>Email:</strong> <?php echo $result['email']; ?><br>
					<strong>Telp:</strong> <?php echo $result['telp']; ?>
				</p>
			</div>

			<div class="detail-box">
				<h3>Detail Penyewaan</h3>
				<p><strong>Tanggal Mulai:</strong> <?php echo IndonesiaTgl($result['tgl_mulai']); ?><br>
					<strong>Tanggal Selesai:</strong> <?php echo IndonesiaTgl($result['tgl_selesai']); ?><br>
					<strong>Durasi:</strong> <?php echo $durasi; ?> Hari
				</p>
			</div>
		</div>

		<h2>Detail Pembayaran</h2>
		<table>
			<thead>
				<tr>
					<th>Deskripsi</th>
					<th>Durasi</th>
					<th>Harga/Hari</th>
					<th>Jumlah</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><?php echo $result['nama_merek'] . ' ' . $result['nama_mobil']; ?></td>
					<td><?php echo $durasi; ?> Hari</td>
					<td><?php echo format_rupiah($harga); ?></td>
					<td><?php echo format_rupiah($totalmobil); ?></td>
				</tr>
				<tr>
					<td>Biaya Driver</td>
					<td>-</td>
					<td>-</td>
					<td><?php echo format_rupiah($drivercharges); ?></td>
				</tr>
				<tr>
					<td colspan="3" style="text-align: right;"><strong>Total Pembayaran:</strong></td>
					<td class="total-amount"><?php echo format_rupiah($totalsewa); ?></td>
				</tr>
			</tbody>
		</table>

		<?php if ($result['status'] == "Menunggu Pembayaran"): ?>
			<div class="payment-instruction">
				<h3>Instruksi Pembayaran</h3>
				<?php
				$sqlrek = "SELECT * FROM tblpages WHERE id='5'";
				$queryrek = mysqli_query($koneksidb, $sqlrek);
				$resultrek = mysqli_fetch_array($queryrek);
				?>
				<p>Silahkan lakukan pembayaran ke:<br>
					<?php echo $resultrek['detail']; ?><br>
					<strong>Batas Waktu Pembayaran:</strong> <?php echo IndonesiaTgl($tglhasil); ?>
				</p>
			</div>
		<?php endif; ?>

		<div class="footer">
			<p>Terima kasih telah menggunakan layanan kami<br>
				Untuk pertanyaan silahkan hubungi kami di (021) 12345678 atau email info@eriarentalcar.com</p>
			<p><strong>www.eriarentalcar.com</strong></p>
		</div>
	</div>

	<script>
		window.print();
	</script>
</body>

</html>