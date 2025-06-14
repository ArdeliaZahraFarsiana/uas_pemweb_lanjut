<?php
include 'conf/config.php';
// Proses form lupa password
if (isset($_POST['submit'])) {
    $email = trim($_POST['email']);
    if (empty($email)) {
        $error = "Email harus diisi.";
    } else {
        // Cek email di database
        $cek = mysqli_query($koneksi, "SELECT * FROM tb_users WHERE email='$email'");
        if (mysqli_num_rows($cek) == 1) {
            $user = mysqli_fetch_assoc($cek);
            // Generate token reset
            $token = bin2hex(random_bytes(16));
            // Simpan token ke database
            mysqli_query($koneksi, "UPDATE tb_users SET reset_token='$token' WHERE email='$email'");
            // Simulasi kirim link reset (tampilkan di halaman)
            $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=$token";
            $success = "Link reset password: <a href='$reset_link'>$reset_link</a>";
        } else {
            $error = "Email tidak ditemukan.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lupa Password</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .login-box {
            background: var(--card-bg);
            border: 1px solid var(--accent);
            border-radius: 0.8rem;
            box-shadow: var(--shadow);
            width: 100%;
            max-width: 450px;
            padding: 2rem;
            text-align: center;
        }

        .login-box h1 {
            font-size: 1.8rem;
            color: var(--text-light);
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .input-group {
            margin-bottom: 1.5rem;
        }

        .input-group .form-control {
            background: #f3e5f5;
            border: 1px solid var(--accent);
            border-radius: 0.4rem 0 0 0.4rem;
            border-right: none;
            color: var(--text-light);
            font-size: 1.1rem;
            padding: 0.8rem;
        }

        .input-group-append .input-group-text {
            background: #f3e5f5;
            border: 1px solid var(--accent);
            border-left: none;
            border-radius: 0 0.4rem 0.4rem 0;
            color: var(--primary);
            padding: 0.8rem 1rem;
        }

        button[type="submit"], .btn-primary {
            background: var(--primary);
            border: none;
            color: #fff;
            padding: 0.8rem 2rem;
            border-radius: 0.4rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, transform 0.2s;
            margin-top: 0.5rem;
        }

        button[type="submit"]:hover, .btn-primary:hover {
            background: #6a1b9a;
            transform: translateY(-2px);
        }

        .links {
            margin-top: 1rem;
        }

        .links a {
            color: var(--primary);
            text-decoration: none;
            font-size: 0.95rem;
            transition: color 0.2s;
        }

        .links a:hover {
            color: #6a1b9a;
            text-decoration: underline;
        }

        .alert {
            border-radius: 0.4rem;
            padding: 0.8rem;
            margin-bottom: 1.5rem;
            text-align: left;
            font-size: 0.95rem;
        }

        .alert-danger {
            background: #f8bbd0;
            border: 1px solid #c2185b;
            color: #880e4f;
        }

        .alert-success {
            background: #c8e6c9;
            border: 1px solid #388e3c;
            color: #1b5e20;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            background: var(--card-bg);
            border-top: 1px solid var(--accent);
            text-align: center;
            padding: 0.5rem;
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        @media (max-width: 576px) {
            .login-box {
                padding: 1.5rem;
                max-width: 90%;
            }
            .login-box h1 {
                font-size: 1.5rem;
            }
            .input-group .form-control {
                font-size: 1rem;
                padding: 0.6rem;
            }
            .input-group-append .input-group-text {
                padding: 0.6rem 0.8rem;
            }
            .btn-primary {
                font-size: 0.95rem;
                padding: 0.6rem 1.5rem;
            }
            .alert {
                font-size: 0.9rem;
                padding: 0.6rem;
            }
            .links a {
                font-size: 0.9rem;
            }
            .footer {
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h1><b>Lupa</b> Password</h1>
        <div class="card-body login-card-body">
            <?php if(isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            <?php if(isset($success)): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            <form action="" method="post">
                <div class="input-group mb-3">
                    <input type="email" name="email" class="form-control" placeholder="Email" required>
                    <div class="input-group-append">
                        <div class="input-group-text"><span class="fas fa-envelope"></span></div>
                    </div>
                </div>
                <div class="row justify-content-center">
                    <div class="col-12">
                        <button type="submit" name="submit" class="btn btn-primary btn-block">Kirim</button>
                    </div>
                    <div class="col-12 links">
                        <a href="index.php">Kembali ke Login</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="footer">
        Today: 08:34 PM WIB, Saturday, June 14, 2025
    </div>
    <script src="assets/plugins/jquery/jquery.min.js"></script>
    <script src="assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/dist/js/adminlte.min.js"></script>
</body>
</html>