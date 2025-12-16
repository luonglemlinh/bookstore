<?php
// add_to_cart.php
include 'connect.php';
session_start();

// redirect back (fallback)
$redirect = $_SERVER['HTTP_REFERER'] ?? 'index.php';

// bảo vệ input
$book_id = isset($_POST['book_id']) ? intval($_POST['book_id']) : 0;
$quantity = isset($_POST['quantity']) ? max(1, intval($_POST['quantity'])) : 1;

if ($book_id <= 0) {
    $_SESSION['cart_msg'] = "Sản phẩm không hợp lệ.";
    header("Location: $redirect");
    exit();
}

// user (0 = guest)
$user_id = $_SESSION['user_id'] ?? 0;

/*
 * 1) Lấy hoặc tạo cart (dùng user_id = 0 cho guest)
 */
$cart_id = 0;
$q = "SELECT cart_id FROM carts WHERE user_id = $user_id LIMIT 1";
$res = mysqli_query($ocon, $q);
if ($res && mysqli_num_rows($res) > 0) {
    $row = mysqli_fetch_assoc($res);
    $cart_id = intval($row['cart_id']);
} else {
    // Tạo cart mới
    $ins = mysqli_query($ocon, "INSERT INTO carts(user_id) VALUES($user_id)");
    if (!$ins) {
        $_SESSION['cart_msg'] = "Lỗi tạo giỏ hàng: " . mysqli_error($ocon);
        header("Location: $redirect");
        exit();
    }
    $cart_id = mysqli_insert_id($ocon);
}

/*
 * 2) Kiểm tra sách đã có trong cart chưa -> nếu có thì cập nhật số lượng, nếu chưa thì insert
 *
 * Sử dụng prepared statement an toàn, nhưng kiểm tra prepare() trước khi bind_param()
 */
$check_sql = "SELECT cart_item_id, quantity FROM cart_items WHERE cart_id = ? AND book_id = ? LIMIT 1";
$stmt = mysqli_prepare($ocon, $check_sql);
if ($stmt === false) {
    // nếu prepare thất bại, show lỗi để dev debug (chỉ tạm thời)
    $_SESSION['cart_msg'] = "Lỗi prepare check: " . mysqli_error($ocon);
    header("Location: $redirect");
    exit();
}
mysqli_stmt_bind_param($stmt, "ii", $cart_id, $book_id);
mysqli_stmt_execute($stmt);
$check_res = mysqli_stmt_get_result($stmt);

if ($check_res && mysqli_num_rows($check_res) > 0) {
    $item = mysqli_fetch_assoc($check_res);
    $new_qty = max(1, intval($item['quantity']) + $quantity);

    $update_sql = "UPDATE cart_items SET quantity = ? WHERE cart_item_id = ?";
    $upd = mysqli_prepare($ocon, $update_sql);
    if ($upd === false) {
        $_SESSION['cart_msg'] = "Lỗi prepare update: " . mysqli_error($ocon);
        header("Location: $redirect");
        exit();
    }
    mysqli_stmt_bind_param($upd, "ii", $new_qty, $item['cart_item_id']);
    mysqli_stmt_execute($upd);
    mysqli_stmt_close($upd);

    $_SESSION['cart_msg'] = "Đã cập nhật số lượng trong giỏ hàng.";
} else {
    // insert new row
    $insert_sql = "INSERT INTO cart_items(cart_id, book_id, quantity) VALUES(?,?,?)";
    $ins_stmt = mysqli_prepare($ocon, $insert_sql);
    if ($ins_stmt === false) {
        $_SESSION['cart_msg'] = "Lỗi prepare insert: " . mysqli_error($ocon);
        header("Location: $redirect");
        exit();
    }
    mysqli_stmt_bind_param($ins_stmt, "iii", $cart_id, $book_id, $quantity);
    mysqli_stmt_execute($ins_stmt);
    mysqli_stmt_close($ins_stmt);

    $_SESSION['cart_msg'] = "Đã thêm vào giỏ hàng!";
}

mysqli_stmt_close($stmt);
session_start();

$_SESSION['cart_msg'] = "Đã thêm sản phẩm vào giỏ!";
header("Location: $redirect"); 
exit();

