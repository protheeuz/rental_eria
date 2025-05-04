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

            .leaflet-popup {
                bottom: 20px !important;
                z-index: 1000 !important;
            }

            .leaflet-popup-content-wrapper {
                border-radius: 10px;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
                transition: all 0.3s ease;
            }

            .leaflet-popup-content {
                margin: 0 !important;
                padding: 15px !important;
            }

            .leaflet-popup-close-button {
                right: 8px !important;
                top: 8px !important;
                font-size: 20px !important;
                color: #6c757d !important;
            }

            .vehicle-status {
                min-width: 250px;
                padding: 15px;
            }

            .status-item {
                display: flex;
                justify-content: space-between;
                margin: 8px 0;
            }

            .status-label {
                color: #6c757d;
                font-weight: 500;
            }

            .status-value {
                color: #2c3e50;
                font-weight: 600;
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
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

            // Buat marker dengan popup
            let marker = L.marker([-7.708628, 109.476342], {
                icon: carIcon,
                riseOnHover: true
            }).addTo(map);

            // Fungsi untuk membuat konten popup
            const createPopupContent = (data) => `
        <div class="vehicle-status">
            <h5 style="color: #2c3e50; border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 15px;">
                Status Kendaraan
            </h5>
            <div class="status-item">
                <span class="status-label">Kecepatan:</span>
                <span class="status-value text-success">${data.speed} km/h</span>
            </div>
            <div class="status-item">
                <span class="status-label">Baterai:</span>
                <span class="status-value text-primary">${data.battery}%</span>
            </div>
            <div class="status-item">
                <span class="status-label">Status:</span>
                <span class="status-value">
                    ${data.status === '1' ? 
                        '<span class="text-success">Aktif</span>' : 
                        '<span class="text-danger">Nonaktif</span>'}
                </span>
            </div>
            <div class="status-item">
                <span class="status-label">Update Terakhir:</span>
                <span class="status-value">${data.time}</span>
            </div>
        </div>
    `;
            // Inisialisasi popup pertama kali
            marker.bindPopup(createPopupContent({
                speed: '0',
                battery: '-',
                status: '1',
                time: 'Memuat data...'
            })).openPopup();
            // Fungsi update data
            async function updatePosition() {
                try {
                    const response = await fetch('map-api.php');
                    const data = await response.json();

                    if (data.error) {
                        console.error('Error:', data.error);
                        return;
                    }
                    marker.setLatLng([data.lat, data.lon]);
                    marker.setPopupContent(createPopupContent(data));
                    map.panTo([data.lat, data.lon]);
                } catch (error) {
                    console.error('Gagal memperbarui:', error);
                }
            }
            marker.on('click', function(e) {
                marker.togglePopup();
            });
            // Auto-update setiap 3 detik
            setInterval(updatePosition, 3000);
            updatePosition();
        </script>
    </body>

    </html>
<?php } ?>