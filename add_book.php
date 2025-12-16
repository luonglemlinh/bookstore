<?php
session_start();
include 'connect.php';

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Gán tên admin
$admin_name = $_SESSION['username'] ?? "Admin";
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm sách mới | Babo Bookstore</title>
    <link rel="stylesheet" href="CSS/admin.css">
    
    <link rel="stylesheet" href="CSS/add_book.css"> 
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<?php include 'admin_nav.php'; ?>

<main class="book-management">
    <div class="header-container" style="display:flex; justify-content:space-between; align-items:center;">
        <h2>Thêm sách mới</h2>
        <a href="admin_bookmanagement.php" class="btn-back" style="text-decoration:none; color:#333;"><i class="fa fa-arrow-left"></i> Quay lại danh sách</a>
    </div>

    <form id="add-book-form" enctype="multipart/form-data">
        
        <div class="form-group">
            <label>Tên sách:</label>
            <input type="text" name="title" required placeholder="Nhập tên sách...">
        </div>

        <div class="form-group">
            <label>Slug (Tùy chọn):</label>
            <input type="text" name="slug" placeholder="tự-động-tạo-nếu-để-trống">
        </div>

        <div class="form-group">
            <label>Mô tả:</label>
            <textarea name="description" rows="5" placeholder="Mô tả nội dung sách..."></textarea>
        </div>

        <div class="form-row" style="display:flex; gap:20px;">
            <div style="flex:1">
                <label>Năm xuất bản:</label>
                <input type="number" name="publish_year" value="<?= date('Y') ?>" min="1000" max="<?= date('Y') ?>">
            </div>
            <div style="flex:1">
                <label>Số trang:</label>
                <input type="number" name="pages" value="1" min="1">
            </div>
            <div style="flex:1">
                <label>Trọng lượng (gram):</label>
                <input type="number" name="weight" value="1" min="1">
            </div>
        </div>

        <div class="form-group">
            <label>Loại bìa:</label>
            <select name="cover_type">
                <option value="hard">Bìa Cứng</option>
                <option value="soft" selected>Bìa Mềm</option>
            </select>
        </div>

        <div class="form-row" style="display:flex; gap:20px;">
            <div style="flex:1">
                <label>Giá gốc (vnđ):</label>
                <input type="number" step="1000" name="price" value="0" min="0">
            </div>
            <div style="flex:1">
                <label>Giá khuyến mãi (vnđ):</label>
                <input type="number" step="1000" name="discounted_price" value="0" min="0">
            </div>
        </div>

        <div class="form-group">
            <label>Thể loại:</label>
            <select name="category">
                <option value="">-- Chọn thể loại --</option>
                <?php
                $res = $ocon->query("SELECT * FROM categories");
                while($cat = $res->fetch_assoc()){
                    echo '<option value="'.$cat['category_id'].'">'.htmlspecialchars($cat['name']).'</option>';
                }
                ?>
            </select>
            <input type="text" name="new_category" placeholder="Hoặc nhập tên thể loại mới để tạo nhanh" style="margin-top:5px; font-size:0.9em;">
        </div>

        <div class="form-group">
            <label>Nhà xuất bản:</label>
            <select name="publisher">
                <option value="">-- Chọn Nhà xuất bản --</option>
                <?php
                $res3 = $ocon->query("SELECT * FROM publishers");
                while($pub = $res3->fetch_assoc()){
                    echo '<option value="'.$pub['publisher_id'].'">'.htmlspecialchars($pub['name']).'</option>';
                }
                ?>
            </select>
            <input type="text" name="new_publisher" placeholder="Hoặc nhập NXB mới" style="margin-top:5px; font-size:0.9em;">
        </div>

        <div class="form-group">
            <label>Tác giả chính:</label>
            <select name="author">
                <option value="">-- Chọn Tác giả --</option>
                <?php
                $res2 = $ocon->query("SELECT * FROM authors");
                while($auth = $res2->fetch_assoc()){
                    echo '<option value="'.$auth['author_id'].'">'.htmlspecialchars($auth['name']).'</option>';
                }
                ?>
            </select>
            <input type="text" name="new_author" placeholder="Hoặc nhập tên tác giả mới" style="margin-top:5px; font-size:0.9em;">
        </div>

        <div class="form-group">
            <label>Số lượng tồn kho:</label>
            <input type="number" name="stock_quantity" value="0" min="0">
        </div>

        <div class="form-group" style="background: #f9f9f9; padding: 15px; border-radius: 8px;">
            <label>Ảnh bìa sách:</label>
            <input type="file" name="cover" accept="image/*" style="background: white;">
        </div>

        <div class="form-group">
            <label>Trạng thái hiển thị:</label>
            <select name="status">
                <option value="active">Hiển thị (Active)</option>
                <option value="hidden">Ẩn (Hidden)</option>
            </select>
        </div>

        <button type="submit" class="btn-submit" style="margin-top: 20px;"><i class="fa fa-plus"></i> Thêm sách mới</button>
    </form>

    <h3 style="margin-top: 40px; border-bottom: 2px solid #ddd; padding-bottom: 10px;">Vừa thêm gần đây</h3>
    <div id="book-list" style="margin-top: 15px;">
        </div>

</main>

<script>
document.getElementById('add-book-form').addEventListener('submit', function(e){
    e.preventDefault();
    
    // Hiệu ứng Loading cho nút bấm
    const btn = this.querySelector('.btn-submit');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Đang xử lý...';
    btn.disabled = true;

    let formData = new FormData(this);

    fetch('add_book_ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.text()) // Dùng text() để debug an toàn
    .then(text => {
        try {
            const data = JSON.parse(text);
            if(data.success){
                const book = data.book;

                // 1. Tạo HTML hiển thị sách vừa thêm bên dưới
                const list = document.getElementById('book-list');
                let htmlAdmin = `
                <div class="book-item" style="background:#fff; border:1px solid #ddd; padding:15px; margin-bottom:10px; display:flex; gap:15px; align-items:center; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                    <img src="${book.cover_path}" style="width:60px; height:85px; object-fit:cover; border-radius:4px; border:1px solid #eee;">
                    <div>
                        <h3 style="margin:0; font-size:18px; color:#16515fff;">${book.title}</h3>
                        <p style="margin:5px 0; font-weight:bold; color:#d32f2f;">${Number(book.price).toLocaleString('vi-VN')}₫</p>
                        <p style="margin:0; font-size:0.9em; color:#666;">
                            <span style="background:#eee; padding:2px 6px; border-radius:4px;">${book.category_name}</span> • 
                            <span>${book.author_name}</span>
                        </p>
                    </div>
                    <div style="margin-left:auto; color: green; font-weight:bold;">
                        <i class="fa fa-check-circle"></i> Mới thêm
                    </div>
                </div>`;
                
                // Thêm vào đầu danh sách
                list.insertAdjacentHTML('afterbegin', htmlAdmin);

                // 2. Thông báo & Reset form
                alert('Thêm sách thành công!');
                document.getElementById('add-book-form').reset();
                
                // Scroll xuống để thấy sách vừa thêm
                list.scrollIntoView({behavior: "smooth"});
            } else {
                alert('Lỗi: ' + data.error);
            }
        } catch(e) {
            console.error("Lỗi PHP:", text);
            alert("Lỗi server! Kiểm tra console.");
        }
    })
    .catch(err => {
        console.error(err);
        alert('Lỗi kết nối.');
    })
    .finally(() => {
        // Trả lại nút bấm
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
});
</script>

</body>
</html>