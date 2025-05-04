<?php
session_start();
error_reporting(0);
include('includes/config.php');

if (strlen($_SESSION['alogin']) == 0) {
    header('location:index.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $imei = trim($_POST['imei']);
    $id_mobil = intval($_POST['id_mobil']);

    if (empty($imei) || $id_mobil <= 0) {
        header('Location: tambah-data-gps.php?error=1');
        exit;
    }

    try {
        // Cek apakah mobil sudah terdaftar
        $check_sql = "SELECT * FROM gps_devices WHERE id_mobil = ?";
        $check_stmt = $koneksidb->prepare($check_sql);
        $check_stmt->bind_param('i', $id_mobil);
        $check_stmt->execute();
        $existing = $check_stmt->get_result()->fetch_assoc();

        if ($existing) {
            // Update existing
            $sql = "UPDATE gps_devices SET imei = ? WHERE id_mobil = ?";
            $stmt = $koneksidb->prepare($sql);
            $stmt->bind_param('si', $imei, $id_mobil);
        } else {
            $sql = "INSERT INTO gps_devices (imei, id_mobil, created_at) VALUES (?, ?, NOW())";
            $stmt = $koneksidb->prepare($sql);
            $stmt->bind_param('si', $imei, $id_mobil);
        }

        if ($stmt->execute()) {
            header('Location: tambah-data-gps.php?success=1');
            exit;
        } else {
            header('Location: tambah-data-gps.php?error=2');
            exit;
        }
    } catch (Exception $e) {
        header('Location: tambah-data-gps.php?error=3');
        exit;
    }
}

// Ambil data mobil
$mobil_sql = "SELECT id_mobil, nama_mobil, nopol FROM mobil";
$mobil_result = $koneksidb->query($mobil_sql);

$koneksidb->query("CREATE TABLE IF NOT EXISTS gps_devices (
    id INT PRIMARY KEY AUTO_INCREMENT,
    imei VARCHAR(20) NOT NULL UNIQUE,
    id_mobil INT NOT NULL UNIQUE,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (id_mobil) REFERENCES mobil(id_mobil)
)");
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

    <title>Rental Mobil | Tambah Data GPS</title>

    <!-- Font awesome -->
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <!-- Sandstone Bootstrap CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <!-- Admin Style -->
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include('includes/header.php'); ?>

    <div class="ts-main-content">
        <?php include('includes/leftbar.php'); ?>

        <div class="content-wrapper">
            <div class="container-fluid">
                <h2 class="page-title">Tambah Data GPS Kendaraan</h2>

                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">Form Input Data GPS</div>
                            <div class="panel-body">
                                <?php if (isset($_GET['success'])): ?>
                                    <div class="alert alert-success alert-dismissible">
                                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                        <i class="icon fa fa-check"></i> Data berhasil disimpan!
                                    </div>
                                <?php endif; ?>

                                <?php if (isset($_GET['error'])): ?>
                                    <div class="alert alert-danger alert-dismissible">
                                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                        <i class="icon fa fa-ban"></i>
                                        <?php
                                        switch ($_GET['error']) {
                                            case 1:
                                                echo 'Harap isi semua field';
                                                break;
                                            case 2:
                                                echo 'Gagal menyimpan data';
                                                break;
                                            case 3:
                                                echo 'Terjadi kesalahan sistem';
                                                break;
                                            default:
                                                echo 'Terjadi kesalahan';
                                        }
                                        ?>
                                    </div>
                                <?php endif; ?>

                                <form method="POST" class="form-horizontal">
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">Pilih Mobil</label>
                                        <div class="col-sm-10">
                                            <select class="form-control" name="id_mobil" required>
                                                <option value="">-- Pilih Mobil --</option>
                                                <?php while ($mobil = $mobil_result->fetch_assoc()): ?>
                                                    <option value="<?= $mobil['id_mobil'] ?>">
                                                        <?= htmlspecialchars($mobil['nama_mobil']) ?>
                                                        (<?= htmlspecialchars($mobil['nopol']) ?>)
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">IMEI GPS</label>
                                        <div class="col-sm-10">
                                            <input type="text"
                                                class="form-control"
                                                name="imei"
                                                placeholder="Masukkan 15 digit IMEI GPS"
                                                required
                                                pattern="[0-9]{15}"
                                                title="IMEI harus 15 digit angka">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="col-sm-offset-2 col-sm-10">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fa fa-save"></i> Simpan Data
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/main.js"></script>
</body>

</html>