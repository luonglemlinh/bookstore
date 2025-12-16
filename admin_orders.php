<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

/* ================= TRẠNG THÁI ================= */
$order_status_text = [
    'pending'        => 'Chờ xác nhận',
    'confirmed'      => 'Đã xác nhận',
    'shipping'       => 'Đang giao hàng',
    'completed'      => 'Hoàn thành',
    'canceled'       => 'Đã hủy',
    'req_cancel'     => 'Yêu cầu hủy',
    'reject_cancel'  => 'Từ chối hủy',
    'req_return'     => 'Yêu cầu trả hàng',
    'accept_return'  => 'Chấp nhận trả hàng',
    'reject_return'  => 'Từ chối trả hàng',
];

/* ================= FILTER ================= */
$where = "WHERE 1=1";

$search    = $_GET['search'] ?? '';
$status    = $_GET['status'] ?? '';
$from_date = $_GET['from_date'] ?? '';
$to_date   = $_GET['to_date'] ?? '';

if ($search) {
    $s = mysqli_real_escape_string($ocon, $search);
    $where .= " AND (o.order_id LIKE '%$s%' OR u.full_name LIKE '%$s%')";
}
if ($status) {
    $where .= " AND o.order_status='$status'";
}
if ($from_date) {
    $where .= " AND DATE(o.order_date)>='$from_date'";
}
if ($to_date) {
    $where .= " AND DATE(o.order_date)<='$to_date'";
}

/* ================= DATA ================= */
$sql = "
    SELECT o.*, u.full_name
    FROM orders o
    JOIN users u ON o.user_id=u.user_id
    $where
    ORDER BY o.order_id DESC
";
$result = mysqli_query($ocon, $sql);

/* ================= STATS ================= */
$total_orders   = mysqli_num_rows(mysqli_query($ocon,"SELECT order_id FROM orders"));
$pending        = mysqli_num_rows(mysqli_query($ocon,"SELECT order_id FROM orders WHERE order_status='pending'"));
$confirmed      = mysqli_num_rows(mysqli_query($ocon,"SELECT order_id FROM orders WHERE order_status='confirmed'"));
$shipping       = mysqli_num_rows(mysqli_query($ocon,"SELECT order_id FROM orders WHERE order_status='shipping'"));
$completed      = mysqli_num_rows(mysqli_query($ocon,"SELECT order_id FROM orders WHERE order_status='completed'"));
$canceled       = mysqli_num_rows(mysqli_query($ocon,"SELECT order_id FROM orders WHERE order_status='canceled'"));
$req_cancel     = mysqli_num_rows(mysqli_query($ocon,"SELECT order_id FROM orders WHERE order_status='req_cancel'"));
$req_return     = mysqli_num_rows(mysqli_query($ocon,"SELECT order_id FROM orders WHERE order_status='req_return'"));
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Quản lý đơn hàng</title>

<link rel="stylesheet" href="CSS/admin.css">
<link rel="stylesheet" href="CSS/admin_orders.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
<?php include 'admin_header.php'; ?>
<?php include 'admin_nav.php'; ?>

<main class="admin_main">

<div class="admin_topbar">
    <h2><i class="fas fa-shopping-cart"></i> Quản lý đơn hàng</h2>
</div>

<!-- ================= STATS ================= -->
<div class="stats_grid">
    <div class="stat_card"><h4>Tổng đơn</h4><p><?= $total_orders ?></p></div>
    <div class="stat_card"><h4>Chờ xác nhận</h4><p><?= $pending ?></p></div>
    <div class="stat_card"><h4>Đã xác nhận</h4><p><?= $confirmed ?></p></div>
    <div class="stat_card"><h4>Đang giao</h4><p><?= $shipping ?></p></div>
    <div class="stat_card"><h4>Hoàn thành</h4><p><?= $completed ?></p></div>
    <div class="stat_card"><h4>Đã hủy</h4><p><?= $canceled ?></p></div>
    <div class="stat_card"><h4>YC hủy</h4><p><?= $req_cancel ?></p></div>
    <div class="stat_card"><h4>YC trả</h4><p><?= $req_return ?></p></div>
</div>

<div class="order_table_container">

<!-- ================= FILTER ================= -->
<form method="GET" class="admin_filter_bar">
    <input type="text" name="search"
           placeholder="Tìm mã đơn hoặc khách hàng"
           value="<?= htmlspecialchars($search) ?>">

    <select name="status">
        <option value="">Tất cả trạng thái</option>
        <?php foreach ($order_status_text as $k=>$v): ?>
            <option value="<?= $k ?>" <?= $status==$k?'selected':'' ?>><?= $v ?></option>
        <?php endforeach; ?>
    </select>

    <input type="date" name="from_date" value="<?= $from_date ?>">
    <input type="date" name="to_date" value="<?= $to_date ?>">

    <button type="submit"><i class="fas fa-search"></i> Lọc</button>
    <a href="admin_orders.php" class="btn-reset"><i class="fas fa-rotate"></i> Reset</a>
</form>

<!-- ================= TABLE ================= -->
<?php if (mysqli_num_rows($result) > 0): ?>
<table class="order_table">
<thead>
<tr>
    <th>ID</th>
    <th>Khách hàng</th>
    <th>Ngày đặt</th>
    <th>Tổng tiền</th>
    <th>Trạng thái</th>
    <th>Thao tác</th>
</tr>
</thead>
<tbody>
<?php while ($o = mysqli_fetch_assoc($result)): ?>
<tr>
    <td>#<?= $o['order_id'] ?></td>
    <td><strong><?= htmlspecialchars($o['full_name']) ?></strong></td>
    <td><?= date('d/m/Y', strtotime($o['order_date'])) ?></td>
    <td><?= number_format($o['total_amount'],0,',','.') ?>₫</td>
    <td>
        <span class="badge <?= $o['order_status'] ?>">
            <?= $order_status_text[$o['order_status']] ?>
        </span>
    </td>
    <td>
        <a href="admin_order_detail.php?id=<?= $o['order_id'] ?>" class="btn view">
            <i class="fas fa-eye"></i>
        </a>
    </td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
<?php else: ?>
<div class="empty_state">
    <i class="fas fa-box-open"></i>
    <p>Không có đơn hàng</p>
</div>
<?php endif; ?>

</div>
</main>
</body>
</html>
