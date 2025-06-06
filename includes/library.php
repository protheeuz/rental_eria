<?php
# Pengaturan tanggal komputer
date_default_timezone_set("Asia/Jakarta");

// function buatKode($tabel, $prefix)
// {
// 	global $koneksidb;

// 	$max_attempt = 10;
// 	$attempt = 0;

// 	do {
// 		$query = "SELECT MAX(kode_booking) AS max_code FROM $tabel";
// 		$result = mysqli_query($koneksidb, $query);
// 		$data = mysqli_fetch_assoc($result);

// 		$number = ($data['max_code']) ?
// 			(int) substr($data['max_code'], strlen($prefix)) + 1 : 1;

// 		$new_code = $prefix . str_pad($number, 6, '0', STR_PAD_LEFT);

// 		$check = mysqli_query(
// 			$koneksidb,
// 			"SELECT kode_booking FROM $tabel WHERE kode_booking='$new_code'"
// 		);
// 		$attempt++;
// 	} while (mysqli_num_rows($check) > 0 && $attempt < $max_attempt);

// 	if ($attempt >= $max_attempt) {
// 		throw new Exception("Gagal generate kode unik setelah $max_attempt percobaan");
// 	}

// 	return $new_code;
// }

function buatKode($tabel, $prefix)
{
	global $koneksidb;

	$max_attempt = 10;
	$attempt = 0;

	do {
		$timestamp = date('YmdHis');
		$random = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
		$new_code = "{$prefix}{$timestamp}{$random}"; // Contoh: TRX20240504153000123
		// Cek apakah kode sudah pernah digunakan di Midtrans
		$check = mysqli_query(
			$koneksidb,
			"SELECT kode_booking FROM $tabel WHERE kode_booking='$new_code'"
		);
		$attempt++;
	} while (mysqli_num_rows($check) > 0 && $attempt < $max_attempt);

	if ($attempt >= $max_attempt) {
		throw new Exception("Gagal generate kode unik setelah $max_attempt percobaan");
	}

	return $new_code;
}

# Fungsi untuk membalik tanggal dari format Indo (d-m-Y) -> English (Y-m-d)
function InggrisTgl($tanggal)
{
	$tgl = substr($tanggal, 0, 2);
	$bln = substr($tanggal, 3, 2);
	$thn = substr($tanggal, 6, 4);
	$tanggal = "$thn-$bln-$tgl";
	return $tanggal;
}

# Fungsi untuk membalik tanggal dari format English (Y-m-d) -> Indo (d-m-Y)
function IndonesiaTgl($tanggal)
{
	$tgl = substr($tanggal, 8, 2);
	$bln = substr($tanggal, 5, 2);
	$thn = substr($tanggal, 0, 4);
	$tanggal = "$tgl-$bln-$thn";
	return $tanggal;
}

# Fungsi untuk membalik tanggal dari format English (Y-m-d) -> Indo (d-m-Y)
function Indonesia2Tgl($tanggal)
{
	$namaBln = array(
		"01" => "Januari",
		"02" => "Februari",
		"03" => "Maret",
		"04" => "April",
		"05" => "Mei",
		"06" => "Juni",
		"07" => "Juli",
		"08" => "Agustus",
		"09" => "September",
		"10" => "Oktober",
		"11" => "November",
		"12" => "Desember"
	);

	$tgl = substr($tanggal, 8, 2);
	$bln = substr($tanggal, 5, 2);
	$thn = substr($tanggal, 0, 4);
	$tanggal = "$tgl " . $namaBln[$bln] . " $thn";
	return $tanggal;
}

function hitungHari($myDate1, $myDate2)
{
	$myDate1 = strtotime($myDate1);
	$myDate2 = strtotime($myDate2);

	return ($myDate2 - $myDate1) / (24 * 3600);
}

# Fungsi untuk membuat format rupiah pada angka (uang)
function format_angka($angka)
{
	$hasil =  number_format($angka, 0, ",", ".");
	return $hasil;
}

# Fungsi untuk format tanggal, dipakai plugins Callendar
function form_tanggal($nama, $value = '')
{
	echo " <input type='text' name='$nama' id='$nama' size='11' maxlength='20' value='$value'/>&nbsp;
	<img src='images/calendar-add-icon.png' align='top' style='cursor:pointer; margin-top:7px;' alt='kalender'onclick=\"displayCalendar(document.getElementById('$nama'),'dd-mm-yyyy',this)\"/>			
	";
}

function angkaTerbilang($x)
{
	$abil = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
	if ($x < 12)
		return " " . $abil[$x];
	elseif ($x < 20)
		return angkaTerbilang($x - 10) . " belas";
	elseif ($x < 100)
		return angkaTerbilang($x / 10) . " puluh" . angkaTerbilang($x % 10);
	elseif ($x < 200)
		return " seratus" . angkaTerbilang($x - 100);
	elseif ($x < 1000)
		return angkaTerbilang($x / 100) . " ratus" . angkaTerbilang($x % 100);
	elseif ($x < 2000)
		return " seribu" . angkaTerbilang($x - 1000);
	elseif ($x < 1000000)
		return angkaTerbilang($x / 1000) . " ribu" . angkaTerbilang($x % 1000);
	elseif ($x < 1000000000)
		return angkaTerbilang($x / 1000000) . " juta" . angkaTerbilang($x % 1000000);
}
