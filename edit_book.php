<?php
session_start();
include 'connect.php';

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$admin_name = $_SESSION['username'] ?? "Admin";

// 2. Lấy ID sách cần sửa
$book_id = isset($_GET['id']) ? intval($_GET['id']) : 0; 
if ($book_id == 0 && isset($_GET['book_id'])) {
    $book_id = intval($_GET['book_id']);
}

// 3. Lấy thông tin chi tiết của sách (Kèm ảnh và danh sách ID tác giả)
$sql = "SELECT b.*, 
        (SELECT url FROM images WHERE book_id = b.book_id LIMIT 1) AS image_url,
        (SELECT GROUP_CONCAT(author_id) FROM book_authors WHERE book_id = b.book_id) AS author_ids
        FROM books b 
        WHERE b.book_id = ?";

$stmt = $ocon->prepare($sql);
$stmt->bind_param("i", $book_id);
$stmt->execute();
$result = $stmt->get_result();
$book = $result->fetch_assoc();
$stmt->close();

if (!$book) {
    die("Không tìm thấy sách hoặc sách đã bị xóa.");
}

// 4. Lấy dữ liệu cho các ô chọn (Dropdown)
$categories = $ocon->query("SELECT * FROM categories");
$authors    = $ocon->query("SELECT * FROM authors");
$publishers = $ocon->query("SELECT * FROM publishers");

// Chuyển danh sách ID tác giả thành mảng
$current_author_ids = !empty($book['author_ids']) ? explode(",", $book['author_ids']) : [];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa sách: <?= htmlspecialchars($book['title']) ?> | Babo Bookstore</title>
    <link rel="stylesheet" href="CSS/admin.css">
    <link rel="stylesheet" href="CSS/edit_book.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<?php include 'admin_nav.php'; ?>

