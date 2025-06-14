<?php
include 'conf/config.php';
session_start();

// Fetch categories for filter
$kategori = mysqli_query($koneksi, "SELECT * FROM tb_kategori ORDER BY nama_kategori");

// Handle search
$search_results = [];
$search_keyword = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_kategori = isset($_GET['kategori']) ? intval($_GET['kategori']) : 0;
$search_performed = ($search_keyword !== '' || $search_kategori > 0);
if ($search_performed) {
    $where = [];
    if ($search_keyword !== '') {
        $escaped = mysqli_real_escape_string($koneksi, $search_keyword);
        $where[] = "(b.judul LIKE '%$escaped%' OR b.isi LIKE '%$escaped%')";
    }
    if ($search_kategori > 0) {
        $where[] = "b.id_kategori = $search_kategori";
    }
    $where[] = "b.status = 'publish'";
    $where_sql = implode(' AND ', $where);
    $q_search = mysqli_query($koneksi, "SELECT b.*, k.nama_kategori, u.username FROM berita b LEFT JOIN tb_kategori k ON b.id_kategori=k.id LEFT JOIN tb_users u ON b.id_pengirim=u.id WHERE $where_sql ORDER BY b.created_at DESC LIMIT 12");
    while ($row = mysqli_fetch_assoc($q_search)) {
        $search_results[] = $row;
    }
}

