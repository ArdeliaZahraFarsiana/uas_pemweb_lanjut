<?php
include_once 'conf/config.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
$level = $_SESSION['level'];
$user_id = $_SESSION['user_id'];

// Filter berita
$where = '';
if ($level == 'wartawan') {
    $where = "WHERE b.id_pengirim='$user_id'";
}
if ($level == 'editor') {
    $where = "WHERE b.status='draft'";
}
$query = mysqli_query($koneksi, "SELECT b.*, k.nama_kategori, u.username FROM berita b LEFT JOIN tb_kategori k ON b.id_kategori=k.id LEFT JOIN tb_users u ON b.id_pengirim=u.id $where ORDER BY b.created_at DESC");

// Count status for chart
$status_counts = ['draft' => 0, 'published' => 0, 'rejected' => 0];
while ($row = mysqli_fetch_assoc($query)) {
    $status_counts[$row['status']]++;
}
mysqli_data_seek($query, 0); // Reset pointer for table display
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Berita</title>
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
            padding: 1rem;
            display: flex;
            justify-content: center;
        }

        .container-wrapper {
            width: 100%;
            max-width: 1200px;
            display: flex;
            gap: 1.5rem;
            padding: 1.5rem;
        }

        .sidebar {
            width: 250px;
            background: var(--card-bg);
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            border: 1.5px solid var(--accent);
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .main-content {
            flex: 1;
            background: var(--card-bg);
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            border: 1.5px solid var(--accent);
        }

        .card-header {
            text-align: center;
            margin-bottom: 1rem;
        }

        .card-header h2 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--text-light);
        }

        .search-bar {
            width: 100%;
            padding: 0.7rem;
            background: #f3e5f5;
            border: 1px solid var(--accent);
            border-radius: 0.5rem;
            color: var(--text-light);
            font-size: 0.95rem;
            transition: border-color 0.2s;
        }

        .search-bar:focus {
            border-color: var(--primary);
            box-shadow: var(--glow);
        }

        .btn-sidebar {
            width: 100%;
            background: var(--primary);
            border: none;
            border-radius: 0.5rem;
            padding: 0.7rem;
            font-size: 0.95rem;
            font-weight: 600;
            color: #fff;
            transition: background 0.2s, transform 0.2s;
            text-align: left;
        }

        .btn-sidebar:hover {
            background: #6a1b9a;
            transform: translateY(-2px);
        }

        .table-responsive {
            border-radius: 0.8rem;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.05);
        }

        .table {
            margin-bottom: 0;
            font-size: 0.95rem;
            width: 100%;
            color: var(--text-light);
        }

        .table thead th {
            background: var(--secondary);
            color: var(--text-light);
            font-weight: 600;
            border: none;
            padding: 0.8rem;
            text-align: center;
            font-size: 1rem;
        }

        .table tbody tr:nth-child(even) {
            background: rgba(225, 190, 231, 0.05);
        }

        .table tbody tr:hover {
            background: rgba(123, 31, 162, 0.1);
            transition: background 0.2s;
        }

        .table td {
            vertical-align: middle;
            padding: 0.8rem;
            text-align: center;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            font-size: 0.9rem;
        }

        .img-preview {
            max-width: 80px;
            border-radius: 0.4rem;
            border: 1px solid var(--accent);
            background: #f3e5f5;
            padding: 2px;
            transition: transform 0.2s;
        }

        .img-preview:hover {
            transform: scale(1.05);
        }

        .btn-action {
            border-radius: 0.4rem;
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
            font-weight: 500;
            margin: 0.2rem;
            transition: all 0.2s;
        }

        .btn-warning {
            background: var(--secondary);
            color: var(--text-light);
            border: none;
        }

        .btn-danger {
            background: linear-gradient(90deg, #c2185b, #7b1fa2);
            color: #fff;
            border: none;
        }

        .btn-success {
            background: linear-gradient(90deg, #388e3c, #66bb6a);
            color: #fff;
            border: none;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 3px 8px rgba(123, 31, 162, 0.2);
        }

        .badge-status {
            font-size: 0.85rem;
            padding: 0.4em 0.8em;
            border-radius: 0.4rem;
            font-weight: 500;
        }

        .badge-draft {
            background: #e1bee7;
            color: #4a148c;
        }

        .badge-published {
            background: #c8e6c9;
            color: #1b5e20;
        }

        .badge-rejected {
            background: #f8bbd0;
            color: #880e4f;
        }

        #canvasPanel {
            background: var(--card-bg);
            border-radius: 0.8rem;
            box-shadow: var(--shadow);
            padding: 1.5rem;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1000;
            max-width: 500px;
            width: 90%;
        }

        #canvasPanel button {
            background: var(--primary);
            border: none;
            border-radius: 0.4rem;
            padding: 0.5rem 1rem;
            color: #fff;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        #canvasPanel button:hover {
            background: #6a1b9a;
        }

        @media (max-width: 768px) {
            .container-wrapper {
                flex-direction: column;
                padding: 1rem;
            }
            .sidebar {
                width: 100%;
            }
            .main-content {
                padding: 1rem;
            }
            .card-header h2 {
                font-size: 1.5rem;
            }
            .table td, .table th {
                padding: 0.6rem;
                font-size: 0.85rem;
            }
            .btn-action {
                padding: 0.4rem 0.8rem;
                font-size: 0.85rem;
            }
            .img-preview {
                max-width: 60px;
            }
        }
    </style>
