<?php
// order_detail.php (sửa lỗi và hoàn chỉnh)
include 'connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['msg']) && $_GET['msg'] == 'huy_thanh_cong') {
    echo "<p class='success'>Yêu cầu hủy đơn đã được gửi thành công!</p>";
}

if (isset($_GET['msg']) && $_GET['msg'] == 'stop_cancel_success') {
    echo "<p class='success'>Bạn đã dừng yêu cầu hủy đơn thành công!</p>";
}
if (isset($_GET['msg']) && $_GET['msg'] == 'completed_success') {
    echo "<p class='success'>Bạn đã xác nhận nhận hàng thành công!</p>";
}



if (!isset($_GET['id'])) {
    die("Thiếu mã đơn hàng");
}

$order_id = intval($_GET['id']);
$user_id = intval($_SESSION['user_id']);

$order_query = "
    SELECT o.*, u.full_name
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    WHERE o.order_id = '$order_id' AND o.user_id = '$user_id'
    LIMIT 1
";

$order_result = mysqli_query($ocon, $order_query);
$order = mysqli_fetch_assoc($order_result);

if (!$order) {
    die("Không tìm thấy đơn hàng hoặc không có quyền xem!");
}

/* ==============================
   LẤY ĐỊA CHỈ GIAO HÀNG
============================== */
$address = null;

if (!empty($order['address_id'])) {
    $addr_q = "
        SELECT *
        FROM addresses
        WHERE address_id = {$order['address_id']}
          AND user_id = $user_id
        LIMIT 1
    ";
    $addr_rs = mysqli_query($ocon, $addr_q);
    $address = mysqli_fetch_assoc($addr_rs);
}

/* ==============================
   LẤY SẢN PHẨM TRONG ĐƠN
============================== */
$items_query = "
    SELECT 
        oi.*, b.title,
        (SELECT url FROM images WHERE book_id = b.book_id LIMIT 1) AS book_image
    FROM order_items oi
    LEFT JOIN books b ON oi.book_id = b.book_id
    WHERE oi.order_id = '$order_id'
";
$items_result = mysqli_query($ocon, $items_query);

function e($v){ return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }

$placeholder_img = "img/placeholder-book.png";
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ | Bookiee</title>
    <link rel="stylesheet" href="CSS/style.css">
    <link rel="stylesheet" href="CSS/order.css">
    <link rel="stylesheet" href="CSS/order_detail.css">


    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

<?php include 'header.php'; ?>

<div class="order_detail_page">
<div class="detail_container">
    <div class="order_tabs">
    <a href="order.php?status=all" class="tab">Tất cả</a>
    <a href="order.php?status=pending" class="tab">Chờ xác nhận</a>
    <a href="order.php?status=confirmed" class="tab">Đã xác nhận</a>
    <a href="order.php?status=shipping" class="tab">Đang vận chuyển</a>
    <a href="order.php?status=completed" class="tab">Đã hoàn thành</a>
    <a href="order.php?status=req_cancel" class="tab">Xử lý hủy</a>
    <a href="order.php?status=canceled" class="tab">Đã hủy</a>
    <a href="order.php?status=req_return" 
        class="tab <?= ($status=='req_return')?'active':'' ?>" 
        data-tab="req_return">Yêu cầu trả hàng</a>
        <a href="order.php?status=req_return" 
        class="tab <?= ($status=='req_return')?'active':'' ?>" 
        data-tab="returned">Đã chấp nhận trả hàng</a>
    

