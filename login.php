<?php
include 'connect.php';
session_start();

$errors = [];

// === reCAPTCHA keys ===
$recaptcha_secret = "6LeqIhMsAAAAAO7sELQwRxNpBMovbQvhWlUJcyZo";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['matkhau'] ?? '';

    // === Check reCAPTCHA ===
    $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';
    if (empty($recaptcha_response)) {
        $errors[] = "Vui lòng xác thực reCAPTCHA!";
    } else {
        $verify = file_get_contents(
            "https://www.google.com/recaptcha/api/siteverify?secret={$recaptcha_secret}&response={$recaptcha_response}"
        );
        $captcha_success = json_decode($verify);
        if (!$captcha_success->success) {
            $errors[] = "Xác thực reCAPTCHA thất bại!";
        }
    }

    if (empty($errors)) {
        if (empty($email) || empty($password)) {
            $errors[] = "Vui lòng điền đầy đủ thông tin.";
        } else {
            // Lấy thông tin user từ DB
            $stmt = $ocon->prepare("SELECT * FROM users WHERE email=?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();

            if ($user) {
                // === Giới hạn 5 lần login sai / 15 phút ===
                $lockout_time = 15 ; // 15 phút
                $last_attempt = strtotime($user['last_attempt']);
                $current_time = time();

                if ($user['login_attempts'] >= 5 && ($current_time - $last_attempt) < $lockout_time) {
                    $errors[] = "Bạn đã đăng nhập sai quá 5 lần. Vui lòng thử lại sau 15 phút.";
                } else {
                    // Reset nếu đã quá 15 phút
                    if (($current_time - $last_attempt) >= $lockout_time) {
                        $stmt_reset = $ocon->prepare("UPDATE users SET login_attempts=0, last_attempt=NULL WHERE user_id=?");
                        $stmt_reset->bind_param("i", $user['user_id']);
                        $stmt_reset->execute();
                        $user['login_attempts'] = 0;
                    }

                    // === Kiểm tra password ===
                    $password_correct = false;

                    // 1. Nếu password đã hash
                    if (password_verify($password, $user['password'])) {
                        $password_correct = true;
                    }
                    // 2. Nếu password plaintext (user cũ)
                    elseif ($password === $user['password']) {
                        $password_correct = true;

                        // Hash lại mật khẩu user cũ
                        $new_hashed = password_hash($password, PASSWORD_DEFAULT);
                        $stmt_update = $ocon->prepare("UPDATE users SET password=? WHERE user_id=?");
                        $stmt_update->bind_param("si", $new_hashed, $user['user_id']);
                        $stmt_update->execute();
                    }

                    if ($password_correct) {
                        // Login thành công → reset counter
                        $stmt_reset = $ocon->prepare("UPDATE users SET login_attempts=0, last_attempt=NULL WHERE user_id=?");
                        $stmt_reset->bind_param("i", $user['user_id']);
                        $stmt_reset->execute();

                        $_SESSION['user_id'] = $user['user_id'];
                        $_SESSION['username'] = $user['full_name'];
                        $_SESSION['role'] = $user['role'];

                        // Chuyển hướng
                        if ($_SESSION['role'] === 'admin') {
                            header("Location: admin_dashboard.php");
                        } else {
                            header("Location: index.php");
                        }
                        exit();
                    } else {
                        // Password sai → tăng số lần login sai
                        $stmt_update = $ocon->prepare("UPDATE users SET login_attempts=login_attempts+1, last_attempt=NOW() WHERE user_id=?");
                        $stmt_update->bind_param("i", $user['user_id']);
                        $stmt_update->execute();

                        $errors[] = "Email hoặc mật khẩu không đúng. Lần thử: " . ($user['login_attempts'] + 1);
                    }
                }
            } else {
                $errors[] = "Email hoặc mật khẩu không đúng.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/login.css">
</head>
<body>

<div class="register-box">
    <h2>Đăng nhập</h2>

    <?php if (!empty($errors)): ?>
        <div class="danger"><?= implode("<br>", array_map('htmlspecialchars', $errors)) ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="input-box">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>

        <div class="input-box">
            <label>Mật khẩu</label>
            <input type="password" name="matkhau" required>
        </div>

        <div class="g-recaptcha" data-sitekey="6LeqIhMsAAAAAP3becH6MiYEdC7EmyqQ7ZC8PajJ"></div>

        <button type="submit">Đăng nhập</button>
    </form>

    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

    <p>Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></p>
    <p><a href="forgot_password.php">Quên mật khẩu?</a></p>
</div>

</body>
</html>
