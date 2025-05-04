<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$GPS_USER = '869207032962699';
$GPS_PASS = 'Argich12';
$IMEI = '869207032962699';

function get_gps_data()
{
    global $GPS_USER, $GPS_PASS, $IMEI;

    try {
        // 1. Login untuk mendapatkan cookie
        $login_url = 'http://www.360gps.net/api/signin';
        $login_data = json_encode([
            'userName' => $GPS_USER,
            'password' => $GPS_PASS,
            'userType' => 2
        ]);

        $ch = curl_init($login_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $login_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ]);
        curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');

        $login_response = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new Exception('Login error: ' . curl_error($ch));
        }

        // 2. Ambil data device
        $device_url = "http://www.360gps.net/api/device/getDeviceInfo?lang=en&imei=$IMEI";
        curl_setopt($ch, CURLOPT_URL, $device_url);
        curl_setopt($ch, CURLOPT_HTTPGET, 1);
        curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');

        $device_response = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new Exception('Device API error: ' . curl_error($ch));
        }

        curl_close($ch);

        $data = json_decode($device_response, true);

        // Handle error response
        if ($data['errcode'] !== 0) {
            throw new Exception('API Error: ' . $data['errmsg']);
        }
        // Format data
        return [
            'speed' => $data['retobj']['speed'],
            'lat' => $data['retobj']['lat'],
            'lon' => $data['retobj']['lon'],
            'time' => date('Y-m-d H:i:s', $data['retobj']['locTime']),
            'battery' => $data['retobj']['electricity'],
            'direction' => $data['retobj']['direct'],
            'status' => $data['retobj']['status'],
            'alarms' => $data['retobj']['alarms'],
            'voltage' => $data['retobj']['voltage'],
            'signal' => $data['retobj']['signals'],
            'acc' => $data['retobj']['ACC']
        ];
    } catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

// Output data
echo json_encode(get_gps_data());
