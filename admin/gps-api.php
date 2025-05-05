<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json");
include('includes/config.php');

try {
    // 1. Ambil data dari database
    $result = $koneksidb->query("SELECT g.imei, m.nama_mobil, m.nopol 
                               FROM gps_devices g
                               JOIN mobil m ON g.id_mobil = m.id_mobil
                               LIMIT 1");

    if (!$result || $result->num_rows === 0) {
        throw new Exception('Data GPS belum terdaftar');
    }

    $device = $result->fetch_assoc();
    $imei = $device['imei'];
    $password = 'Argich12';

    $ch = curl_init();
    $cookieFile = tempnam(sys_get_temp_dir(), '360gps');

    curl_setopt_array($ch, [
        CURLOPT_URL => 'http://www.360gps.net/api/signin',
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode([
            'userName' => $imei,
            'password' => $password,
            'userType' => 2
        ]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
        CURLOPT_FOLLOWLOCATION => true
    ]);

    $loginResponse = curl_exec($ch);
    $loginData = json_decode($loginResponse, true);

    if (!isset($loginData['errcode']) || $loginData['errcode'] !== 0) {
        throw new Exception('Login gagal: ' . ($loginData['errmsg'] ?? 'Unknown error'));
    }

    $authToken = $loginData['retobj']['authToken'] ?? '';

    curl_setopt_array($ch, [
        CURLOPT_URL => "http://www.360gps.net/api/device/getDeviceInfo?lang=en&imei=$imei",
        CURLOPT_HTTPGET => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $authToken,
            'Content-Type: application/json',
            'Accept: application/json'
        ]
    ]);

    $deviceResponse = curl_exec($ch);
    $deviceData = json_decode($deviceResponse, true);

    if (!isset($deviceData['errcode']) || $deviceData['errcode'] !== 0) {
        throw new Exception('Gagal ambil data: ' . ($deviceData['errmsg'] ?? 'Unknown error'));
    }

    // 6. Format response
    $retobj = $deviceData['retobj'] ?? [];
    $gpsData = [
        'speed' => $retobj['speed'] ?? 0,
        'lat' => $retobj['lat'] ?? 0,
        'lon' => $retobj['lon'] ?? 0,
        'time' => isset($retobj['locTime']) ? date('Y-m-d H:i:s', $retobj['locTime']) : 'N/A',
        'battery' => $retobj['electricity'] ?? null,
        'status' => $retobj['status'] ?? null,
        'direction' => $retobj['direct'] ?? 0,
        'voltage' => $retobj['voltage'] ?? 0,
        'signal' => $retobj['signals'] ?? 0,
        'acc' => $retobj['ACC'] ?? 0
    ];

    echo json_encode([
        'vehicle' => [
            'nama_mobil' => $device['nama_mobil'],
            'nopol' => $device['nopol']
        ],
        'gps_data' => $gpsData
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    if ($ch) curl_close($ch);
    if (file_exists($cookieFile)) @unlink($cookieFile);
}
