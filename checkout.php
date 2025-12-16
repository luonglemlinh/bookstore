<?php
include 'connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = intval($_SESSION['user_id']);
$message = [];

/* =====================================================
   1) LOAD SẢN PHẨM (BUY NOW hoặc CART)
===================================================== */

$items = [];
$is_buy_now = false;

if (isset($_GET['id']) && isset($_GET['qty'])) {

    $bid = intval($_GET['id']);
    $qty = intval($_GET['qty']);

    $q = mysqli_query($ocon,
        "SELECT b.*, 
            (SELECT url FROM images WHERE book_id = b.book_id LIMIT 1) AS image
         FROM books b 
         WHERE b.book_id = $bid AND b.status = 'active'"
    );

    if ($q && mysqli_num_rows($q) > 0) {
        $p = mysqli_fetch_assoc($q);
        $items[] = [
            "book_id" => $p["book_id"],
            "title" => $p["title"],
            "price" => $p["discounted_price"],
            "qty" => $qty,
            "image" => $p["image"]
        ];
        $is_buy_now = true;
    }

} else {
    // Lấy cart_id
    $q_cart = mysqli_query($ocon, "SELECT cart_id FROM carts WHERE user_id = $user_id");
    if ($q_cart && mysqli_num_rows($q_cart) > 0) {
        $cart = mysqli_fetch_assoc($q_cart);
        $cart_id = $cart['cart_id'];

        $q_items = mysqli_query($ocon,
            "SELECT ci.quantity as qty, 
                    b.book_id, b.title, b.discounted_price as price,
                    (SELECT url FROM images WHERE book_id=b.book_id LIMIT 1) AS image
             FROM cart_items ci
             JOIN books b ON ci.book_id = b.book_id
             WHERE ci.cart_id = $cart_id"
        );

        while ($r = mysqli_fetch_assoc($q_items)) {
            $items[] = $r;
        }
    }
}

if (empty($items)) {
    header("Location: index.php");
    exit();
}

/* =====================================================
   2) TÍNH TIỀN
===================================================== */

$subtotal = 0;
foreach ($items as $it) $subtotal += $it['price'] * $it['qty'];

$shipping_fee = 15000;

$total = $subtotal + $shipping_fee ;

/* =====================================================
   3) LOAD ĐỊA CHỈ USER
===================================================== */

$addr_list = mysqli_query($ocon, 
    "SELECT * FROM addresses WHERE user_id = $user_id ORDER BY is_default DESC"
);

/* =====================================================
   4) XỬ LÝ ĐẶT HÀNG
===================================================== */

