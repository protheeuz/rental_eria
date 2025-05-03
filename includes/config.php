<?php
# Konek ke Web Server Lokal
$myHost  = "localhost";
$myUser  = "root";
$myPass  = "";
$myDbs   = "rental_eria";

// Midtrans Configuration
define('MIDTRANS_SERVER_KEY', 'SB-Mid-server-ucGa2a3QFBu7NtwoRZ5ESvkg');
define('MIDTRANS_CLIENT_KEY', 'SB-Mid-client-Wnk4EO3qKdEE0Bjd');
define('MIDTRANS_ENVIRONMENT', 'sandbox'); // sandbox/production

// Midtrans Library Setup
require_once 'midtrans/Midtrans.php';

\Midtrans\Config::$serverKey = MIDTRANS_SERVER_KEY;
\Midtrans\Config::$isProduction = (MIDTRANS_ENVIRONMENT === 'production');
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;

// Koneksi Database
$koneksidb = mysqli_connect($myHost, $myUser, $myPass, $myDbs);
if (!$koneksidb) {
  die("Koneksi gagal: " . mysqli_connect_error());
}
?>