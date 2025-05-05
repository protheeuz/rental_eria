<?php
session_start();
error_reporting(0);
include('includes/config.php');

if (strlen($_SESSION['alogin']) == 0) {
    header('location:index.php');
    exit;
}

// Handle delete action
if (isset($_POST['delete'])) {
    $id_mobil = intval($_POST['id_mobil']);

    try {
        $stmt = $koneksidb->prepare("DELETE FROM gps_devices WHERE id_mobil = ?");
        $stmt->bind_param('i', $id_mobil);
        if ($stmt->execute()) {
            header('Location: tambah-data-gps.php?success=2');
        } else {
            header('Location: tambah-data-gps.php?error=4');
        }
        exit;
    } catch (Exception $e) {
        header('Location: tambah-data-gps.php?error=5');
        exit;
    }
}

// Handle edit action - get existing data
$edit_data = null;
if (isset($_GET['edit'])) {
    $id_mobil = intval($_GET['edit']);

    $stmt = $koneksidb->prepare("SELECT g.imei, m.id_mobil, m.nama_mobil, m.nopol 
                                FROM gps_devices g
                                JOIN mobil m ON g.id_mobil = m.id_mobil
                                WHERE g.id_mobil = ?");
    $stmt->bind_param('i', $id_mobil);
    $stmt->execute();
    $edit_data = $stmt->get_result()->fetch_assoc();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete'])) {
    $imei = trim($_POST['imei']);
    $id_mobil = isset($_POST['id_mobil']) ? intval($_POST['id_mobil']) : 0;

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

// Query untuk tabel list GPS
$gps_list_sql = "SELECT g.imei, m.id_mobil, m.nama_mobil, m.nopol 
                FROM gps_devices g
                JOIN mobil m ON g.id_mobil = m.id_mobil
                ORDER BY m.nama_mobil";
$gps_list_result = $koneksidb->query($gps_list_sql);

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
                                        <i class="icon fa fa-check"></i>
                                        <?= $_GET['success'] == 1 ? 'Data berhasil disimpan!' : 'Data berhasil dihapus!' ?>
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
                                            case 4:
                                                echo 'Gagal menghapus data';
                                                break;
                                            case 5:
                                                echo 'Terjadi kesalahan saat menghapus';
                                                break;
                                            default:
                                                echo 'Terjadi kesalahan';
                                        }
                                        ?>
                                    </div>
                                <?php endif; ?>

                                <form method="POST" class="form-horizontal">
                                    <?php if ($edit_data): ?>
                                        <input type="hidden" name="id_mobil" value="<?= $edit_data['id_mobil'] ?>">
                                    <?php endif; ?>

                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">Pilih Mobil</label>
                                        <div class="col-sm-10">
                                            <select class="form-control" name="id_mobil" <?= $edit_data ? 'disabled' : 'required' ?>>
                                                <?php if ($edit_data): ?>
                                                    <option value="<?= $edit_data['id_mobil'] ?>" selected>
                                                        <?= htmlspecialchars($edit_data['nama_mobil']) ?>
                                                        (<?= htmlspecialchars($edit_data['nopol']) ?>)
                                                    </option>
                                                <?php else: ?>
                                                    <option value="">-- Pilih Mobil --</option>
                                                    <?php
                                                    $mobil_result->data_seek(0);
                                                    while ($mobil = $mobil_result->fetch_assoc()): ?>
                                                        <option value="<?= $mobil['id_mobil'] ?>" <?= isset($_POST['id_mobil']) && $_POST['id_mobil'] == $mobil['id_mobil'] ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($mobil['nama_mobil']) ?>
                                                            (<?= htmlspecialchars($mobil['nopol']) ?>)
                                                        </option>
                                                    <?php endwhile; ?>
                                                <?php endif; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">IMEI GPS</label>
                                        <div class="col-sm-10">
                                            <input type="text"
                                                class="form-control"
                                                name="imei"
                                                value="<?= $edit_data ? htmlspecialchars($edit_data['imei']) : '' ?>"
                                                placeholder="Masukkan 15 digit IMEI GPS"
                                                required
                                                pattern="[0-9]{15}"
                                                title="IMEI harus 15 digit angka">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="col-sm-offset-2 col-sm-10">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fa fa-<?= $edit_data ? 'refresh' : 'save' ?>"></i>
                                                <?= $edit_data ? 'Update Data' : 'Simpan Data' ?>
                                            </button>

                                            <?php if ($edit_data): ?>
                                                <a href="tambah-data-gps.php" class="btn btn-default">
                                                    <i class="fa fa-times"></i> Batal Edit
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Tabel List GPS -->
                        <div class="panel panel-default">
                            <div class="panel-heading">Daftar GPS Terdaftar</div>
                            <div class="panel-body">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>No.</th>
                                            <th>Nama Mobil</th>
                                            <th>Nomor Polisi</th>
                                            <th>IMEI GPS</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($gps_list_result->num_rows > 0): ?>
                                            <?php $no = 1; ?>
                                            <?php while ($row = $gps_list_result->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?= $no++ ?></td>
                                                    <td><?= htmlspecialchars($row['nama_mobil']) ?></td>
                                                    <td><?= htmlspecialchars($row['nopol']) ?></td>
                                                    <td><?= htmlspecialchars($row['imei']) ?></td>
                                                    <td>
                                                        <a href="tambah-data-gps.php?edit=<?= $row['id_mobil'] ?>"
                                                            class="btn btn-sm btn-warning">
                                                            <i class="fa fa-edit"></i> Edit
                                                        </a>

                                                        <form method="POST" style="display:inline-block">
                                                            <input type="hidden" name="id_mobil" value="<?= $row['id_mobil'] ?>">
                                                            <button type="submit" name="delete"
                                                                class="btn btn-sm btn-danger"
                                                                onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                                                <i class="fa fa-trash"></i> Hapus
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center">Tidak ada data GPS terdaftar</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
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