<?php
session_start();
include 'connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Chưa đăng nhập']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cat_id = intval($_POST['category_id']);

   
    $check = $ocon->query("SELECT COUNT(*) as total FROM books WHERE category_id = $cat_id");
    $row = $check->fetch_assoc();
    if($row['total'] > 0){
         echo json_encode(['success' => false, 'error' => 'Không thể xóa! Danh mục này đang chứa sách.']);
         exit;
    }


    // Thực hiện xóa
    $stmt = $ocon->prepare("DELETE FROM categories WHERE category_id = ?");
    $stmt->bind_param("i", $cat_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Lỗi SQL: ' . $ocon->error]);
    }
}
?>