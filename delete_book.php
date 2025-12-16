<?php
session_start();
include 'connect.php';

// Kiểm tra quyền
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success'=>false, 'error'=>'Chưa đăng nhập']);
    exit();
}

// Lấy book_id từ POST
$book_id = intval($_POST['book_id'] ?? 0);

if($book_id > 0){
    // Nếu có bảng liên kết, xóa bảng liên kết trước
    $ocon->query("DELETE FROM book_authors WHERE book_id = $book_id");

    if($ocon->query("DELETE FROM books WHERE book_id = $book_id")){
        echo json_encode(['success'=>true]);
    } else {
        echo json_encode(['success'=>false, 'error'=>$ocon->error]);
    }
} else {
    echo json_encode(['success'=>false, 'error'=>'ID sách không hợp lệ']);
}
?>