if (!isset($_GET['id'])) {
    header('Location: berita_list.php');
    exit;
}
$id = intval($_GET['id']);
$sql_detail = "SELECT b.*, k.nama_kategori, u.username FROM berita b LEFT JOIN tb_kategori k ON b.id_kategori=k.id LEFT JOIN tb_users u ON b.id_pengirim=u.id WHERE b.id='$id'";
$q = mysqli_query($koneksi, $sql_detail);
if (!$q) {
    echo '<div class="alert alert-danger">Query error: ' . mysqli_error($koneksi) . '</div>';
    echo '<pre>' . htmlspecialchars($sql_detail) . '</pre>';
    exit;
}
if (!$data = mysqli_fetch_assoc($q)) {
    $cek = mysqli_query($koneksi, "SELECT * FROM berita WHERE id='$id'");
    if (mysqli_num_rows($cek) == 0) {
        echo '<div class="alert alert-danger">Data berita dengan id = ' . $id . ' tidak ada di tabel berita.</div>';
    } else {
        echo '<div class="alert alert-danger">Data berita ditemukan, tapi join kategori/user gagal. Cek data kategori dan user terkait.</div>';
    }
    echo '<pre>' . htmlspecialchars($sql_detail) . '</pre>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Berita</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="assets/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="assets/dist/css/adminlte.min.css">
    <style>
        :root {
            --primary: #7b1fa2; /* Deep purple */
            --secondary: #e1bee7; /* Very light purple */
            --accent: #ab47bc; /* Medium purple */
            --bg-gradient: linear-gradient(135deg, #f3e5f5 0%, #e1bee7 50%, #7b1fa2 100%); /* Purple gradient */
            --card-bg: rgba(243, 229, 245, 0.95); /* Soft purple transparan */
            --shadow: 0 6px 20px rgba(123, 31, 162, 0.1); /* Purple shadow */
            --text-light: #4a148c; /* Darker purple for contrast */
            --text-muted: #8e24aa; /* Muted purple */
            --glow: 0 0 10px rgba(123, 31, 162, 0.2); /* Purple glow */
        }

        body {
            background: var(--bg-gradient);
            min-height: 100vh;
            font-family: 'Roboto', sans-serif;
            color: var(--text-light);
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }

        .search-header {
            background: var(--card-bg);
            border-bottom: 1px solid var(--accent);
            padding: 1rem;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: var(--shadow);
        }

        .search-form {
            display: flex;
            gap: 1rem;
            max-width: 1200px;
            margin: 0 auto;
            align-items: center;
        }

        .search-form .form-group {
            margin: 0;
            flex: 1;
        }

        .search-form .form-control {
            background: #f3e5f5;
            border: 1px solid var(--accent);
            color: var(--text-light);
            border-radius: 0.4rem;
            padding: 0.6rem;
            font-size: 0.9rem;
        }

        .search-form select.form-control {
            background: var(--secondary) !important;
            color: var(--text-light) !important;
        }

        .search-form select.form-control option {
            background: #f3e5f5 !important;
            color: var(--text-light) !important;
        }

        .search-form .form-control:focus {
            border-color: var(--primary);
            box-shadow: var(--glow);
        }

        .search-form .btn-primary {
            background: var(--primary);
            border: none;
            border-radius: 0.4rem;
            padding: 0.6rem 1.2rem;
            font-size: 0.9rem;
            transition: background 0.2s;
        }

        .search-form .btn-primary:hover {
            background: #6a1b9a;
        }

        .container-wrapper {
            max-width: 900px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .glass-card {
            background: var(--card-bg);
            border-radius: 0.8rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--accent);
            padding: 1.5rem;
        }

        .card-header {
            position: relative;
            padding: 1rem;
            margin-bottom: 1rem;
            text-align: center;
        }

        .card-header h2 {
            margin: 0;
            font-size: 1.6rem;
            font-weight: 600;
            color: var(--text-light);
        }

        .btn-back {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: var(--primary);
            border: none;
            border-radius: 0.4rem;
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
            color: #fff;
            transition: background 0.2s;
        }

        .btn-back:hover {
            background: #6a1b9a;
        }

        .meta-container {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }

        .meta-item {
            background: var(--secondary);
            color: var(--text-light);
            padding: 0.4rem 0.8rem;
            border-radius: 0.4rem;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .img-preview {
            max-width: 100%;
            max-height: 350px;
            border-radius: 0.6rem;
            border: 1px solid var(--accent);
            margin: 1rem auto;
            display: block;
            object-fit: cover;
        }

        .content {
            font-size: 1rem;
            line-height: 1.6;
            color: var(--text-light);
            background: rgba(255, 255, 255, 0.05);
            padding: 1rem;
            border-radius: 0.6rem;
        }

        .search-results {
            margin-top: 2rem;
        }

        .search-results h4 {
            font-size: 1.3rem;
            color: var(--text-light);
            margin-bottom: 1rem;
        }

        .accordion-button {
            background: var(--secondary);
            color: var(--text-light);
            border: none;
            border-radius: 0.4rem;
            padding: 0.8rem;
            font-size: 0.95rem;
            width: 100%;
            text-align: left;
            transition: background 0.2s;
        }

        .accordion-button:hover {
            background: #ce93d8;
        }

        .accordion-content {
            display: none;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 0.4rem;
            margin-top: 0.5rem;
        }

        .search-results .card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--accent);
            border-radius: 0.6rem;
            margin-bottom: 1rem;
            transition: transform 0.2s;
        }

        .search-results .card:hover {
            transform: translateY(-2px);
        }

        .search-results .card-img-top {
            height: 120px;
            object-fit: cover;
            border-radius: 0.6rem 0.6rem 0 0;
        }

        .search-results .card-title a {
            color: var(--text-light);
            text-decoration: none;
            font-size: 1rem;
            font-weight: 500;
        }

        .search-results .card-title a:hover {
            color: var(--primary);
        }

        .search-results .btn-outline-primary {
            border-color: var(--primary);
            color: var(--primary);
            border-radius: 0.4rem;
            padding: 0.4rem 0.8rem;
            font-size: 0.85rem;
        }

        .search-results .btn-outline-primary:hover {
            background: var(--primary);
            color: #fff;
        }

        @media (max-width: 768px) {
            .search-form {
                flex-direction: column;
            }
            .search-form .btn-primary {
                width: 100%;
            }
            .container-wrapper {
                padding: 0 0.5rem;
            }
            .glass-card {
                padding: 1rem;
            }
            .card-header h2 {
                font-size: 1.4rem;
            }
            .meta-container {
                flex-direction: column;
                align-items: center;
            }
            .img-preview {
                max-height: 250px;
            }
            .search-results .card-img-top {
                height: 100px;
            }
        }
    </style>
</head>
<body>
<header class="search-header">
    <form class="search-form" method="get" action="berita_detail.php">
        <div class="form-group">
            <input type="text" class="form-control" id="search" name="search" placeholder="Kata kunci..." value="<?= htmlspecialchars($search_keyword) ?>">
        </div>
        <div class="form-group">
            <select class="form-control" id="kategori" name="kategori">
                <option value="0">Semua Kategori</option>
                <?php
                mysqli_data_seek($kategori, 0);
                while ($row = mysqli_fetch_assoc($kategori)):
                ?>
                    <option value="<?= $row['id'] ?>" <?= $search_kategori == $row['id'] ? 'selected' : '' ?>><?= htmlspecialchars($row['nama_kategori']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-search mr-2"></i>Cari</button>
        <?php if (isset($_GET['id'])): ?>
            <input type="hidden" name="id" value="<?= intval($_GET['id']) ?>">
        <?php endif; ?>
    </form>
</header>
<div class="container-wrapper">
    <div class="glass-card">
        <div class="card-header">
            <h2><i class="fas fa-newspaper mr-2"></i><?= htmlspecialchars($data['judul']) ?></h2>
            <a href="berita_list.php" class="btn-back"><i class="fas fa-arrow-left mr-2"></i>Kembali</a>
        </div>
        <div class="card-body">
            <div class="meta-container">
                <span class="meta-item"><i class="fas fa-tag mr-1"></i> <?= htmlspecialchars($data['nama_kategori'] ?: 'Tidak ada kategori') ?></span>
                <span class="meta-item"><i class="fas fa-user mr-1"></i> <?= htmlspecialchars($data['username']) ?></span>
                <span class="meta-item"><i class="fas fa-info-circle mr-1"></i> <?= htmlspecialchars(ucfirst($data['status'])) ?></span>
                <span class="meta-item"><i class="far fa-clock mr-1"></i> <?= htmlspecialchars(date('d F Y H:i', strtotime($data['created_at']))) ?> WIB</span>
            </div>
            <?php if ($data['gambar']): ?>
                <img src="upload/<?= htmlspecialchars($data['gambar']) ?>" class="img-preview" alt="Gambar Berita">
            <?php endif; ?>
            <div class="content"><?= nl2br(htmlspecialchars($data['isi'])) ?></div>
        </div>
    </div>
    <?php if ($search_performed): ?>
        <div class="search-results">
            <h4><i class="fas fa-list mr-2"></i>Hasil Pencarian Berita</h4>
            <button class="accordion-button" onclick="toggleAccordion()">Tampilkan Hasil Pencarian</button>
            <div class="accordion-content" id="searchAccordion">
                <?php if (count($search_results) === 0): ?>
                    <div class="alert alert-warning" style="background: rgba(255, 255, 255, 0.1); border: 1px solid var(--accent);">Tidak ada berita ditemukan untuk pencarian Anda.</div>
                <?php else: ?>
                    <?php foreach ($search_results as $berita): ?>
                        <div class="card">
                            <?php if ($berita['gambar']): ?>
                                <img src="upload/<?= htmlspecialchars($berita['gambar']) ?>" class="card-img-top" alt="Gambar Berita">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title">
                                    <a href="berita_detail.php?id=<?= $berita['id'] ?>"><?= htmlspecialchars($berita['judul']) ?></a>
                                </h5>
                                <div class="mb-2"><span class="badge badge-info"><i class="fas fa-tag mr-1"></i><?= htmlspecialchars($berita['nama_kategori'] ?: 'Tanpa Kategori') ?></span></div>
                                <div class="mb-2 text-muted" style="font-size: 0.85rem;">
                                    <i class="fas fa-user mr-1"></i> <?= htmlspecialchars($berita['username']) ?>
                                    | <i class="far fa-clock mr-1"></i> <?= date('d M Y', strtotime($berita['created_at'])) ?>
                                </div>
                                <div class="mb-2" style="color: var(--text-muted); font-size: 0.9rem;">
                                    <?= htmlspecialchars(mb_strimwidth(strip_tags($berita['isi']), 0, 80, '...')) ?>
                                </div>
                                <a href="berita_detail.php?id=<?= $berita['id'] ?>" class="btn btn-outline-primary"><i class="fas fa-arrow-right mr-1"></i>Lihat Detail</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
<script src="assets/plugins/jquery/jquery.min.js"></script>
<script src="assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleAccordion() {
        const content = document.getElementById('searchAccordion');
        content.style.display = content.style.display === 'block' ? 'none' : 'block';
    }
</script>
</body>
</html>