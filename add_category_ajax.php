<?php
session_start();
include 'connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Chưa đăng nhập']);
    exit;
}

try {
    $name = trim($_POST['name'] ?? '');
    $parent_id = !empty($_POST['parent_id']) ? intval($_POST['parent_id']) : NULL;

    if(empty($name)){
        throw new Exception("Tên danh mục không được để trống.");
    }

    // Kiểm tra trùng tên
    $check = $ocon->prepare("SELECT category_id FROM categories WHERE name = ?");
    $check->bind_param("s", $name);
    $check->execute();
    if($check->get_result()->num_rows > 0){
        throw new Exception("Tên danh mục đã tồn tại.");
    }
    $check->close();

    // Thêm mới
    $stmt = $ocon->prepare("INSERT INTO categories (name, parent_id) VALUES (?, ?)");
    $stmt->bind_param("si", $name, $parent_id);
    
    if($stmt->execute()){
        echo json_encode(['success' => true]);
    } else {
        throw new Exception("Lỗi SQL: " . $stmt->error);
    }
    $stmt->close();

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>