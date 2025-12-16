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

// Lấy trạng thái lọc
$status = $_GET['status'] ?? 'all';
// Lấy rating lọc
$rating = $_GET['rating'] ?? 'all';


// Build WHERE
$where = [];
if ($status !== 'all') {
    $where[] = "r.status = '$status'";
}
if ($rating !== 'all') {
    $where[] = "r.rating = $rating";
}

$whereSQL = '';
if (!empty($where)) {
    $whereSQL = 'WHERE ' . implode(' AND ', $where);
}


// Lấy bình luận
$sql = "
    SELECT r.*, u.full_name, b.title
    FROM reviews r
    JOIN users u ON r.user_id = u.user_id
    JOIN books b ON r.book_id = b.book_id
    $whereSQL
    ORDER BY r.review_id DESC
";
$result = mysqli_query($ocon, $sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Quản lý bình luận</title>

  <link rel="stylesheet" href="css/admin_comments.css">
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
<?php include 'admin_header.php'; ?>
<?php include 'admin_nav.php'; ?>

<div class="main-content">

  <h1>Quản lý bình luận</h1>

  <!-- Tabs lọc -->
  <div class="status-tabs">
    <?php
      $tabs = [
        'all'      => 'Tất cả',
        'pending'  => 'Chờ duyệt',
        'approved' => 'Đã duyệt',
        'hidden'   => 'Ẩn',
      ];

      foreach ($tabs as $key => $label) {
          $active = ($status === $key) ? 'active' : '';
          echo "<a class='tab-item $active' href='admin_comments.php?status=$key&rating=$rating'>$label</a>";
      }
    ?>
  </div>

  <div class="filter-rating">
    <label for="filter_rating">Lọc theo rating:</label>
    <select id="filter_rating">
    <option value="all" <?= $rating === 'all' ? 'selected' : '' ?>>Tất cả</option>
    <option value="5" <?= $rating === '5' ? 'selected' : '' ?>>5 ⭐</option>
    <option value="4" <?= $rating === '4' ? 'selected' : '' ?>>4 ⭐</option>
    <option value="3" <?= $rating === '3' ? 'selected' : '' ?>>3 ⭐</option>
    <option value="2" <?= $rating === '2' ? 'selected' : '' ?>>2 ⭐</option>
    <option value="1" <?= $rating === '1' ? 'selected' : '' ?>>1 ⭐</option>
</select>

</div>


  <!-- Table -->
  <div class="comments-table-wrapper">
    <?php if ($result && mysqli_num_rows($result) > 0): ?>
    <table class="comments-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Người dùng</th>
          <th>Sách</th>
          <th>Rating</th>
          <th>Bình luận</th>
          <th>Ngày tạo</th>
          <th>Trạng thái</th>
          <th>Thao tác</th>
        </tr>
      </thead>

      <tbody>
        <?php while ($c = mysqli_fetch_assoc($result)): ?>
          <tr>
            <td><?= $c['review_id'] ?></td>
            <td><?= htmlspecialchars($c['full_name']) ?></td>
            <td><?= htmlspecialchars($c['title']) ?></td>
            <td><?= $c['rating'] ?>/5</td>
            <td><?= nl2br(htmlspecialchars($c['comment'])) ?></td>
            <td><?= $c['created_at'] ?></td>

            <td>
              <span class="c-status <?= $c['status'] ?>">
                <?= $c['status'] ?>
              </span>
            </td>

            <td class="action-buttons">
              <?php if ($c['status'] === 'pending'): ?>
                <a href="comment_approve.php?id=<?= $c['review_id'] ?>" class="btn-approve">Duyệt</a>
                <a href="comment_hide.php?id=<?= $c['review_id'] ?>" class="btn-hide">Ẩn</a>
              <?php elseif ($c['status'] === 'approved'): ?>
                <a href="comment_hide.php?id=<?= $c['review_id'] ?>" class="btn-hide">Ẩn</a>
              <?php elseif ($c['status'] === 'hidden'): ?>
                <a href="comment_approve.php?id=<?= $c['review_id'] ?>" class="btn-approve">Hiện lại</a>
              <?php endif; ?>

              <a href="comment_delete.php?id=<?= $c['review_id'] ?>" class="btn-delete"
                onclick="return confirm('Xóa bình luận này?')">Xóa</a>
            </td>

          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>

    <?php else: ?>
      <p class="no-comments">Không có bình luận nào.</p>
    <?php endif; ?>

  </div>
</div>

<script>
document.getElementById('filter_rating').addEventListener('change', function() {
    const selectedRating = this.value;
    const params = new URLSearchParams(window.location.search);
    params.set('rating', selectedRating);
    window.location.search = params.toString(); // reload trang với rating mới
});
</script>


</body>
</html>