<main class="book-management">
    <div class="header-container" style="display:flex; justify-content:space-between; align-items:center;">
        <h2>Sửa thông tin sách</h2>
        <a href="admin_bookmanagement.php" class="btn-back" style="text-decoration:none; color:#333;"><i class="fa fa-arrow-left"></i> Quay lại</a>
    </div>

    <form id="edit-book-form" enctype="multipart/form-data">
        <input type="hidden" name="book_id" value="<?= $book['book_id'] ?>">

        <div class="form-group">
            <label>Tên sách:</label>
            <input type="text" name="title" value="<?= htmlspecialchars($book['title']) ?>" required>
        </div>

        <div class="form-group">
            <label>Slug (URL thân thiện - Tùy chọn):</label>
            <input type="text" name="slug" value="<?= htmlspecialchars($book['slug'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Mô tả:</label>
            <textarea name="description" rows="5"><?= htmlspecialchars($book['description']) ?></textarea>
        </div>

        <div class="form-row" style="display:flex; gap:20px;">
            <div style="flex:1">
                <label>Năm xuất bản:</label>
                <input type="number" name="publish_year" value="<?= $book['publish_year'] ?>" min="1000" max="<?= date('Y') ?>">
            </div>
            <div style="flex:1">
                <label>Số trang:</label>
                <input type="number" name="pages" value="<?= $book['pages'] ?>" min="1">
            </div>
            <div style="flex:1">
                <label>Trọng lượng (gram):</label>
                <input type="number" name="weight" value="<?= $book['weight'] ?>" min="1">
            </div>
        </div>

        <div class="form-group">
            <label>Loại bìa:</label>
            <select name="cover_type">
                <option value="hard" <?= $book['cover_type'] == 'hard' ? 'selected' : '' ?>>Bìa Cứng</option>
                <option value="soft" <?= $book['cover_type'] == 'soft' ? 'selected' : '' ?>>Bìa Mềm</option>
            </select>
        </div>

        <div class="form-row" style="display:flex; gap:20px;">
            <div style="flex:1">
                <label>Giá gốc (vnđ):</label>
                <input type="number" step="1000" name="price" value="<?= $book['price'] ?>" min="0">
            </div>
            <div style="flex:1">
                <label>Giá khuyến mãi (vnđ):</label>
                <input type="number" step="1000" name="discounted_price" value="<?= $book['discounted_price'] ?>" min="0">
            </div>
        </div>

        <div class="form-group">
            <label>Thể loại:</label>
            <select name="category">
                <option value="">-- Chọn thể loại --</option>
                <?php while ($cat = $categories->fetch_assoc()): ?>
                    <option value="<?= $cat['category_id'] ?>" <?= $book['category_id'] == $cat['category_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <input type="text" name="new_category" placeholder="Hoặc nhập tên thể loại mới để tạo nhanh" style="margin-top:5px; font-size:0.9em;">
        </div>

        <div class="form-group">
            <label>Nhà xuất bản:</label>
            <select name="publisher">
                <option value="">-- Chọn Nhà xuất bản --</option>
                <?php while ($pub = $publishers->fetch_assoc()): ?>
                    <option value="<?= $pub['publisher_id'] ?>" <?= $book['publisher_id'] == $pub['publisher_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($pub['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <input type="text" name="new_publisher" placeholder="Hoặc nhập NXB mới" style="margin-top:5px; font-size:0.9em;">
        </div>

        <div class="form-group">
            <label>Tác giả chính:</label>
            <select name="author">
                <option value="">-- Chọn Tác giả --</option>
                <?php while ($auth = $authors->fetch_assoc()): ?>
                    <option value="<?= $auth['author_id'] ?>" <?= in_array($auth['author_id'], $current_author_ids) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($auth['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <input type="text" name="new_author" placeholder="Hoặc nhập tên tác giả mới" style="margin-top:5px; font-size:0.9em;">
        </div>

        <div class="form-group">
            <label>Số lượng tồn kho:</label>
            <input type="number" name="stock_quantity" value="<?= $book['stock_quantity'] ?>" min="0">
        </div>

        <div class="form-group" style="background: #f9f9f9; padding: 15px; border-radius: 8px;">
            <label>Ảnh bìa sách:</label>
            
            <div style="margin-bottom: 10px;">
                <?php if (!empty($book['image_url'])): ?>
                    <p style="font-size: 0.9em; color: #666;">Ảnh hiện tại:</p>
                    <img src="<?= htmlspecialchars($book['image_url']) ?>" alt="Ảnh bìa" style="height: 150px; border: 1px solid #ddd; border-radius: 4px;">
                <?php else: ?>
                    <p style="color: #999; font-style: italic;">Chưa có ảnh bìa</p>
                <?php endif; ?>
            </div>

            <label for="cover_upload" style="cursor: pointer; display: inline-block; background: #ddd; padding: 5px 10px; border-radius: 4px;">
                <i class="fa fa-camera"></i> Chọn ảnh mới (nếu muốn thay đổi)
            </label>
            <input type="file" id="cover_upload" name="cover" accept="image/*" style="margin-top: 5px;">
        </div>

        <div class="form-group">
            <label>Trạng thái hiển thị:</label>
            <select name="status">
                <option value="active" <?= $book['status'] == 'active' ? 'selected' : '' ?>>Hiển thị (Active)</option>
                <option value="hidden" <?= $book['status'] == 'hidden' ? 'selected' : '' ?>>Ẩn (Hidden)</option>
            </select>
        </div>

        <button type="submit" class="btn-submit" style="margin-top: 20px;"><i class="fa fa-save"></i> Cập nhật sách</button>
    </form>
</main>

<script>
document.getElementById('edit-book-form').addEventListener('submit', function(e){
    e.preventDefault();
    
    // Hiệu ứng loading nút bấm
    const btn = this.querySelector('.btn-submit');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Đang lưu...';
    btn.disabled = true;

    let formData = new FormData(this);

    fetch('edit_book_ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.text()) // Dùng text() để debug nếu PHP lỗi
    .then(text => {
        try {
            const data = JSON.parse(text);
            if(data.success){
                alert('Cập nhật sách thành công!');
                // Load lại trang để thấy ảnh mới
                window.location.reload(); 
            } else {
                alert('Lỗi: ' + data.error);
            }
        } catch(e) {
            console.error("Lỗi PHP trả về không phải JSON:", text);
            alert("Lỗi server! Xem console để biết chi tiết.");
        }
    })
    .catch(err => {
        console.error(err);
        alert('Lỗi kết nối.');
    })
    .finally(() => {
        // Trả lại trạng thái nút bấm
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
});
</script>

</body>
</html>