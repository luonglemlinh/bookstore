<?php
session_start();
include 'connect.php';


// ==================== KIỂM TRA PHÂN QUYỀN =====================
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
if ($_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}
// Gán tên admin
$admin_name = $_SESSION['username'] ?? "Admin";
// ==================== ĐÁNH DẤU TẤT CẢ ĐÃ ĐỌC =====================
if (isset($_GET['read_all'])) {
    $ocon->query("UPDATE notifications SET is_read = 1");
    header("Location: admin_notifications.php");
    exit();
}

// ==================== LẤY THÔNG BÁO =====================
$sql = "SELECT * FROM notifications ORDER BY created_at DESC";
$result = $ocon->query($sql);

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thông báo hệ thống</title>

    <!-- BOOTSTRAP + ICONS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">

    <style>
        body {
            background: #f5f6fa;
            margin: 0;
            padding: 0;
            font-family: "Segoe UI", sans-serif;
        }

        /* ======= LAYOUT ======= */
        .admin_wrapper {
            display: flex;
            width: 100%;
        }

        /* ======= CONTENT ======= */
        .admin_content {
            margin-left: 0;          /* Sidebar rộng 260px */
            padding: 25px;
            width: calc(100% - 260px);
            margin-top: 80px;            /* Tránh header */
            min-height: 100vh;
        }

        table tbody tr td {
            vertical-align: middle;
        }
    </style>
</head>

<body>

<!-- HEADER -->

<div class="admin_wrapper">

    <!-- SIDEBAR -->
     <?php include 'admin_header.php'; ?>
 <?php include 'admin_nav.php'; ?>

    <!-- CONTENT -->
    <div class="admin_content">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3><i class="fa-solid fa-bell"></i> Danh sách thông báo</h3>

            <a href="admin_notifications.php?read_all=1" class="btn btn-primary">
                <i class="fa-solid fa-check-double"></i> Đánh dấu tất cả đã đọc
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">

                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th width="7%">#</th>
                            <th width="45%">Nội dung</th>
                            <th width="18%">Ngày tạo</th>
                            <th width="15%">Trạng thái</th>
                            <th width="15%">Hành động</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if ($result->num_rows > 0): 
                        $i = 1;
                        while ($row = $result->fetch_assoc()): ?>

                        <tr style="<?= $row['is_read'] ? '' : 'background:#ffeaea;' ?>">
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($row['message']) ?></td>
                            <td><?= $row['created_at'] ?></td>

                            <td>
                                <?= $row['is_read'] 
                                    ? '<span class="badge bg-success">Đã đọc</span>'
                                    : '<span class="badge bg-danger">Chưa đọc</span>' ?>
                            </td>

                            <td>
                                <?php if (!empty($row['order_id'])): ?>
                                    <a href="admin_order_detail.php?id=<?= $row['order_id'] ?>" 
                                       class="btn btn-sm btn-info">
                                        Xem đơn
                                    </a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>

                    <?php endwhile; else: ?>

                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                Không có thông báo nào.
                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>
                </table>

            </div>
        </div>

    </div>

</div>

</body>
</html>
