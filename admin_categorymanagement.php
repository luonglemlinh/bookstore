<?php
session_start();
include 'connect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
// Nếu không phải ADMIN → chặn
if ($_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}
$admin_name = $_SESSION['username'] ?? "Admin";

// --- XỬ LÝ TÌM KIẾM ---
$search = $_GET['search'] ?? '';

// --- PHÂN TRANG ---
$limit = 20;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// --- WHERE CLAUSE ---
$where = "WHERE 1";
if(!empty($search)){
    $search_safe = $ocon->real_escape_string($search);
    $where .= " AND (c.name LIKE '%$search_safe%')";
}

// --- TÍNH TỔNG SỐ DANH MỤC (ĐỂ PHÂN TRANG) ---
$total_sql = "SELECT COUNT(*) AS total FROM categories c $where";
$totalResult = $ocon->query($total_sql);
$totalRow = $totalResult->fetch_assoc();
$totalCategories = $totalRow['total'];
$totalPages = ceil($totalCategories / $limit);

// --- LẤY DỮ LIỆU HIỂN THỊ ---
// Kỹ thuật: Join với bảng books để đếm số sách, Join với chính nó (categories p) để lấy tên danh mục cha
$sql = "SELECT c.*, 
               p.name AS parent_name,
               COUNT(b.book_id) AS book_count
        FROM categories c
        LEFT JOIN categories p ON c.parent_id = p.category_id
        LEFT JOIN books b ON c.category_id = b.category_id
        $where
        GROUP BY c.category_id
        ORDER BY c.category_id ASC
        LIMIT $limit OFFSET $offset";

$result = $ocon->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý danh mục | Babo Bookstore</title>
    <link rel="stylesheet" href="CSS/admin.css">
    <link rel="stylesheet" href="CSS/book_management.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'admin_header.php'; ?>
<?php include 'admin_nav.php'; ?>

<main class="book-management">
    <h2>Quản lý danh mục sách</h2>

    <div class="book-actions">
        <a href="add_category.php" class="btn-create"><i class="fa fa-plus"></i> Tạo danh mục</a>
    </div>

    <form class="book-filter-form" method="get" action="">
        <input type="text" name="search" placeholder="Nhập tên danh mục..." value="<?= htmlspecialchars($search) ?>" style="flex: 1;">
        
        <?php if(!empty($search)): ?>
            <a href="category_management.php" class="btn-reset" style="padding: 10px; background:#ccc; color:#333; text-decoration:none; border-radius:4px;">Xóa lọc</a>
        <?php endif; ?>

        <button type="submit"><i class="fa fa-search"></i> Tìm kiếm</button>
    </form>

    <div class="book-list">
        <table>
            <thead>
                <tr>
                    <th style="width: 50px;">ID</th>
                    <th>Tên danh mục</th>
                    <th>Danh mục cha</th>
                    <th style="text-align: center;">Số lượng sách</th>
                    <th style="width: 150px;">Hành động</th>
                </tr>
            </thead>
            <tbody>
            <?php 
            if($result->num_rows > 0){
                while($row = $result->fetch_assoc()){
                    // Kiểm tra nếu danh mục có sách thì cảnh báo khi xóa
                    $hasBooks = $row['book_count'] > 0;
            ?>
                <tr id="cat-<?= $row['category_id'] ?>">
                    <td style="text-align:center;"><?= $row['category_id'] ?></td>
                    
                    <td>
                        <a class="book-link" href="edit_category.php?id=<?= $row['category_id'] ?>">
                            <?= htmlspecialchars($row['name']) ?>
                        </a>
                    </td>

                    <td style="color: #666; font-style: italic;">
                        <?= $row['parent_name'] ? htmlspecialchars($row['parent_name']) : '---' ?>
                    </td>

                    <td style="text-align:center;">
                        <?php if($hasBooks): ?>
                            <span style="background: #e7f1ff; color: #0d6efd; padding: 2px 8px; border-radius: 10px; font-size: 0.9em; font-weight: bold;">
                                <?= $row['book_count'] ?> cuốn
                            </span>
                        <?php else: ?>
                            <span style="color: #999;">Trống</span>
                        <?php endif; ?>
                    </td>

                    <td class="action-buttons">
                        <a href="edit_category.php?id=<?= $row['category_id'] ?>" class="btn-edit" title="Sửa">
                            <i class="fa fa-edit"></i>
                        </a>
                        
                        <a href="javascript:void(0);" 
                           class="btn-delete" 
                           data-id="<?= $row['category_id'] ?>"
                           data-count="<?= $row['book_count'] ?>"
                           title="Xóa">
                            <i class="fa fa-trash"></i>
                        </a>
                    </td>
                </tr>
            <?php 
                }
            } else {
                echo "<tr><td colspan='5' style='text-align:center; padding: 20px;'>Không tìm thấy danh mục nào</td></tr>";
            }
            ?>
            </tbody>
        </table>

        <?php if($totalPages > 1): ?>
        <div class="pagination">
            <?php for($i=1; $i<=$totalPages; $i++): ?>
                <?php
                $query = $_GET;
                $query['page'] = $i;
                $url = "?".http_build_query($query);
                ?>
                <a href="<?= $url ?>" class="<?= ($i==$page)?'active':'' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>

    </div>
</main>

<script>
document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', function(){
        let catId = this.getAttribute('data-id');
        let bookCount = parseInt(this.getAttribute('data-count'));
        
        let confirmMsg = 'Bạn có chắc muốn xóa danh mục này?';
        
        // Cảnh báo kỹ hơn nếu danh mục đang chứa sách
        if(bookCount > 0){
            confirmMsg = `CẢNH BÁO: Danh mục này đang chứa ${bookCount} cuốn sách.\nNếu xóa, các sách này sẽ mất danh mục!\n\nBạn vẫn muốn tiếp tục xóa?`;
        }

        if(confirm(confirmMsg)){
            fetch('delete_category.php', {
                method: 'POST',
                headers: {'Content-Type':'application/x-www-form-urlencoded'},
                body: 'category_id=' + catId
            })
            .then(res => res.json())
            .then(data => {
                if(data.success){
                    document.getElementById('cat-' + catId).remove();
                    alert('Xóa danh mục thành công!');
                } else {
                    alert('Lỗi: ' + data.error);
                }
            })
            .catch(err => {
                console.error(err);
                alert('Lỗi kết nối server.');
            });
        }
    });
});
</script>

</body>
</html>