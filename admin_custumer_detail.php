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

// 1. Kiểm tra kết nối
if (!isset($ocon) || $ocon->connect_error) {
    die("Lỗi kết nối Database: " . ($ocon->connect_error ?? "Biến kết nối không xác định"));
}

// 2. Lấy ID khách hàng
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Không tìm thấy ID khách hàng.");
}
$user_id = (int)$_GET['id'];

// 3. Lấy thông tin khách (BIẾN AN TOÀN)
$sql_user = "SELECT * FROM users WHERE user_id = $user_id";
$user_res = $ocon->query($sql_user);
if (!$user_res || $user_res->num_rows == 0) {
    die("Khách hàng không tồn tại.");
}
$customer_info = $user_res->fetch_assoc();

// 4. Thống kê
$sql_stats = "
    SELECT 
        COUNT(*) as total_orders, 
        IFNULL(SUM(total_amount), 0) as total_spent,
        MAX(order_id) as last_order_id,
        MAX(order_date) as last_order_date
    FROM orders 
    WHERE user_id = $user_id
";
$stats = $ocon->query($sql_stats)->fetch_assoc();
$avg_spent = ($stats['total_orders'] > 0) ? ($stats['total_spent'] / $stats['total_orders']) : 0;

// 5. Lấy danh sách địa chỉ (MỚI THÊM)
// Sắp xếp: Địa chỉ mặc định (is_default=1) lên đầu
$sql_addr = "SELECT * FROM addresses WHERE user_id = $user_id ORDER BY is_default DESC";
$result_addr = $ocon->query($sql_addr);

// 6. Lấy danh sách đơn hàng
$sql_orders = "SELECT * FROM orders WHERE user_id = $user_id ORDER BY order_date DESC";
$result_orders = $ocon->query($sql_orders);

// --- HÀM MÀU SẮC ---
function getStatusColor($status) {
    $s = strtolower((string)$status);
    switch ($s) {
        case 'paid': case 'completed': case 'success': return 'success';
        case 'pending': return 'warning';
        case 'shipping': case 'confirmed': return 'info';
        case 'canceled': case 'failed': case 'rejected': case 'return': return 'danger';
        default: return 'secondary';
    }
}

