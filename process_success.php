<?php
// process_success.php
include 'connect.php';
session_start();

$code = $_GET['code'] ?? '';
if (!$code) die("Thiếu code.");

$code_esc = mysqli_real_escape_string($ocon, $code);

// Kiểm tra lại online_queue
$q = mysqli_query($ocon, "SELECT * FROM online_queue WHERE trans_code = '$code_esc' LIMIT 1");
if (!$q || mysqli_num_rows($q) == 0) die("Giao dịch không tồn tại.");
$queue = mysqli_fetch_assoc($q);

if ($queue['status'] !== 'success') {
    die("Giao dịch chưa được xác nhận.");
}

// Lấy payload (đã lưu ở generate_qr.php)
$payload_json = $queue['payload'] ?? null;
if (!$payload_json) {
    die("Thiếu payload đơn hàng. Không thể tạo order tự động.");
}
$pending = json_decode($payload_json, true);
if (!$pending) die("Payload lỗi.");

// Bắt đầu insert order
mysqli_begin_transaction($ocon);
try {
    $user_id = intval($pending['user_id']);
    $address_id = intval($pending['address_id']);
    $total = floatval($pending['total']);
    $shipping = floatval($pending['shipping'] ?? 0);
    $notes = mysqli_real_escape_string($ocon, $pending['notes'] ?? '');
    $subtotal=$total-$shipping;

    // Insert orders
   // Insert orders với enum hợp lệ
$sql = "INSERT INTO orders (
            user_id, address_id, total_amount, payment_method, payment_status, shipping_fee, order_status
        ) VALUES (
            $user_id, $address_id, $subtotal, 'online', 'paid', $shipping, 'pending'
        )";

if (!mysqli_query($ocon, $sql)) throw new Exception("Lỗi INSERT orders: " . mysqli_error($ocon));

$order_id = mysqli_insert_id($ocon); // Gán sau khi insert thành công


    // Insert order_items
    foreach ($pending['items'] as $it) {
        $bid = intval($it['book_id']);
        $qty = intval($it['qty']);
        $price = floatval($it['price']);
        $sql_item = "INSERT INTO order_items (order_id, book_id, quantity, price_at_order)
                     VALUES ($order_id, $bid, $qty, $price)";
        if (!mysqli_query($ocon, $sql_item)) throw new Exception(mysqli_error($ocon));

        // Trừ tồn
        mysqli_query($ocon, "UPDATE books SET stock_quantity = stock_quantity - $qty WHERE book_id = $bid");
    }

    // Update transaction_history: link order_id, set status success (nếu chưa)
    $trans_code_esc = mysqli_real_escape_string($ocon, $code);
    mysqli_query($ocon, "UPDATE transaction_history SET order_id = $order_id, status = 'success' WHERE transaction_code = '$trans_code_esc'");

    // Xóa cart nếu cần
    if (empty($pending['is_buy_now']) || !$pending['is_buy_now']) {
        if (!empty($pending['cart_id'])) {
            $cid = intval($pending['cart_id']);
            mysqli_query($ocon, "DELETE FROM cart_items WHERE cart_id = $cid");
        } else {
            unset($_SESSION['cart']);
        }
    }

    // Optionally update online_queue.status (already success)
    mysqli_query($ocon, "UPDATE online_queue SET status='done' WHERE queue_id = " . intval($queue['queue_id']));

    mysqli_commit($ocon);

    // Clear session pending_order
    unset($_SESSION['pending_order']);

    // Redirect hoặc hiển thị trang thành công
    header("Location: payment_success.php?order_id=" . $order_id);
    exit();

} catch (Exception $e) {
    mysqli_rollback($ocon);
    die("Lỗi khi tạo đơn: " . $e->getMessage());
}
