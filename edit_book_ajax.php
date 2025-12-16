<?php
session_start();
include 'connect.php';
header('Content-Type: application/json');

// Tắt báo lỗi hiển thị để tránh hỏng JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Chưa đăng nhập']);
    exit;
}

try {
    // 1. Lấy và kiểm tra ID sách
    $book_id = intval($_POST['book_id'] ?? 0);
    if ($book_id <= 0) {
        throw new Exception("ID sách không hợp lệ.");
    }

    // 2. Lấy dữ liệu từ form
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    // Các trường số
    $publish_year = intval($_POST['publish_year'] ?? 0);
    $pages = intval($_POST['pages'] ?? 0);
    $weight = intval($_POST['weight'] ?? 0);
    $stock_quantity = intval($_POST['stock_quantity'] ?? 0);
    $price = floatval($_POST['price'] ?? 0);
    $discounted_price = floatval($_POST['discounted_price'] ?? 0);
    
    $cover_type = $_POST['cover_type'] ?? 'hard';
    $category_id = intval($_POST['category'] ?? 0);
    $author_id = intval($_POST['author'] ?? 0);
    $publisher_id = intval($_POST['publisher'] ?? 0);
    
    $new_category = trim($_POST['new_category'] ?? '');
    $new_author_name = trim($_POST['new_author'] ?? '');
    $new_publisher = trim($_POST['new_publisher'] ?? '');
    $status = $_POST['status'] ?? 'active';

    // --- BẮT ĐẦU VALIDATION (KIỂM TRA DỮ LIỆU) ---
    $errors = [];

    // Kiểm tra cơ bản
    if(empty($title)) $errors[] = "Tên sách không được để trống.";
    if($category_id <= 0 && empty($new_category)) $errors[] = "Vui lòng chọn hoặc thêm thể loại.";
    if($author_id <= 0 && empty($new_author_name)) $errors[] = "Vui lòng chọn hoặc thêm tác giả.";

    // KIỂM TRA: Các chỉ số phải lớn hơn 0
    if ($price <= 0) $errors[] = "Giá gốc phải lớn hơn 0.";
    if ($discounted_price <= 0) $errors[] = "Giá giảm phải lớn hơn 0.";
    if ($pages <= 0) $errors[] = "Số trang phải lớn hơn 0.";
    if ($weight <= 0) $errors[] = "Trọng lượng phải lớn hơn 0.";
    
    // Lưu ý: Nếu muốn cho phép hết hàng (tồn kho = 0) thì bỏ dòng dưới đi
    if ($stock_quantity < 0) $errors[] = "Số lượng tồn kho không được âm.";

    // KIỂM TRA: Logic giá (Giá gốc phải lớn hơn giá giảm)
    if ($price > 0 && $discounted_price > 0 && $discounted_price >= $price) {
        $errors[] = "Giá giảm ($discounted_price) phải nhỏ hơn giá gốc ($price).";
    }

    // KIỂM TRA: Năm xuất bản (Không được quá năm hiện tại)
    $current_year = (int)date('Y');
    if ($publish_year > $current_year) {
        $errors[] = "Năm xuất bản ($publish_year) không được vượt quá năm hiện tại ($current_year).";
    }

    // Nếu có lỗi, ném ra Exception để dừng chương trình
    if(!empty($errors)){
        throw new Exception(implode(', ', $errors));
    }
    // --- KẾT THÚC VALIDATION ---


    // 3. Xử lý thêm mới Category/Author/Publisher (nếu có nhập ô input text)
    
    // Category mới
    if(!empty($new_category)){
        $stmt = $ocon->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->bind_param("s", $new_category);
        $stmt->execute();
        $category_id = $stmt->insert_id;
        $stmt->close();
    }

    // Author mới
    if(!empty($new_author_name)){
        $stmt = $ocon->prepare("INSERT INTO authors (name) VALUES (?)");
        $stmt->bind_param("s", $new_author_name);
        $stmt->execute();
        $author_id = $stmt->insert_id;
        $stmt->close();
    }

    // Publisher mới
    if(!empty($new_publisher)){
        $stmt = $ocon->prepare("INSERT INTO publishers (name) VALUES (?)");
        $stmt->bind_param("s", $new_publisher);
        $stmt->execute();
        $publisher_id = $stmt->insert_id;
        $stmt->close();
    }

    // 4. Xử lý Slug
    if(empty($slug)){
        $slug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $title));
    }

    // 5. CẬP NHẬT BẢNG BOOKS
    $sql = "UPDATE books SET 
            title=?, slug=?, description=?, publish_year=?, pages=?, weight=?, 
            cover_type=?, price=?, discounted_price=?, category_id=?, 
            stock_quantity=?, status=?, publisher_id=? 
            WHERE book_id=?";
    
    $stmt = $ocon->prepare($sql);
    // Bind param: sssiiisddiisii (Lưu ý chữ cái cuối cùng là 'i' cho book_id)
    $stmt->bind_param(
        "sssiiisddiisii", 
        $title, $slug, $description, $publish_year, $pages, $weight, 
        $cover_type, $price, $discounted_price, $category_id, 
        $stock_quantity, $status, $publisher_id, $book_id
    );
    
    if(!$stmt->execute()){
        throw new Exception("Lỗi Update sách: " . $stmt->error);
    }
    $stmt->close();

    // 6. CẬP NHẬT TÁC GIẢ (Bảng book_authors)
    // Chiến thuật: Xóa liên kết cũ -> Thêm liên kết mới
    if($author_id > 0){
        // Xóa cũ
        $ocon->query("DELETE FROM book_authors WHERE book_id = $book_id");
        
        // Thêm mới
        $stmt_auth = $ocon->prepare("INSERT INTO book_authors (book_id, author_id) VALUES (?, ?)");
        $stmt_auth->bind_param("ii", $book_id, $author_id);
        $stmt_auth->execute();
        $stmt_auth->close();
    }

    // 7. XỬ LÝ ẢNH (Chỉ thực hiện nếu user có chọn file mới)
    if(isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK){
        $fileTmpPath = $_FILES['cover']['tmp_name'];
        $fileName = basename($_FILES['cover']['name']);
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
        $newFileName = uniqid('book_') . '.' . $ext;
        
        $uploadFileDir = 'images/';
        if (!file_exists($uploadFileDir)) {
            mkdir($uploadFileDir, 0777, true);
        }
        $dest_path = $uploadFileDir . $newFileName;

        if(move_uploaded_file($fileTmpPath, $dest_path)){
            // Kiểm tra xem sách đã có ảnh trong bảng images chưa
            $check_img = $ocon->query("SELECT image_id FROM images WHERE book_id = $book_id LIMIT 1");
            
            if($check_img->num_rows > 0){
                // Nếu có rồi -> Update
                $stmt_img = $ocon->prepare("UPDATE images SET url = ? WHERE book_id = ?");
                $stmt_img->bind_param("si", $dest_path, $book_id);
                $stmt_img->execute();
                $stmt_img->close();
            } else {
                // Nếu chưa có -> Insert
                $stmt_img = $ocon->prepare("INSERT INTO images (book_id, url) VALUES (?, ?)");
                $stmt_img->bind_param("is", $book_id, $dest_path);
                $stmt_img->execute();
                $stmt_img->close();
            }
        }
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>