function getStatusText($status) {
    $s = strtolower((string)$status);
    $map = [
        'pending' => 'Chờ xử lý', 'confirmed' => 'Đã xác nhận', 
        'shipping' => 'Đang giao', 'completed' => 'Hoàn thành', 
        'canceled' => 'Đã hủy', 'paid' => 'Đã thanh toán', 
        'cod' => 'COD', 'online' => 'Chuyển khoản'
    ];
    return $map[$s] ?? ucfirst($s);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi tiết: <?= htmlspecialchars($customer_info['full_name']) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="CSS/admin.css">
    <link rel="stylesheet" href="CSS/admin_customer_detail.css">
</head>
<body>

<?php include 'admin_nav.php'; ?>
<?php include 'admin_header.php'; ?>

<div class="detail-container">
<div class="" style =" margin-top : 50px;"></div>
    <div class="card-box">
        <div class="profile-header">
            <div class="p-avatar">
                <?= mb_strtoupper(mb_substr($customer_info['full_name'], 0, 1)) ?>
            </div>
            <div class="p-info">
                <h2><?= htmlspecialchars($customer_info['full_name']) ?></h2>
                <div class="p-contact">
                    <span><i class="fa fa-phone"></i> <?= !empty($customer_info['phone']) ? $customer_info['phone'] : 'Chưa có SĐT' ?></span>
                    <span><i class="fa fa-envelope"></i> <?= $customer_info['email'] ?></span>
                    <span><i class="fa fa-hashtag"></i> ID: <?= $customer_info['user_id'] ?></span>
                </div>
            </div>
        </div>

        <div class="stats-row">
            <div class="stat-col">
                <div class="stat-label">Đơn hàng mới nhất</div>
                <?php if ($stats['last_order_id']): ?>
                    <a href="admin_order_detail.php?id=<?= $stats['last_order_id'] ?>" class="stat-link">
                        #<?= $stats['last_order_id'] ?>
                    </a>
                    <div class="stat-sub"><?= date('d/m/Y', strtotime($stats['last_order_date'])) ?></div>
                <?php else: ?>
                    <span class="stat-value">--</span>
                <?php endif; ?>
            </div>
            
            <div class="stat-col">
                <div class="stat-label">Tổng chi tiêu</div>
                <div class="stat-value"><?= number_format($stats['total_spent'], 0, ',', '.') ?>đ</div>
                <div class="stat-sub"><?= $stats['total_orders'] ?> đơn hàng</div>
            </div>

            <div class="stat-col">
                <div class="stat-label">Chi tiêu trung bình</div>
                <div class="stat-value"><?= number_format($avg_spent, 0, ',', '.') ?>đ</div>
            </div>
        </div>
    </div>

    <div class="card-box">
        <div class="section-header">
            <span class="section-title">Sổ địa chỉ</span>
            <?php if ($result_addr): ?>
                <span class="link-all"><?= $result_addr->num_rows ?> địa chỉ</span>
            <?php endif; ?>
        </div>

        <div class="address-grid">
            <?php if ($result_addr && $result_addr->num_rows > 0): ?>
                <?php while ($addr = $result_addr->fetch_assoc()): ?>
                    <div class="address-item">
                        <div class="addr-header">
                            <span class="addr-name">
                                <i class="fa fa-user-circle" style="color:#ccc; margin-right:5px"></i>
                                <?= htmlspecialchars($addr['receiver_name']) ?>
                            </span>
                            <?php if ($addr['is_default'] == 1): ?>
                                <span class="badge-default"><i class="fa fa-check-circle"></i> Mặc định</span>
                            <?php endif; ?>
                        </div>
                        <div class="addr-info">
                            <div style="margin-bottom: 5px;">
                                <i class="fa fa-phone" style="width:15px; text-align:center; margin-right:5px; color:#16515fff;"></i> 
                                <span class="addr-phone"><?= $addr['receiver_phone'] ?></span>
                            </div>
                            <div>
                                <i class="fa fa-map-marker-alt" style="width:15px; text-align:center; margin-right:5px; color:#16515fff;"></i>
                                <?= htmlspecialchars($addr['specific_address']) ?>, 
                                <?= htmlspecialchars($addr['ward']) ?>, 
                                <?= htmlspecialchars($addr['district']) ?>, 
                                <?= htmlspecialchars($addr['province']) ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; color: #999; padding: 20px;">
                    <i class="fa fa-map-marked-alt" style="font-size: 30px; margin-bottom: 10px; opacity: 0.5;"></i><br>
                    Khách hàng chưa lưu địa chỉ nào.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card-box">
        <div class="section-header">
            <span class="section-title">Đơn hàng gần đây</span>
            <span class="link-all"><?= $stats['total_orders'] ?> đơn hàng</span>
        </div>

        <div class="order-list">
            <?php if ($result_orders && $result_orders->num_rows > 0): ?>
                <?php while ($row_ord = $result_orders->fetch_assoc()): ?>
                    <div class="order-row">
                        <div class="o-left">
                            <a href="admin_order_detail.php?id=<?= $row_ord['order_id'] ?>" class="o-id">
                                #<?= $row_ord['order_id'] ?>
                            </a>
                            <div class="o-meta">
                                Website | <?= date('d/m/Y H:i', strtotime($row_ord['order_date'])) ?>
                            </div>
                        </div>

                        <div class="o-right">
                            <div class="o-price">
                                <?= number_format($row_ord['total_amount'], 0, ',', '.') ?>đ
                            </div>
                            <div class="badges-group">
                                <span class="status-badge <?= getStatusColor($row_ord['payment_status']) ?>">
                                    <span class="dot"></span>
                                    <?= getStatusText($row_ord['payment_status']) ?>
                                </span>
                                <span class="status-badge <?= getStatusColor($row_ord['order_status']) ?>">
                                    <span class="dot"></span>
                                    <?= getStatusText($row_ord['order_status']) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 40px; color: #999;">
                    <i class="fa fa-box-open" style="font-size: 30px; margin-bottom: 10px; opacity: 0.5;"></i><br>
                    Khách hàng này chưa có đơn hàng nào.
                </div>
            <?php endif; ?>
        </div>
    </div>
<a href="admin_customer_stats.php" class="btn-back">
        <i class="fa fa-arrow-left"></i> Quay lại danh sách
    </a>
</div>

</body>
</html>