<?php
include 'connect.php';
session_start();
// Nếu chưa đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
// Nếu không phải ADMIN → chặn
if ($_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Gán tên admin
$admin_name = $_SESSION['username'] ?? "Admin";
if (!isset($_GET['id'])) {
    die("Thiếu mã đơn hàng!");
}

$order_id = intval($_GET['id']);

$order_q = "
    SELECT o.*, u.full_name
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    WHERE o.order_id = $order_id
    LIMIT 1
";
$order_rs = mysqli_query($ocon, $order_q);
$order = mysqli_fetch_assoc($order_rs);

if (!$order) {
    die("Không tìm thấy đơn hàng!");
}

// LẤY ĐỊA CHỈ
$address = null;
if (!empty($order['address_id'])) {
    $addr_q = "SELECT * FROM addresses WHERE address_id = {$order['address_id']} LIMIT 1";
    $addr_rs = mysqli_query($ocon, $addr_q);
    $address = mysqli_fetch_assoc($addr_rs);
}

// LẤY SẢN PHẨM TRONG ĐƠN
$items_q = "
    SELECT 
        oi.*, b.title,
        (SELECT url FROM images WHERE book_id = b.book_id LIMIT 1) AS book_image
    FROM order_items oi
    LEFT JOIN books b ON oi.book_id = b.book_id
    WHERE oi.order_id = $order_id
";
$items_rs = mysqli_query($ocon, $items_q);

// LÝ DO HỦY NẾU TRẠNG THÁI req_cancel
$cancel_info = null;
if ($order['order_status'] === 'req_cancel') {
    $cancel_q = "
        SELECT *
        FROM cancel_requests
        WHERE order_id = $order_id
        ORDER BY created_at DESC
        LIMIT 1
    ";
    $cancel_rs = mysqli_query($ocon, $cancel_q);
    $cancel_info = mysqli_fetch_assoc($cancel_rs);
}

// LÝ DO TRẢ HÀNG nếu req_return
$return_info = null;
if ($order['order_status'] === 'req_return') {
    $return_q = "
        SELECT *
        FROM return_requests
        WHERE order_id = $order_id
        ORDER BY created_at DESC
        LIMIT 1
    ";
    $return_rs = mysqli_query($ocon, $return_q);
    $return_info = mysqli_fetch_assoc($return_rs);
}

function e($v) { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
$placeholder_img = "img/placeholder-book.png";
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi tiết đơn hàng (Admin)</title>

    <link rel="stylesheet" href="css/admin_orders.css">
    <link rel="stylesheet" href="css/admin_order_detail.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

   
</head>

<body>

<div class="admin_layout">

    <?php include 'admin_nav.php'; ?>


    <div class="main-content">

        <div class="detail_container">
            
            <h2>Chi tiết đơn hàng <?= e($order_id) ?></h2>

            <!-- THÔNG TIN ĐƠN -->
            <div class="detail_box">
                <h3><i class="fa-solid fa-info-circle"></i> Thông tin đơn hàng</h3>
                <p><strong>Khách hàng:</strong> <?= e($order['full_name']) ?></p>
                <p><strong>Ngày đặt:</strong> <?= e($order['order_date']) ?></p>
                <p><strong>Trạng thái:</strong> 
                    <span class="status <?= e($order['order_status']) ?>">
                        <?= e($order['order_status']) ?>
                    </span>
                </p>
                <p><strong>Thanh toán:</strong> <?= e($order['payment_method']) ?> — <?= e($order['payment_status']) ?></p>
            </div>

            <!-- ĐỊA CHỈ GIAO HÀNG -->
            <div class="detail_box">
                <h3><i class="fa-solid fa-location-dot"></i> Địa chỉ giao hàng</h3>
                <?php if ($address): ?>
                    <p><strong>Người nhận:</strong> <?= e($address['receiver_name']) ?></p>
                    <p><strong>SĐT:</strong> <?= e($address['receiver_phone']) ?></p>
                    <p><strong>Địa chỉ:</strong>
                        <?= e($address['specific_address']) ?>,
                        <?= e($address['ward']) ?>,
                        <?= e($address['district']) ?>,
                        <?= e($address['province']) ?>
                    </p>
                <?php else: ?>
                    <p>Không có địa chỉ.</p>
                <?php endif; ?>
            </div>

            <!-- SẢN PHẨM -->
            <div class="detail_box">
                <h3><i class="fa-solid fa-box"></i> Sản phẩm trong đơn</h3>

                <?php while ($it = mysqli_fetch_assoc($items_rs)): 
                    $img = $it['book_image'] ?: $placeholder_img;
                    $qty = intval($it['quantity']);
                    $price = floatval($it['price_at_order']);
                    $subtotal = $qty * $price;
                ?>
                    <div class="item_row">
                        <img src="<?= e($img) ?>" class="item_img">
                        <div>
                            <p><strong><?= e($it['title']) ?></strong></p>
                            <p>Số lượng: <?= $qty ?></p>
                            <p>Đơn giá: <?= number_format($price,0,',','.') ?>₫</p>
                        </div>
                        <div style="margin-left:auto;font-weight:700;">
                            <?= number_format($subtotal,0,',','.') ?>₫
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <!-- TỔNG TIỀN -->
            <div class="detail_box">
                <h3><i class="fa-solid fa-wallet"></i> Thanh toán</h3>
                <p><strong>Tổng sản phẩm:</strong> <?= number_format($order['total_amount'],0,',','.') ?>₫</p>
                <p><strong>Phí vận chuyển:</strong> <?= number_format($order['shipping_fee'],0,',','.') ?>₫</p>
                <p><strong style="font-size:17px;">Tổng thanh toán:</strong> 
                    <?= number_format($order['total_amount'] + $order['shipping_fee'],0,',','.') ?>₫
                </p>
            </div>

            <!-- LÝ DO YÊU CẦU HỦY -->
            <?php if ($cancel_info): ?>
                <div class="cancel_reason">
                    <h3><i class="fa-solid fa-ban"></i> Lý do yêu cầu hủy đơn</h3>
                    <p><?= e($cancel_info['reason']) ?></p>
                    <p><em>Thời gian gửi: <?= e($cancel_info['created_at']) ?></em></p>
                </div>
            <?php endif; ?>

            <!-- LÝ DO TRẢ HÀNG -->
            <?php if ($return_info): ?>
                <div class="return_reason">
                    <h3><i class="fa-solid fa-rotate-left"></i> Lý do yêu cầu trả hàng</h3>
                    <p><?= e($return_info['reason']) ?></p>
                    <p><em>Thời gian gửi: <?= e($return_info['created_at']) ?></em></p>
                </div>
            <?php endif; ?>

            <!-- NÚT ADMIN XỬ LÝ -->
            <div class="action_buttons">
                <?php
                switch ($order['order_status']) {
                     case 'pending':
                        echo "
                            <a class='confirmed' href='admin_confirmed.php?id=$order_id'>Xác nhận</a>
                            <a class='reject' href='admin_reject.php?id=$order_id'>Từ chối</a>
                        ";
                        break;

                    case 'req_cancel':
                        echo "
                            <a class='approve' href='admin_approve_cancel.php?id=$order_id'>Duyệt hủy</a>
                            <a class='reject' href='admin_reject_cancel.php?id=$order_id'>Từ chối hủy</a>
                        ";
                        break;

                    case 'req_return':
                        echo "
                            <a class='approve' href='admin_return_accept.php?id=$order_id'>Chấp nhận trả hàng</a>
                            <a class='reject' href='admin_return_reject.php?id=$order_id'>Từ chối trả hàng</a>
                        ";
                        break;
                    case 'confirmed':
                        echo "
                            <a class='approve' href='admin_shipping.php?id=$order_id'>Đã giao cho bên vận chuyển</a>
                        ";
                        break;
                    case 'shipping':
                        echo "
                            <a class='approve' href='update_completed.php?id=$order_id'>Đã hoàn thành</a>
                        ";
                        break;

                    default:
                        break;
                }
                ?>
            </div>

        </div>

    </div> <!-- main-content -->

</div> <!-- admin_layout -->

</body>
</html>