</div>


    <h2>
        Chi tiết đơn hàng #<?= e($order_id) ?>
    </h2>

    <!-- THÔNG TIN ĐƠN -->
    <div class="detail_box">
        <h3><i class="fa-solid fa-file-invoice"></i> Thông tin đơn hàng</h3>
        <p><strong>Ngày đặt:</strong> <?= e($order['order_date']) ?></p>
        <p><strong>Trạng thái:</strong> 
            <span class="status <?= e($order['order_status']) ?>">
                <?= e($order['order_status']) ?>
            </span>
        </p>
        <p><strong>Phương thức thanh toán:</strong> <?= e($order['payment_method']) ?></p>
        <p><strong>Thanh toán:</strong> <?= e($order['payment_status']) ?></p>
    </div>

    <!-- ĐỊA CHỈ -->
    <div class="detail_box">
        <h3><i class="fa-solid fa-location-dot"></i> Địa chỉ giao hàng</h3>

        <?php if ($address): ?>
            <p><strong>Người nhận:</strong> <?= e($address['receiver_name']) ?></p>
            <p><strong>SĐT:</strong> <?= e($address['receiver_phone']) ?></p>
            <p>
                <strong>Địa chỉ:</strong> 
                <?= e($address['specific_address']) ?>, 
                <?= e($address['ward']) ?>, 
                <?= e($address['district']) ?>, 
                <?= e($address['province']) ?>
            </p>
        <?php else: ?>
            <p>Không có địa chỉ giao hàng</p>
        <?php endif; ?>

    </div>

    <!-- SẢN PHẨM -->
    <div class="detail_box">
        <h3><i class="fa-solid fa-box"></i> Sản phẩm đã mua</h3>

        <?php while ($it = mysqli_fetch_assoc($items_result)): 
            $img = $it['book_image'] ?: $placeholder_img;
            $qty = intval($it['quantity']);
            $price = floatval($it['price_at_order']);
            $subtotal = $qty * $price;
        ?>
            <div class="item_row">
                <img src="<?= e($img) ?>" class="item_img">

                <div class="item_info">
                    <div class="item_title"><?= e($it['title']) ?></div>
                    <div>Số lượng: <?= $qty ?></div>
                    <div class="item_price"><?= number_format($price,0,',','.') ?>₫</div>
                </div>

                <div class="item_price">
                    <?= number_format($subtotal,0,',','.') ?>₫
                </div>
            </div>
        <?php endwhile; ?>

    </div>

    <!-- TỔNG TIỀN -->
    <div class="detail_box">
        <h3><i class="fa-solid fa-wallet"></i> Thanh toán</h3>

        <div class="total_row">
            <span>Tổng sản phẩm</span>
            <span><?= number_format($order['total_amount'],0,',','.') ?>₫</span>
        </div>

        <div class="total_row">
            <span>Phí vận chuyển</span>
            <span><?= number_format($order['shipping_fee'],0,',','.') ?>₫</span>
        </div>

        <div class="total_row total_final">
            <span>Tổng phải trả</span>
            <span><?= number_format($order['total_amount'] + $order['shipping_fee'],0,',','.') ?>₫</span>
        </div>
    </div>


<?php 
$status = strtolower(trim($order['order_status']));
?>

<div class="action_buttons">

<?php 
$status = strtolower(trim($order['order_status']));

switch ($status) {

    // ĐƠN ĐÃ HỦY
    case 'canceled':
        // Không hiện nút gì cả
        break;

   
    // ĐANG CHỜ DUYỆT HỦY
  
    case 'req_cancel':
    ?>
    <a href="cancel_stop.php?id=<?= $order_id ?>" 
       class="cancel_btn"
       onclick="return confirm('Bạn có chắc muốn dừng yêu cầu hủy đơn này không?');">
        Dừng yêu cầu hủy đơn
    </a>
    <?php
    break;

 
    // ĐÃ HOÀN THÀNH → CHO PHÉP TRẢ HÀNG
  
    case 'completed':
        ?>
        <a href="return_request.php?id=<?= $order_id ?>" 
           class="return_btn"
           onclick="return confirm('Bạn muốn yêu cầu trả hàng đơn này?');">
            Yêu cầu trả hàng
        </a>
        <?php
        break;
    case 'req_return':
    ?>
    <a href="return_cancel.php?id=<?= $order_id ?>" 
       class="cancel_btn"
       onclick="return confirm('Bạn muốn hủy yêu cầu trả hàng?');">
        Hủy yêu cầu trả hàng
    </a>
    <?php
    break;
  
    // CÁC TRẠNG THÁI CHO PHÉP HỦY
    // pending / confirmed
    case 'pending':
    case 'confirmed':
        ?>
        <a href="cancel_order_form.php?id=<?= $order_id ?>" 
           class="cancel_btn"
           onclick="return confirm('Bạn có chắc muốn hủy đơn này không?');">
            Yêu cầu hủy đơn
        </a>
        <?php
        break;

    // shipping → KHÔNG ĐƯỢC HỦY

    case 'shipping':
        echo '<p class="disabled_action">Đơn hàng đang được vận chuyển, không thể hủy.</p>';
         ?>
        <a href="update_completed.php?id=<?= $order_id ?>" 
           class="cancel_btn">
            Tôi đã nhận được hàng
        </a>
        <?php
        break;

   
    // TRẠNG THÁI KHÁC
    default:
        // Không hiển thị gì
        break;
}
?>

</div>





</div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
