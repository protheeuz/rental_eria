<?php
// tracking.php
session_start();
error_reporting(0);
include('includes/config.php');
if (strlen($_SESSION['alogin']) == 0) {
    header('location:index.php');
} else {
?>
    <!doctype html>
    <html lang="en" class="no-js">

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
        <meta name="description" content="">
        <meta name="author" content="">
        <meta name="theme-color" content="#3e454c">

        <title>Rental Mobil | Tracking Kendaraan</title>

        <!-- Font awesome -->
        <link rel="stylesheet" href="css/font-awesome.min.css">
        <!-- Sandstone Bootstrap CSS -->
        <link rel="stylesheet" href="css/bootstrap.min.css">
        <!-- Admin Stye -->
        <link rel="stylesheet" href="css/style.css">
        <!-- Leaflet CSS -->
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
        <style>
            #map {
                height: 75vh;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                margin-top: 20px;
            }

            .leaflet-popup-content {
                min-width: 250px;
                font-family: 'Arial', sans-serif;
            }

            .car-icon {
                transition: transform 0.5s ease-in-out;
                filter: hue-rotate(200deg);
            }

            .status-box {
                background: #fff;
                padding: 15px;
                border-radius: 8px;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            }
        </style>
    </head>

    <body>
        <?php include('includes/header.php'); ?>

        <div class="ts-main-content">
            <?php include('includes/leftbar.php'); ?>

            <div class="content-wrapper">
                <div class="container-fluid">
                    <h2 class="page-title">Live Tracking Kendaraan</h2>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="panel panel-default">
                                <div class="panel-heading">Peta Lokasi Real-Time</div>
                                <div class="panel-body">
                                    <div id="map"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Loading Scripts -->
        <script src="js/jquery.min.js"></script>
        <script src="js/bootstrap.min.js"></script>
        <script src="js/main.js"></script>

        <!-- Leaflet JS -->
        <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>

        <script>
            // Konfigurasi icon mobil
            const carIcon = L.icon({
                iconUrl: 'https://cdn-icons-png.flaticon.com/512/744/744465.png',
                iconSize: [40, 40],
                iconAnchor: [20, 20],
                className: 'car-icon'
            });

            // Inisialisasi peta
            const map = L.map('map').setView([-7.708628, 109.476342], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(map);

            let marker = L.marker([-7.708628, 109.476342], {
                icon: carIcon
            }).addTo(map);

            // Fungsi update data
            async function updatePosition() {
                try {
                    const response = await fetch('map-api.php');
                    const data = await response.json();

                    if (data.error) {
                        console.error('Error:', data.error);
                        return;
                    }

                    // Update marker
                    marker.setLatLng([data.lat, data.lon]);
                    marker.setRotationAngle(data.direction);

                    // Update popup
                    const popupContent = `
                    <div class="status-box">
                        <h4 style="color:#2c3e50; margin-bottom:15px">Status Kendaraan</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Kecepatan:</strong> <span class="text-success">${data.speed} km/h</span></p>
                                <p><strong>Baterai:</strong> <span class="text-primary">${data.battery}%</span></p>
                                <p><strong>Arah:</strong> ${data.direction}°</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Status:</strong> ${data.status === '1' ? '<span class="text-success">Aktif</span>' : '<span class="text-danger">Nonaktif</span>'}</p>
                                <p><strong>Sinyal:</strong> ${data.signal}/100</p>
                                <p><strong>Tegangan:</strong> ${data.voltage}V</p>
                            </div>
                        </div>
                        <hr>
                        <small class="text-muted">Update terakhir: ${data.time}</small>
                    </div>
                `;

                    if (!marker.getPopup()) {
                        marker.bindPopup(popupContent).openPopup();
                    } else {
                        marker.setPopupContent(popupContent);
                    }

                    map.panTo([data.lat, data.lon]);

                } catch (error) {
                    console.error('Gagal update:', error);
                }
            }

            // Auto-update setiap 5 detik
            setInterval(updatePosition, 5000);
            updatePosition();
        </script>
    </body>

    </html>
<?php } ?>