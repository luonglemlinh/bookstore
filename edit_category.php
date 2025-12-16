<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$admin_name = $_SESSION['username'] ?? "Admin";
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Lấy thông tin danh mục hiện tại
$stmt = $ocon->prepare("SELECT * FROM categories WHERE category_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$current_cat = $stmt->get_result()->fetch_assoc();
$stmt->close();

if(!$current_cat){
    die("Không tìm thấy danh mục!");
}

// Lấy danh sách các danh mục khác (để chọn làm cha)
// TRỪ chính nó ra (không thể tự làm cha của mình)
$cats = $ocon->query("SELECT * FROM categories WHERE category_id != $id");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa danh mục | Babo Bookstore</title>
    <link rel="stylesheet" href="CSS/admin.css">
    <link rel="stylesheet" href="CSS/add_book.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        #add-book-form { max-width: 600px; display: block; }
        .form-group { margin-bottom: 15px; }
    </style>
</head>
<body>
<?php include 'admin_nav.php'; ?>

<main class="book-management">
    <h2 style="
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #16515fff;">Sửa danh mục: <?= htmlspecialchars($current_cat['name']) ?></h2>

    <form id="add-book-form"> <input type="hidden" name="category_id" value="<?= $current_cat['category_id'] ?>">
        
        <div class="form-group">
            <label>Tên danh mục:</label>
            <input type="text" name="name" value="<?= htmlspecialchars($current_cat['name']) ?>" required>
        </div>

        <div class="form-group">
            <label>Danh mục cha:</label>
            <select name="parent_id">
                <option value="">-- Không có (Là danh mục gốc) --</option>
                <?php while($c = $cats->fetch_assoc()): ?>
                    <option value="<?= $c['category_id'] ?>" <?= $current_cat['parent_id'] == $c['category_id'] ? 'selected' : '' ?>>
                        <?= $c['name'] ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <button type="submit" class="btn-submit"><i class="fa fa-save"></i> Cập nhật</button>
        <a href="admin_categorymanagement.php" class="btn-back" style="text-decoration:none; color:#333; margin-top: 10px;">Quay lại</a>
    </form>
</main>

<script>
document.getElementById('add-book-form').addEventListener('submit', function(e){
    e.preventDefault();
    let formData = new FormData(this);

    fetch('edit_category_ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.success){
            alert('Cập nhật thành công!');
            window.location.href = 'admin_categorymanagement.php';
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