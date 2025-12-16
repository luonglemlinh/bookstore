<?php
include 'connect.php';
$message = "";
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
error_reporting(E_ALL);
ini_set('display_errors', 1);

// === Thêm reCAPTCHA Secret Key ===
$recaptcha_secret = "6LeqIhMsAAAAAO7sELQwRxNpBMovbQvhWlUJcyZo";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hoten = trim($_POST['hoten']);
    $email = trim($_POST['email']);
    $matkhau_raw = $_POST['matkhau']; // giữ lại để kiểm tra trước khi mã hóa
    $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';

    // === KIỂM TRA reCAPTCHA ===
    if (empty($recaptcha_response)) {
        $message = "Vui lòng xác thực reCAPTCHA!";
    } else {
        $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$recaptcha_secret}&response={$recaptcha_response}");
        $captcha_success = json_decode($verify);

        if (!$captcha_success->success) {
            $message = "Xác thực reCAPTCHA thất bại, vui lòng thử lại!";
        }
    }

    // Nếu reCAPTCHA hợp lệ, tiếp tục kiểm tra dữ liệu
    if (!$message) {
        // Kiểm tra dữ liệu nhập
        if (empty($hoten) || empty($email) || empty($matkhau_raw)) {
            $message = "Vui lòng nhập đầy đủ thông tin!";
        }
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || 
                !(substr($email, -10) === '@gmail.com' || substr($email, -12) === '@hvnh.edu.vn')) {
            $message = "Email phải hợp lệ và kết thúc bằng @gmail.com hoặc @hvnh.edu.vn!";
        }
        elseif (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/', $matkhau_raw)) {
            $message = "Mật khẩu phải có ít nhất 8 ký tự, gồm chữ hoa, chữ thường, số và ký tự đặc biệt!";
        }
        else {
            // Mã hóa mật khẩu
            $matkhau = password_hash($matkhau_raw, PASSWORD_DEFAULT);

            // Kiểm tra email trùng
            $check = $ocon->prepare("SELECT user_id FROM users WHERE email=?");
            $check->bind_param("s", $email);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $message = "Email đã tồn tại!";
            } else {
                // Thêm người dùng mới
                $stmt = $ocon->prepare("INSERT INTO users(full_name, email, password) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $hoten, $email, $matkhau);
                if ($stmt->execute()) {
                    header("Location: login.php?success=Đăng ký thành công! Mời đăng nhập.");
                    exit();
                } else {
                    $message = "Lỗi đăng ký!";
                }
                $stmt->close();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng ký tài khoản</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/register.css">
   
    <!-- reCAPTCHA JS -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>

<body>
    <div class="register-box">
        <h2>Đăng ký tài khoản</h2>

        <?php if ($message): ?>
            <div class="danger"><?= $message ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="input-box">
                <label for="hoten">Họ tên</label>
                <input type="text" id="hoten" name="hoten" placeholder="Nhập họ tên..." required>
            </div>

            <div class="input-box">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Nhập email..." required>
            </div>

            <div class="input-box">
                <label for="matkhau">Mật khẩu</label>
                <input type="password" id="matkhau" name="matkhau" placeholder="Nhập mật khẩu..." required>
            </div>

            <!-- reCAPTCHA -->
            <div class="g-recaptcha" data-sitekey="6LeqIhMsAAAAAP3becH6MiYEdC7EmyqQ7ZC8PajJ"></div>

            <button type="submit">Đăng ký</button>
        </form>

        <p>Đã có tài khoản? <a href="login.php">Đăng nhập</a></p>
    </div>
</body>
</html>
