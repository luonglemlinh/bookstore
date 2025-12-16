<?php
session_start();
include 'connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Chưa đăng nhập']);
    exit;
}

try {
    $id = intval($_POST['category_id']);
    $name = trim($_POST['name'] ?? '');
    $parent_id = !empty($_POST['parent_id']) ? intval($_POST['parent_id']) : NULL;

    if(empty($name)){
        throw new Exception("Tên danh mục không được để trống.");
    }
    
    // Kiểm tra ID hợp lệ
    if($id <= 0) throw new Exception("ID danh mục không hợp lệ.");

    // Kiểm tra trùng tên (nhưng trừ chính nó ra)
    $check = $ocon->prepare("SELECT category_id FROM categories WHERE name = ? AND category_id != ?");
    $check->bind_param("si", $name, $id);
    $check->execute();
    if($check->get_result()->num_rows > 0){
        throw new Exception("Tên danh mục này đã tồn tại.");
    }
    $check->close();

    // Cập nhật
    $stmt = $ocon->prepare("UPDATE categories SET name = ?, parent_id = ? WHERE category_id = ?");
    $stmt->bind_param("sii", $name, $parent_id, $id);
    
    if($stmt->execute()){
        echo json_encode(['success' => true]);
    } else {
        throw new Exception("Lỗi Update: " . $stmt->error);
    }
    $stmt->close();

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>