<?php
session_start();
include 'connect.php';

// Nếu chưa đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Nếu không phải ADMIN → chặn
if ($_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Gán tên admin
$admin_name = $_SESSION['username'] ?? "Admin";

// Lấy danh sách category & author để tạo filter
$categories = $ocon->query("SELECT * FROM categories");
$authors = $ocon->query("SELECT * FROM authors");

// Lấy dữ liệu tìm kiếm và filter
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';
$author_filter = $_GET['author'] ?? '';

// Phân trang
$limit = 20;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Build WHERE clause
$where = "WHERE 1";
if(!empty($search)){
    $search_safe = $ocon->real_escape_string($search);
    $where .= " AND (b.book_id LIKE '%$search_safe%' OR b.title LIKE '%$search_safe%' OR a.name LIKE '%$search_safe%')";
}
if(!empty($category_filter)){
    $category_safe = intval($category_filter);
    $where .= " AND b.category_id = $category_safe";
}
if(!empty($author_filter)){
    $author_safe = intval($author_filter);
    $where .= " AND ba.author_id = $author_safe";
}

// Tổng số sách sau filter (để phân trang)
$total_sql = "SELECT COUNT(DISTINCT b.book_id) AS total
              FROM books b
              LEFT JOIN book_authors ba ON b.book_id = ba.book_id
              LEFT JOIN authors a ON ba.author_id = a.author_id
              $where";
$totalResult = $ocon->query($total_sql);
$totalRow = $totalResult->fetch_assoc();
$totalBooks = $totalRow['total'];
$totalPages = ceil($totalBooks / $limit);

// Lấy dữ liệu sách với filter + phân trang
$sql = "SELECT b.book_id, b.title, b.stock_quantity, b.created_at, c.name AS category_name,
        GROUP_CONCAT(a.name SEPARATOR ', ') AS authors
        FROM books b
        LEFT JOIN categories c ON b.category_id = c.category_id
        LEFT JOIN book_authors ba ON b.book_id = ba.book_id
        LEFT JOIN authors a ON ba.author_id = a.author_id
        $where
        GROUP BY b.book_id
        ORDER BY b.created_at DESC
        LIMIT $limit OFFSET $offset";

$result = $ocon->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | Babo Bookstore</title>
    <link rel="stylesheet" href="CSS/admin.css">
    <link rel="stylesheet" href="CSS/book_management.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'admin_header.php'; ?>
<?php include 'admin_nav.php'; ?>

<main class="book-management">
<h2>Quản lý sách</h2>

<div class="book-actions">
    <a href="add_book.php" class="btn-create"><i class="fa fa-plus"></i> Tạo sản phẩm</a>
</div>

<!-- Form tìm kiếm + filter ngay dưới tiêu đề -->
<form class="book-filter-form" method="get" action="">
    <input type="text" name="search" placeholder="Tìm kiếm theo mã/tên sách/tác giả" value="<?= htmlspecialchars($search) ?>">

    <select name="category">
        <option value="">Tất cả thể loại</option>
        <?php while($cat = $categories->fetch_assoc()): ?>
            <option value="<?= $cat['category_id'] ?>" <?= $category_filter == $cat['category_id'] ? 'selected' : '' ?>>
                <?= $cat['name'] ?>
            </option>
        <?php endwhile; ?>
    </select>

    <select name="author">
        <option value="">Tất cả tác giả</option>
        <?php while($auth = $authors->fetch_assoc()): ?>
            <option value="<?= $auth['author_id'] ?>" <?= $author_filter == $auth['author_id'] ? 'selected' : '' ?>>
                <?= $auth['name'] ?>
            </option>
        <?php endwhile; ?>
    </select>

    <button type="submit"><i class="fa fa-search"></i> Tìm</button>
</form>

<div class="book-list">
<table>
    <thead>
        <tr>
            <th>STT</th>
            <th>Tên sách</th>
            <th>Danh mục</th>
            <th>Tác giả</th>
            <th>Số lượng</th>
            <th>Ngày tạo</th>
            <th>Hành động</th>
        </tr>
    </thead>
    <tbody>
<?php 
if($result->num_rows > 0){
    $stt = $offset + 1;
    while($row = $result->fetch_assoc()){
        echo "<tr id='book-".$row['book_id']."'>";
        echo "<td style='text-align:center;'>".$stt++."</td>";
        echo "<td><a class='book-link' href='edit_book.php?book_id=".$row['book_id']."'>".$row['title']."</a></td>";
        echo "<td>".$row['category_name']."</td>";
        echo "<td>".$row['authors']."</td>";
        echo "<td style='text-align:center;'>".$row['stock_quantity']."</td>";
        echo "<td style='text-align:center;'>".$row['created_at']."</td>";
        echo "<td class='action-buttons'>";
        echo "<a href='edit_book.php?book_id=".$row['book_id']."' class='btn-edit'><i class='fa fa-edit'></i></a>";
        echo "<a href='javascript:void(0);' data-id='".$row['book_id']."' class='btn-delete'><i class='fa fa-trash'></i></a>";
        echo "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='7' style='text-align:center;'>Không tìm thấy sách nào</td></tr>";
}
?>
    </tbody>
</table>


<!-- Pagination -->
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

</div>
</main>
<script>
document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', function(){
        let bookId = this.getAttribute('data-id');
        if(confirm('Bạn có chắc muốn xóa sách này?')){
            fetch('delete_book.php', {
                method: 'POST',
                headers: {'Content-Type':'application/x-www-form-urlencoded'},
                body: 'book_id='+bookId
            })
            .then(res => res.json())
            .then(data => {
                if(data.success){
                    document.getElementById('book-'+bookId).remove();
                    alert('Xóa sách thành công!');
                } else {
                    alert('Lỗi: '+data.error);
                }
            })
            .catch(err => console.error(err));
        }
    });
});

</script>

</body>
</html>