</head>
<body>
<div class="container-wrapper">
    <div class="sidebar">
        <input type="text" class="search-bar" id="searchInput" placeholder="Cari judul berita..." onkeyup="filterTable()">
        <?php if ($level == 'wartawan'): ?>
            <a href="berita_form.php" class="btn-sidebar"><i class="fas fa-plus mr-2"></i>Tambah Berita</a>
        <?php endif; ?>
        <button class="btn-sidebar" onclick="location.reload();"><i class="fas fa-sync-alt mr-2"></i>Refresh</button>
        <button class="btn-sidebar" onclick="openCanvas()"><i class="fas fa-chart-bar mr-2"></i>Lihat Statistik</button>
    </div>
    <div class="main-content">
        <div class="card-header">
            <h2><i class="fas fa-newspaper mr-2"></i>Daftar Berita</h2>
        </div>
        <div class="table-responsive">
            <table class="table" id="newsTable">
                <thead>
                    <tr>
                        <th>Judul</th>
                        <th>Kategori</th>
                        <th>Pengirim</th>
                        <th>Status</th>
                        <th>Gambar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = mysqli_fetch_assoc($query)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['judul']) ?></td>
                        <td><?= htmlspecialchars($row['nama_kategori'] ?: 'Tidak ada kategori') ?></td>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td>
                            <span class="badge-status badge-<?= $row['status'] == 'draft' ? 'draft' : ($row['status'] == 'published' ? 'published' : 'rejected') ?>">
                                <?= htmlspecialchars(ucfirst($row['status'])) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($row['gambar']): ?>
                                <img src="upload/<?= htmlspecialchars($row['gambar']) ?>" class="img-preview" alt="Gambar Berita">
                            <?php else: ?>
                                <span class="text-muted">Tidak ada gambar</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($level == 'wartawan' && $row['status'] == 'draft' && $row['id_pengirim'] == $user_id): ?>
                                <a href="berita_form.php?id=<?= $row['id'] ?>" class="btn btn-action btn-warning"><i class="fas fa-edit"></i> Edit</a>
                                <a href="berita_hapus.php?id=<?= $row['id'] ?>" class="btn btn-action btn-danger" onclick="return confirm('Hapus berita?')"><i class="fas fa-trash"></i> Hapus</a>
                            <?php endif; ?>
                            <?php if ($level == 'editor' && $row['status'] == 'draft'): ?>
                                <a href="berita_approval.php?id=<?= $row['id'] ?>&aksi=publish" class="btn btn-action btn-success"><i class="fas fa-check"></i> Publish</a>
                                <a href="berita_approval.php?id=<?= $row['id'] ?>&aksi=reject" class="btn btn-action btn-danger"><i class="fas fa-times"></i> Tolak</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
                <?php if (mysqli_num_rows($query) == 0): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted">Belum ada berita.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div id="canvasPanel" style="display:none;">
        <button onclick="closeCanvas()">Tutup</button>
        <div id="chartContainer"></div>
    </div>
</div>
<script src="assets/plugins/jquery/jquery.min.js"></script>
<script src="assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
    function filterTable() {
        let input = document.getElementById('searchInput').value.toLowerCase();
        let table = document.getElementById('newsTable');
        let tr = table.getElementsByTagName('tr');

        for (let i = 1; i < tr.length; i++) {
            let td = tr[i].getElementsByTagName('td')[0]; // Filter by Judul column
            if (td) {
                let text = td.textContent || td.innerText;
                tr[i].style.display = text.toLowerCase().indexOf(input) > -1 ? '' : 'none';
            }
        }
    }

    function openCanvas() {
        document.getElementById('canvasPanel').style.display = 'block';
        document.getElementById('chartContainer').innerHTML = '<pre><code class="chartjs">{\n  "type": "bar",\n  "data": {\n    "labels": ["Draft", "Published", "Rejected"],\n    "datasets": [{\n      "label": "Jumlah Berita",\n      "data": [<?= $status_counts['draft'] ?>, <?= $status_counts['published'] ?>, <?= $status_counts['rejected'] ?>],\n      "backgroundColor": ["#e1bee7", "#c8e6c9", "#f8bbd0"],\n      "borderColor": ["#4a148c", "#1b5e20", "#880e4f"],\n      "borderWidth": 1\n    }]\n  },\n  "options": {\n    "scales": {\n      "y": {\n        "beginAtZero": true\n      }\n    }\n  }\n}</code></pre>';
    }

    function closeCanvas() {
        document.getElementById('canvasPanel').style.display = 'none';
    }
</script>
</body>
</html>