if (isset($_POST['place_order'])) {

    $addr_id = intval($_POST['address_id']);
    $payment_method = $_POST['payment_method'];
    $notes = mysqli_real_escape_string($ocon, $_POST['notes']);

    if ($addr_id == 0) {
        $message[] = "Vui lòng chọn địa chỉ giao hàng!";
    } 
    else 
    {
        // --- THANH TOÁN ONLINE ---
        if ($payment_method == "online") {
            $_SESSION["pending_order"] = [
                "user_id"    => $user_id,
                "address_id" => $addr_id,
                "items"      => $items,
                "subtotal"   => $subtotal,
                "shipping"   => $shipping_fee,
                "total"      => $total,
                "notes"      => $notes,
                "is_buy_now" => $is_buy_now,
                "cart_id"    => $is_buy_now ? null : $cart_id
            ];
            header("Location: generate_qr.php");
            exit();
        }

        // --- THANH TOÁN COD ---
        mysqli_begin_transaction($ocon);

        try {
            mysqli_query($ocon,
                "INSERT INTO orders (user_id, address_id, total_amount, payment_method, shipping_fee, order_status) 
                 VALUES ($user_id, $addr_id, $subtotal, 'COD', $shipping_fee, 'pending')"
            );

            $order_id = mysqli_insert_id($ocon);
            // ================== THÔNG BÁO ĐƠN HÀNG MỚI CHO ADMIN ==================
            $title   = "Đơn hàng mới";
            $notif_message = "Có đơn hàng mới #" . $order_id . " vừa được khách đặt";
            $type    = "new_order";

            mysqli_query($ocon, "
                INSERT INTO notifications (title, message, type, is_read, created_at, order_id)
                VALUES (
                    '$title',
                    '$notif_message',
                    '$type',
                    0,
                    NOW(),
                    $order_id
                )
            ");

            // ====================================================================


            foreach ($items as $it) {
                mysqli_query($ocon,
                    "INSERT INTO order_items (order_id, book_id, quantity, price_at_order)
                     VALUES ($order_id, {$it['book_id']}, {$it['qty']}, {$it['price']})"
                );

                mysqli_query($ocon,
                    "UPDATE books 
                     SET stock_quantity = stock_quantity - {$it['qty']}
                     WHERE book_id = {$it['book_id']}"
                );
            }

            if (!$is_buy_now) {
                mysqli_query($ocon, "DELETE FROM cart_items WHERE cart_id = $cart_id");
            }
            $notif_msg = "Khách hàng vừa đặt đơn hàng mới #" . $order_id;
            
            // user_id ở đây là ID khách hàng, type='new_order'
            mysqli_query($ocon, 
                "INSERT INTO notifications_customer (user_id, type, message, reference_id, created_at) 
                 VALUES ($user_id, 'new_order', '$notif_msg', $order_id, NOW())"
            );
            // -----------------------------------------------------------

            mysqli_commit($ocon);
            
            // --- THÔNG BÁO THÀNH CÔNG VÀ CHUYỂN HƯỚNG ---
            echo "<script>
                alert('🎉 Đặt hàng thành công! Cảm ơn bạn đã mua sắm.');
                window.location.href = 'order.php?status=pending';
            </script>";
            exit(); 
            // --------------------------------------------

        } catch (Exception $e) {
            mysqli_rollback($ocon);
            $message[] = "Đặt hàng thất bại! Vui lòng thử lại.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Thanh toán</title>
<link rel="stylesheet" href="CSS/style.css">
<link rel="stylesheet" href="CSS/checkout.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

<?php include 'header.php'; ?>

<?php if (!empty($message)): ?>
    <div style="background: #fff3cd; color: #856404; padding: 15px; margin: 20px auto; max-width: 1200px; border-radius: 5px; border: 1px solid #ffeeba;">
        <?php foreach($message as $msg): ?>
            <p style="margin: 5px 0;"><i class="fa-solid fa-circle-exclamation"></i> <?= $msg ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="checkout-container">

    <div>
        <div class="checkout-box">
            <?php

$add_addr_link = "add_address.php";
if (isset($_GET['id']) && isset($_GET['qty'])) {
    $add_addr_link .= "?id=" . intval($_GET['id']) . "&qty=" . intval($_GET['qty']);
}
?>

<a href="<?= $add_addr_link ?>" class="add-address-btn">
    <i class="fa-solid fa-plus"></i> Thêm địa chỉ
</a>

            <?php if (mysqli_num_rows($addr_list) > 0): ?>
                <?php while ($a = mysqli_fetch_assoc($addr_list)): ?>
                    <label class="address-card address-item <?= $a['is_default'] ? 'selected' : '' ?>" id="addr_<?= $a['address_id'] ?>">
                        <div class="addr-row">
                            <div class="addr-left">
                                <input type="radio" name="address_radio" 
                                       value="<?= $a['address_id'] ?>" 
                                       <?= $a['is_default'] ? "checked" : "" ?>>

                                <div>
                                    <div class="addr-name">
                                        <?= $a['receiver_name'] ?> | <?= $a['receiver_phone'] ?>
                                    </div>
                                    <div class="addr-detail">
                                        <?= $a['specific_address'] ?>, 
                                        <?= $a['ward'] ?>, 
                                        <?= $a['district'] ?>, 
                                        <?= $a['province'] ?>
                                    </div>
                                </div>
                            </div>

                            <div class="addr-right">
                                <a href="edit_address.php?id=<?= $a['address_id'] ?>" class="addr-action-link">Sửa</a>
                                <span class="divider">|</span>
                                <a href="delete_address.php?id=<?= $a['address_id'] ?>" class="addr-action-link"
                                   onclick="return confirm('Bạn có chắc muốn xóa địa chỉ này?');">
                                   Xóa
                                </a>
                            </div>
                        </div>
                    </label>
                    <?php if ($a['is_default']) echo "<script>window.onload = function() { document.getElementById('address_id_hidden').value = " . $a['address_id'] . "; }</script>"; ?>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="padding:10px; color:red;">Bạn chưa có địa chỉ nào. Vui lòng thêm địa chỉ.</p>
            <?php endif; ?>

        </div>

        <div class="checkout-box">
            <div class="box-title"><i class="fas fa-credit-card"></i> Phương thức thanh toán</div>

            <label class="payment-option selected">
                <input type="radio" name="payment_method" value="COD" checked>
                <i class="fa-solid fa-money-bill"></i> Thanh toán khi nhận hàng (COD)
            </label>

            <label class="payment-option">
                <input type="radio" name="payment_method" value="online">
                <i class="fa-solid fa-wallet"></i> Thanh toán Online (QR code)
            </label>
        </div>

        <div class="checkout-box">
            <div class="box-title"><i class="fas fa-shopping-bag"></i> Sản phẩm</div>

            <?php foreach ($items as $it): ?>
                <div class="product-item">
                    <img src="<?= $it['image'] ?>">
                    <div>
                        <div><?= $it['title'] ?></div>
                        <div>x <?= $it['qty'] ?></div>
                        <b><?= number_format($it['price']) ?>₫</b>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div>
        <div class="checkout-box">
            <div class="box-title"><i class="fas fa-receipt"></i> Tóm tắt đơn</div>

            <div class="summary-row"><span>Tạm tính</span><b><?= number_format($subtotal) ?>₫</b></div>
            <div class="summary-row"><span>Vận chuyển</span><b><?= number_format($shipping_fee) ?>₫</b></div>

            

           

            <div class="summary-total">
                Tổng thanh toán: <?= number_format($total) ?>₫
            </div>

            <form method="POST" id="orderSubmit">
                <input type="hidden" name="address_id" id="address_id_hidden" value="">
                <input type="hidden" name="payment_method" id="payment_hidden" value="COD">
                <input type="hidden" name="notes" value="">

                <button type="submit" name="place_order" class="place-order-btn" onclick="return validateOrder()">
                    Đặt hàng
                </button>
            </form>
        </div>
    </div>

</div>

<?php include 'footer.php'; ?>

<script>
// Logic chọn địa chỉ
document.querySelectorAll('.address-card').forEach(function(card){
    card.addEventListener('click', function(){
        document.querySelectorAll('.address-card').forEach(c => c.classList.remove('selected'));
        this.classList.add('selected');

        let radio = this.querySelector('input');
        radio.checked = true;
        document.getElementById('address_id_hidden').value = radio.value;
    });
});

// Logic chọn phương thức thanh toán
document.querySelectorAll('.payment-option').forEach(function(opt){
    opt.addEventListener('click', function(){
        document.querySelectorAll('.payment-option').forEach(o => o.classList.remove('selected'));
        this.classList.add('selected');

        let radio = this.querySelector('input');
        radio.checked = true;
        document.getElementById('payment_hidden').value = radio.value;
    });
});

// Kiểm tra trước khi submit (JS Validation)
function validateOrder() {
    var addr = document.getElementById('address_id_hidden').value;
    if (!addr || addr == 0 || addr == "") {
        alert("Vui lòng chọn địa chỉ giao hàng!");
        return false;
    }
    return true;
}
</script>

</body>
</html>