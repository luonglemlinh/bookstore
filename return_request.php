<?php
include 'connect.php';
session_start();

/* ================== CHECK LOGIN ================== */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

/* ================== GET ORDER ID ================== */
if (!isset($_GET['id']) || (int)$_GET['id'] <= 0) {
    die("Thiếu mã đơn hàng");
}
$order_id = (int)$_GET['id'];

/* ================== KIỂM TRA ĐƠN HÀNG ================== */
$sql_order = "
    SELECT * 
    FROM orders 
    WHERE order_id = ? AND user_id = ?
    LIMIT 1
";
$stmt = $ocon->prepare($sql_order);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order_rs = $stmt->get_result();

if ($order_rs->num_rows === 0) {
    die("Không tìm thấy đơn hàng hoặc bạn không có quyền.");
}

/* ================== LẤY SẢN PHẨM ================== */
$sql_items = "
    SELECT 
        oi.quantity,
        b.title,
        (SELECT url FROM images WHERE book_id = b.book_id LIMIT 1) AS book_image
    FROM order_items oi
    JOIN books b ON oi.book_id = b.book_id
    WHERE oi.order_id = ?
";
$stmt2 = $ocon->prepare($sql_items);
$stmt2->bind_param("i", $order_id);
$stmt2->execute();
$items_rs = $stmt2->get_result();

/* ================== GỬI YÊU CẦU TRẢ ================== */
if (isset($_POST['submit'])) {

    $reason      = trim($_POST['reason']);
    $description = trim($_POST['description']);

    /* ===== Upload ảnh ===== */
    $uploaded_images = [];

    if (!file_exists("uploads/returns")) {
        mkdir("uploads/returns", 0777, true);
    }

    if (!empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp) {

            if ($_FILES['images']['error'][$key] !== 0) continue;

            $filename = time() . "_" . rand(1000,9999) . "_" . basename($_FILES['images']['name'][$key]);
            $path = "uploads/returns/" . $filename;

            if (move_uploaded_file($tmp, $path)) {
                $uploaded_images[] = $path;
            }
        }
    }

    $img_str = implode(",", $uploaded_images);

    /* ===== Lấy trạng thái cũ ===== */
    $get_old = $ocon->prepare("SELECT order_status FROM orders WHERE order_id = ?");
    $get_old->bind_param("i", $order_id);
    $get_old->execute();
    $get_old->bind_result($old_status);
    $get_old->fetch();
    $get_old->close();

    /* ===== Lưu return_requests ===== */
    $sql_insert = "
    INSERT INTO return_requests
    (order_id, user_id, reason, description, images, status, created_at)
    VALUES (?, ?, ?, ?, ?, 'pending', NOW())
    ";

    $stmt3 = $ocon->prepare($sql_insert);
    if (!$stmt3) {
        die("SQL return_requests lỗi: " . $ocon->error);
    }

    $stmt3->bind_param(
        "iisss",
        $order_id,
        $user_id,
        $reason,
        $description,
        $img_str
    );
    $stmt3->execute();


    /* ===== Cập nhật trạng thái đơn ===== */
    $sql_update = "
        UPDATE orders 
        SET order_status = 'req_return'
        WHERE order_id = ? AND user_id = ?
    ";
    $stmt4 = $ocon->prepare($sql_update);
    $stmt4->bind_param("ii", $order_id, $user_id);
    $stmt4->execute();

    /* ===== Notification cho admin ===== */
    $sql_noti = "
        INSERT INTO notifications
        (title, message, type, order_id, is_read, created_at)
        VALUES (?, ?, ?, ?, 0, NOW())
    ";

    $noti = $ocon->prepare($sql_noti);
    if (!$noti) {
        die("SQL notifications lỗi: " . $ocon->error);
    }

    $title = "Yêu cầu trả hàng mới";
    $msg   = "Đơn hàng #$order_id vừa có yêu cầu trả hàng từ khách #$user_id";
    $type  = "return_request";

    $noti->bind_param("sssi", $title, $msg, $type, $order_id);
    $noti->execute();

    header("Location: return_request.php?id=$order_id&sent=1");
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Yêu cầu trả hàng</title>

<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="css/return_request.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
.return_item{display:flex;gap:15px;background:#fff;padding:12px;border-radius:10px;margin-bottom:12px;border:1px solid #eee}
.return_item_img{width:90px;height:120px;object-fit:cover;border-radius:8px}
.alert-success{background:#d4f8d4;color:#0a7a0a;padding:12px;border-radius:8px;margin-bottom:15px}
.cancel_btn{display:block;text-align:center;padding:14px;border-radius:10px;background:linear-gradient(135deg,#0f9b3f,#0a7a32);color:#fff;font-weight:600;text-decoration:none}
</style>
</head>

<body>
<?php include 'header.php'; ?>

<div class="container">
<h2>Yêu cầu trả hàng đơn #<?= $order_id ?></h2>

<?php if (isset($_GET['sent'])): ?>
<div class="alert-success">
<i class="fa-solid fa-circle-check"></i> Gửi yêu cầu trả hàng thành công
</div>
<?php endif; ?>

<h3>Sản phẩm trong đơn</h3>

<?php while ($it = $items_rs->fetch_assoc()): ?>
<div class="return_item">
<img src="<?= $it['book_image'] ?: 'img/placeholder-book.png' ?>" class="return_item_img">
<div>
<strong><?= htmlspecialchars($it['title']) ?></strong>
<p>Số lượng: <?= $it['quantity'] ?></p>
</div>
</div>
<?php endwhile; ?>

<?php if (!isset($_GET['sent'])): ?>
<form method="post" enctype="multipart/form-data">
<label>Lý do</label>
<select name="reason" required>
<option value="">-- Chọn --</option>
<option>Sản phẩm bị lỗi</option>
<option>Không giống mô tả</option>
<option>Hư hỏng khi vận chuyển</option>
<option>Sai sản phẩm</option>
<option>Thiếu hàng</option>
<option>Khác</option>
</select>

<label>Mô tả</label>
<textarea name="description" required></textarea>

<label>Ảnh minh chứng</label>
<input type="file" name="images[]" multiple>

<button type="submit" name="submit">Gửi yêu cầu</button>
</form>
<?php else: ?>
<a href="return_cancel.php?id=<?= $order_id ?>" class="cancel_btn"
onclick="return confirm('Hủy yêu cầu trả hàng?')">Hủy yêu cầu trả hàng</a>
<?php endif; ?>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
