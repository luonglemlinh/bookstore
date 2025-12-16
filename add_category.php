<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$admin_name = $_SESSION['username'] ?? "Admin";
// Lấy danh sách các danh mục để chọn làm danh mục cha (nếu có)
$cats = $ocon->query("SELECT * FROM categories");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm danh mục | Babo Bookstore</title>
    <link rel="stylesheet" href="CSS/admin.css">
    <link rel="stylesheet" href="CSS/add_book.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Tinh chỉnh lại một chút cho form ngắn gọn hơn */
        #add-book-form { max-width: 600px; display: block; }
        .form-group { margin-bottom: 15px; }
    </style>
</head>
<body>
<?php include 'admin_nav.php'; ?>

<main class="book-management">
    <div class="" style="
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #16515fff;">
        <h2>Thêm danh mục mới</h2>
    </div>

    <form id="add-book-form">
        
        <div class="form-group">
            <label>Tên danh mục:</label>
            <input type="text" name="name" placeholder="Ví dụ: Tiểu thuyết, Kinh tế..." required>
        </div>

        <div class="form-group">
            <label>Danh mục cha (Tùy chọn):</label>
            <select name="parent_id">
                <option value="">-- Không có (Là danh mục gốc) --</option>
                <?php while($c = $cats->fetch_assoc()): ?>
                    <option value="<?= $c['category_id'] ?>"><?= $c['name'] ?></option>
                <?php endwhile; ?>
            </select>
            <p style="font-size: 0.85em; color: #666; margin-top: 5px;">Chọn danh mục cha nếu đây là danh mục con (VD: Chọn 'Văn học' cho 'Tiểu thuyết').</p>
        </div>

        <button type="submit" class="btn-submit"><i class="fa fa-plus"></i> Thêm danh mục</button>
        <a href="admin_categorymanagement.php" class="btn-back" style="text-decoration:none; color:#333; margin-top: 10px;">Hủy bỏ</a>
    </form>
</main>

<script>
document.getElementById('add-book-form').addEventListener('submit', function(e){
    e.preventDefault();
    let formData = new FormData(this);

    fetch('add_category_ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.success){
            alert('Thêm danh mục thành công!');
            window.location.href = 'admin_categorymanagement.php'; // Quay về trang danh sách
        } else {
            alert('Lỗi: ' + data.error);
        }
    })
    .catch(err => {
        console.error(err);
        alert('Lỗi kết nối server.');
    });
});
</script>

</body>
</html>