<?php
set_time_limit(0);
$host = '0.0.0.0'; // Bind to all IP addresses
$port = 12345; // Port configured in the GPS tracker

$socket = stream_socket_server("tcp://$host:$port", $errno, $errstr);
if (!$socket) {
    die("Error: $errstr ($errno)\n");
}

while ($conn = stream_socket_accept($socket)) {
    $data = fread($conn, 1024); // Read incoming data
    fwrite($conn, "OK\n"); // Acknowledge receipt
    fclose($conn);

    // Process GPS data
    $parsedData = parseGPSData($data);
    if ($parsedData) {
        saveToDatabase($parsedData);
    }
}

function parseGPSData($data) {
    // Assuming data format: DEVICE_ID,LAT,LONG,TIMESTAMP
    $segments = explode(",", $data);
    if (count($segments) < 4) {
        return null; // Invalid data format
    }

    return [
        'device_id' => $segments[0],
        'latitude' => $segments[1],
        'longitude' => $segments[2],
        'timestamp' => $segments[3],
    ];
}

function saveToDatabase($data) {
    // Database connection
    $host = "localhost";
    $db = "gps_tracking";
    $user = "root";
    $password = "";

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("INSERT INTO gps_data (device_id, latitude, longitude, timestamp) VALUES (:device_id, :latitude, :longitude, :timestamp)");
        $stmt->execute([
            ':device_id' => $data['device_id'],
            ':latitude' => $data['latitude'],
            ':longitude' => $data['longitude'],
            ':timestamp' => $data['timestamp'],
        ]);

        echo "Data saved for Device ID: {$data['device_id']}\n";
    } catch (PDOException $e) {
        echo "Database Error: " . $e->getMessage();
    }
}
?>