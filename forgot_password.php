<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
include 'connect.php'; // Kết nối DB

$message = "";
$msgClass = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    // Kiểm tra email có tồn tại trong DB không
    $stmt = $ocon->prepare("SELECT user_id, full_name FROM users WHERE email = ?");
    if (!$stmt) {
        die("Prepare failed: " . $ocon->error);
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        // Tạo token ngẫu nhiên và thời hạn 15 phút
        $token = bin2hex(random_bytes(16));
        $expiry = date("Y-m-d H:i:s", time() + 900);

        // Cập nhật token vào DB
        $update = $ocon->prepare("UPDATE users SET reset_token=?, token_expiry=? WHERE user_id=?");
        if (!$update) {
            die("Prepare failed: " . $ocon->error);
        }
        $update->bind_param("ssi", $token, $expiry, $user['user_id']);
        $update->execute();

        // Tạo link đặt lại mật khẩu
        $resetLink = "http://localhost/Babo-Bookstore-Website/reset_password.php?token=$token";

        // Soạn email
        $subject = "Đặt lại mật khẩu - Hệ thống hỗ trợ";
        $body = "
        <h3>Xin chào {$user['full_name']}!</h3>
        <p>Bạn vừa yêu cầu đặt lại mật khẩu. Nhấn vào liên kết bên dưới để tạo mật khẩu mới:</p>
        <p><a href='$resetLink'>$resetLink</a></p>
        <p>Liên kết sẽ hết hạn sau 15 phút.</p>
        ";

        // Gửi email bằng PHPMailer
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'lephuongthao14072005@gmail.com'; // Thay bằng email của bạn
            $mail->Password   = 'kxdhecaiaixlndjd'; // App password Gmail
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('lephuongthao14072005@gmail.com', 'Hệ thống hỗ trợ');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            $mail->send();
            $message = "✅ Đã gửi email đặt lại mật khẩu tới $email!";
            $msgClass = "success";
        } catch (Exception $e) {
            $message = "❌ Lỗi khi gửi email: " . $mail->ErrorInfo;
            $msgClass = "danger";
        }

    } else {
        $message = "⚠️ Email không tồn tại trong hệ thống!";
        $msgClass = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quên mật khẩu</title>
    <link rel="stylesheet" href="css/register.css">
</head>
<body>

<div class="register-box">
    <h2>Quên mật khẩu</h2>

    <?php if (!empty($message)): ?>
        <div class="<?= $msgClass ?>"><?= $message ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="input-box">
            <label for="email">Email người nhận</label>
            <input type="email" id="email" name="email" placeholder="Nhập email cần gửi..." required>
        </div>
        <button type="submit">Gửi email</button>
    </form>

    <p>Quay lại <a href="login.php">Đăng nhập</a></p>
</div>

</body>
</html>
