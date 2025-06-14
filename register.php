<?php
include 'conf/config.php';
// Proses form register
if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $email = trim($_POST['email']);
    $nama_lengkap = trim($_POST['nama_lengkap']);
    // Validasi sederhana
    if (empty($username) || empty($password) || empty($email) || empty($nama_lengkap)) {
        $error = "Semua field harus diisi.";
    } else {
        // Cek username/email sudah ada
        $cek = mysqli_query($koneksi, "SELECT * FROM tb_users WHERE username='$username' OR email='$email'");
        if (mysqli_num_rows($cek) > 0) {
            $error = "Username atau email sudah terdaftar.";
        } else {
            // Simpan user baru (level default wartawan)
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $simpan = mysqli_query($koneksi, "INSERT INTO tb_users (username, password, email, nama_lengkap, level) VALUES ('$username', '$password_hash', '$email', '$nama_lengkap', 'wartawan')");
            if ($simpan) {
                $success = "Registrasi berhasil! Silakan login.";
            } else {
                $error = "Registrasi gagal. Silakan coba lagi.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Register User</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap">
    <link rel="stylesheet" href="assets/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="assets/dist/css/adminlte.min.css">
    <style>
        body {
            background-color: #e6e6fa; /* Lavender background */
            font-family: 'Roboto', sans-serif; /* Updated font */
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .register-box {
            background-color: #f3e5f5; /* Light purple */
            border: 2px solid #ce93d8; /* Medium purple border */
            border-radius: 1.2rem;
            box-shadow: 0 0 18px 0 rgba(206, 147, 216, 0.18); /* Purple shadow */
            width: 100%;
            max-width: 500px;
            padding: 30px;
            text-align: center;
        }
        .register-logo {
            font-size: 32px;
            color: #333;
            margin-bottom: 20px;
            font-weight: 700;
        }
        .input-group {
            margin-bottom: 25px;
        }
        .input-group .form-control {
            border-radius: 0.25rem 0 0 0.25rem;
            border-right: none;
            font-size: 1.3rem;
            padding: 15px;
            font-family: 'Roboto', sans-serif; /* Updated font */
        }
        .input-group-append .input-group-text {
            background-color: #fff;
            border-radius: 0 0.25rem 0.25rem 0;
            border-left: none;
            color: #6a1b9a; /* Dark purple */
            padding: 12px 18px;
        }
        button[type="submit"], .btn-primary {
            background-color: #8e24aa !important; /* Vibrant purple */
            border: none !important;
            color: #fff !important;
            padding: 10px 30px;
            border-radius: 2rem;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(142, 36, 170, 0.18); /* Purple shadow */
            transition: background 0.2s, box-shadow 0.2s;
            font-family: 'Roboto', sans-serif; /* Updated font */
        }
        button[type="submit"]:hover, .btn-primary:hover {
            background-color: #6a1b9a !important; /* Darker purple on hover */
            box-shadow: 0 4px 16px rgba(106, 27, 154, 0.18); /* Darker shadow */
        }
        .links a {
            color: #6a1b9a; /* Dark purple */
            text-decoration: none;
            font-size: 1.2rem;
            font-family: 'Roboto', sans-serif; /* Updated font */
        }
        .links a:hover {
            text-decoration: underline;
        }
        .alert {
            border-radius: 0.25rem;
            padding: 15px;
            margin-bottom: 25px;
            text-align: left;
            font-size: 1.2rem;
            font-family: 'Roboto', sans-serif; /* Updated font */
        }
        .alert-danger {
            background-color: #f8bbd0; /* Light pink for contrast */
            border-color: #f48fb1;
            color: #880e4f; /* Dark pink text */
        }
        .alert-success {
            background-color: #d1c4e9; /* Light purple success */
            border-color: #b39ddb;
            color: #311b92; /* Deep purple text */
        }
        .footer {
            margin-top: 20px;
            font-size: 16px;
            color: #666;
            font-family: 'Roboto', sans-serif; /* Updated font */
        }
        @media (max-width: 576px) {
            .register-box {
                padding: 20px;
                max-width: 400px;
            }
            .register-logo {
                font-size: 28px;
            }
            .input-group .form-control {
                font-size: 1.1rem;
                padding: 12px;
            }
            .input-group-append .input-group-text {
                padding: 10px 15px;
            }
            .btn-primary {
                font-size: 1.2rem;
                padding: 12px;
            }
            .alert {
                font-size: 1.1rem;
                padding: 12px;
            }
            .links a {
                font-size: 1.1rem;
            }
            .footer {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
<div class="register-box">
    <div class="register-logo">
        <b>Register</b> User
    </div>
    <div class="card-body register-card-body">
        <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <?php if(isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        <form action="" method="post">
            <div class="input-group mb-3">
                <input type="text" name="username" class="form-control" placeholder="Username" required>
                <div class="input-group-append">
                    <div class="input-group-text"><span class="fas fa-user"></span></div>
                </div>
            </div>
            <div class="input-group mb-3">
                <input type="password" name="password" class="form-control" placeholder="Password" required>
                <div class="input-group-append">
                    <div class="input-group-text"><span class="fas fa-lock"></span></div>
                </div>
            </div>
            <div class="input-group mb-3">
                <input type="email" name="email" class="form-control" placeholder="Email" required>
                <div class="input-group-append">
                    <div class="input-group-text"><span class="fas fa-envelope"></span></div>
                </div>
            </div>
            <div class="input-group mb-3">
                <input type="text" name="nama_lengkap" class="form-control" placeholder="Nama Lengkap" required>
                <div class="input-group-append">
                    <div class="input-group-text"><span class="fas fa-id-card"></span></div>
                </div>
            </div>
            <div class="row">
                <div class="col-8">
                    <div class="links"><a href="index.php">Sudah punya akun? Login</a></div>
                </div>
                <div class="col-4">
                    <button type="submit" name="register" class="btn btn-primary btn-block">Daftar</button>
                </div>
            </div>
        </form>
    </div>
    <div class="footer">
        Today: 08:10 PM WIB, Saturday, June 14, 2025
    </div>
</div>
<script src="assets/plugins/jquery/jquery.min.js"></script>
<script src="assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/dist/js/adminlte.min.js"></script>
</body>
</html>