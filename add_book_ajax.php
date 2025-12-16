<?php
session_start();
include 'connect.php';
header('Content-Type: application/json');

// Tắt báo lỗi hiển thị ra màn hình để tránh làm hỏng JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Chưa đăng nhập']);
    exit;
}

try {
    // --- 1. LẤY DỮ LIỆU TỪ FORM ---
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    // Các trường số (ép kiểu để đảm bảo an toàn)
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
    $status = 'active'; 

    // --- 2. BẮT ĐẦU KIỂM TRA LỖI (VALIDATION) ---
    $errors = [];

    // Kiểm tra các trường bắt buộc nhập
    if(empty($title)) $errors[] = "Tên sách không được để trống.";
    if($category_id <= 0 && empty($new_category)) $errors[] = "Vui lòng chọn hoặc thêm thể loại.";
    if($author_id <= 0 && empty($new_author_name)) $errors[] = "Vui lòng chọn hoặc thêm tác giả.";

    // KIỂM TRA: Các chỉ số phải lớn hơn 0
    if ($price <= 0) $errors[] = "Giá gốc phải lớn hơn 0.";
    if ($discounted_price <= 0) $errors[] = "Giá giảm phải lớn hơn 0.";
    if ($pages <= 0) $errors[] = "Số trang phải lớn hơn 0.";
    if ($weight <= 0) $errors[] = "Trọng lượng phải lớn hơn 0.";
    
    // Nếu bạn muốn kiểm tra cả số lượng tồn kho > 0 thì bỏ comment dòng dưới:
    // if ($stock_quantity <= 0) $errors[] = "Số lượng tồn kho phải lớn hơn 0.";

    // KIỂM TRA: Logic giá (Giá gốc phải lớn hơn giá giảm)
    // Chỉ kiểm tra khi cả 2 giá đều đã > 0 (để tránh báo lỗi chồng chéo)
    if ($price > 0 && $discounted_price > 0 && $discounted_price >= $price) {
        $errors[] = "Giá giảm ($discounted_price) phải nhỏ hơn giá gốc ($price).";
    }

    // KIỂM TRA: Năm xuất bản (Không được quá năm hiện tại)
    $current_year = (int)date('Y');
    if ($publish_year > $current_year) {
        $errors[] = "Năm xuất bản ($publish_year) không được vượt quá năm hiện tại ($current_year).";
    }

    // --- NẾU CÓ LỖI THÌ DỪNG NGAY ---
    if(!empty($errors)){
        echo json_encode(['success'=>false, 'error'=>implode(', ', $errors)]);
        exit;
    }

    // --- 3. XỬ LÝ DỮ LIỆU VÀO DB ---

    // Thêm category mới nếu có
    if(!empty($new_category)){
        $stmt = $ocon->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->bind_param("s", $new_category);
        $stmt->execute();
        $category_id = $stmt->insert_id;
        $stmt->close();
    }

    // Thêm author mới nếu có
    if(!empty($new_author_name)){
        $stmt = $ocon->prepare("INSERT INTO authors (name) VALUES (?)");
        $stmt->bind_param("s", $new_author_name);
        $stmt->execute();
        $author_id = $stmt->insert_id;
        $stmt->close();
    }

    // Thêm publisher mới nếu có
    if(!empty($new_publisher)){
        $stmt = $ocon->prepare("INSERT INTO publishers (name) VALUES (?)");
        $stmt->bind_param("s", $new_publisher);
        $stmt->execute();
        $publisher_id = $stmt->insert_id;
        $stmt->close();
    }

    // Xử lý ảnh cover upload
    $cover_path = '';
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
            $cover_path = $dest_path;
        }
    }

    // Tạo slug nếu trống
    if(empty($slug)){
        $slug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $title));
    }

    // Thêm sách vào DB
    $stmt = $ocon->prepare("INSERT INTO books (title, slug, description, publish_year, pages, weight, cover_type, price, discounted_price, category_id, stock_quantity, status, publisher_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param(
        "sssiiisddiisi", 
        $title, $slug, $description, $publish_year, $pages, $weight, $cover_type, $price, $discounted_price, $category_id, $stock_quantity, $status, $publisher_id
    );
    
    if(!$stmt->execute()){
        throw new Exception("Lỗi SQL: " . $stmt->error);
    }
    $book_id = $stmt->insert_id;
    $stmt->close();

    // Thêm quan hệ sách-tác giả
    if($author_id > 0){
        $stmt2 = $ocon->prepare("INSERT INTO book_authors (book_id, author_id) VALUES (?, ?)");
        $stmt2->bind_param("ii", $book_id, $author_id);
        $stmt2->execute();
        $stmt2->close();
    }

    // Thêm ảnh vào bảng images nếu có
    if(!empty($cover_path)){
        $stmt_img = $ocon->prepare("INSERT INTO images (book_id, url) VALUES (?, ?)");
        $stmt_img->bind_param("is", $book_id, $cover_path);
        $stmt_img->execute();
        $stmt_img->close();
    }

    // Lấy tên category và author để trả về frontend
    $cat_res = $ocon->query("SELECT name FROM categories WHERE category_id=$category_id");
    $cat_name = ($cat_res && $cat_res->num_rows > 0) ? $cat_res->fetch_assoc()['name'] : '';

    $auth_res = $ocon->query("SELECT name FROM authors WHERE author_id=$author_id");
    $author_name = ($auth_res && $auth_res->num_rows > 0) ? $auth_res->fetch_assoc()['name'] : '';

    echo json_encode([
        'success' => true,
        'book' => [
            'id' => $book_id,
            'title' => $title,
            'price' => $discounted_price,
            'category_id' => $category_id,
            'cover_path' => $cover_path,
            'category_name' => $cat_name,
            'author_name' => $author_name,
            'status' => $status
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>