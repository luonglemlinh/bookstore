<?php
session_start();
include 'connect.php';
date_default_timezone_set('Asia/Ho_Chi_Minh');
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

// Kiểm tra kết nối
if (!isset($ocon) || $ocon->connect_error) {
    die("Lỗi kết nối Database: " . ($ocon->connect_error ?? "Biến kết nối không tồn tại"));
}

// --- XỬ LÝ ĐẦU VÀO ---
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$date_from = $_GET['date_from'] ?? date('Y-m-01');
$date_to = $_GET['date_to'] ?? date('Y-m-d');

if (strtotime($date_from) > strtotime($date_to)) {
    $temp = $date_from; $date_from = $date_to; $date_to = $temp;
}

// --- 1. SỐ LIỆU TỔNG QUAN ---
$total_customers = $ocon->query("SELECT COUNT(*) as c FROM users WHERE role='customer'")->fetch_assoc()['c'];

$new_customers = $ocon->query("
    SELECT COUNT(*) as c FROM users 
    WHERE role='customer' 
    AND DATE(created_at) BETWEEN '$date_from' AND '$date_to'
")->fetch_assoc()['c'];

$stats = $ocon->query("
    SELECT COUNT(*) as total_orders, IFNULL(SUM(total_amount), 0) as revenue
    FROM orders 
    WHERE order_date BETWEEN '$date_from 00:00:00' AND '$date_to 23:59:59'
")->fetch_assoc();

// --- 2. BIỂU ĐỒ ---
$chart_res = $ocon->query("
    SELECT DATE(order_date) as date, SUM(total_amount) as total
    FROM orders 
    WHERE order_date BETWEEN '$date_from 00:00:00' AND '$date_to 23:59:59'
    GROUP BY DATE(order_date) ORDER BY date ASC
");
$labels = []; $values = [];
if ($chart_res) {
    while ($row = $chart_res->fetch_assoc()) {
        $labels[] = date('d/m', strtotime($row['date']));
        $values[] = (int)$row['total'];
    }
}

// --- 3. TOP VIP (Giữ nguyên vì đã chạy được) ---
$sql_vip = "
    SELECT u.full_name, 
           IFNULL(SUM(o.total_amount), 0) as total_spent,
           COUNT(o.order_id) as total_orders
    FROM users u
    LEFT JOIN orders o ON u.user_id = o.user_id
    WHERE u.role = 'customer' 
    GROUP BY u.user_id
    ORDER BY total_spent DESC 
    LIMIT 5
";
$vip_result = $ocon->query($sql_vip);

// --- 4. DANH SÁCH KHÁCH HÀNG (CÁCH MỚI: TÁCH RIÊNG QUERY) ---
$limit = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Tạo điều kiện lọc
$where_str = "role = 'customer'";
if (!empty($search)) {
    $s = $ocon->real_escape_string($search);
    $where_str .= " AND (full_name LIKE '%$s%' OR email LIKE '%$s%' OR phone LIKE '%$s%')";
}

// 4.1. Đếm tổng số khách
$count_res = $ocon->query("SELECT COUNT(*) as total FROM users WHERE $where_str");
$total_rows = $count_res ? $count_res->fetch_assoc()['total'] : 0;
$totalPages = ceil($total_rows / $limit);

// 4.2. Lấy danh sách khách hàng (CHỈ LẤY THÔNG TIN CƠ BẢN - KHÔNG JOIN, KHÔNG SUB-QUERY)
// Câu lệnh này cực kỳ đơn giản nên không thể lỗi được.
$sql_users = "SELECT * FROM users WHERE $where_str ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$result_users = $ocon->query($sql_users);

// Kiểm tra lỗi SQL ngay tại đây
if (!$result_users) {
    die("Lỗi SQL Users: " . $ocon->error);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Khách Hàng</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="CSS/admin.css">
    <link rel="stylesheet" href="CSS/customer_stats.css">
</head>
<body>

<?php include 'admin_nav.php'; ?>
<?php include 'admin_header.php'; ?>

<div class="dashboard-container">
    
    <div class="dashboard-header">
        <div class="title-section">
            <h2><i class="fa-solid fa-chart-pie"></i> Phân Tích Khách Hàng</h2>
        </div>
        <form class="filter-box" method="GET" id="filterForm" onsubmit="return validateDate()">
            <div class="f-group">
                <input type="text" name="search" class="search-input" placeholder="Tìm tên, email..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="f-group">
                <span class="f-label">Từ:</span>
                <input type="date" name="date_from" id="date_from" value="<?php echo $date_from; ?>">
            </div>
            <div class="f-group">
                <span class="f-label">Đến:</span>
                <input type="date" name="date_to" id="date_to" value="<?php echo $date_to; ?>">
            </div>
            <button type="submit" class="btn-filter"><i class="fa fa-search"></i> Lọc</button>
            <?php if(!empty($search) || $date_from != date('Y-m-01')): ?>
                <a href="admin_customer_stats.php" class="btn-reset">Xóa lọc</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="icon-box blue"><i class="fa fa-users"></i></div>
            <div class="info-box">
                <h4>Tổng Khách Hàng</h4>
                <div class="number"><?php echo number_format($total_customers); ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="icon-box green"><i class="fa fa-user-plus"></i></div>
            <div class="info-box">
                <h4>Khách Mới</h4>
                <div class="number"><?php echo number_format($new_customers); ?></div>
                <small>Trong khoảng lọc</small>
            </div>
        </div>
        <div class="stat-card">
            <div class="icon-box purple"><i class="fa fa-shopping-bag"></i></div>
            <div class="info-box">
                <h4>Tổng Đơn Hàng</h4>
                <div class="number"><?php echo number_format($stats['total_orders']); ?></div>
                <small>Trong khoảng lọc</small>
            </div>
        </div>
        <div class="stat-card">
            <div class="icon-box orange"><i class="fa fa-coins"></i></div>
            <div class="info-box">
                <h4>Doanh Thu</h4>
                <div class="number"><?php echo number_format($stats['revenue'], 0, ',', '.'); ?>đ</div>
                <small>Trong khoảng lọc</small>
            </div>
        </div>
    </div>

    <div class="main-layout-grid">
        <div class="chart-section box-shadow">
            <div class="box-header">
                <h3><i class="fa fa-chart-area"></i> Biểu đồ doanh thu</h3>
            </div>
            <div class="chart-wrapper">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <div class="vip-section box-shadow">
            <div class="box-header">
                <h3><i class="fa fa-crown"></i> Top Khách VIP</h3>
            </div>
            <div class="vip-list-scroll">
                <?php if ($vip_result && $vip_result->num_rows > 0): ?>
                    <?php $rank=1; while($vip=$vip_result->fetch_assoc()): ?>
                    <div class="vip-row">
                        <div class="vip-rank r-<?php echo ($rank <= 3 ? $rank : 'other'); ?>"><?php echo $rank; ?></div>
                        <div class="vip-details">
                            <span class="v-name"><?php echo htmlspecialchars($vip['full_name']); ?></span>
                            <span class="v-sub"><?php echo $vip['total_orders']; ?> đơn</span>
                        </div>
                        <div class="vip-price">
                            <?php echo number_format($vip['total_spent'], 0, ',', '.'); ?>đ
                        </div>
                    </div>
                    <?php $rank++; endwhile; ?>
                <?php else: ?>
                    <p class="no-data-text" style="padding: 15px; text-align: center; color: #999;">Chưa có dữ liệu</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="table-section box-shadow">
        <div class="table-header-row">
            <h3>Danh sách khách hàng (<?php echo $total_rows; ?>)</h3>
        </div>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 25%;">Tên khách hàng</th>
                    <th style="width: 15%;">Số điện thoại</th>
                    <th style="width: 25%;">Email</th>
                    <th style="width: 10%;" class="text-center">Đơn hàng</th>
                    <th style="width: 15%;" class="text-right">Tổng chi</th>
                    <th style="width: 10%;" class="text-center">Chi tiết</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if ($result_users && $result_users->num_rows > 0) {
                    while ($user = $result_users->fetch_assoc()) {
                        // --- TRUY VẤN PHỤ CHO TỪNG USER ---
                        // Tính toán trực tiếp từng người, không sợ lỗi SQL phức tạp
                        $u_id = $user['user_id'];
                        $res_stats = $ocon->query("SELECT COUNT(*) as count, IFNULL(SUM(total_amount),0) as total FROM orders WHERE user_id = '$u_id'");
                        $u_stats = $res_stats->fetch_assoc();
                        
                        $u_count = $u_stats['count'];
                        $u_total = $u_stats['total'];
                ?>
                    <tr>
                        <td>
                            <div class="user-wrap">
                                <div class="u-avatar"><?php echo mb_strtoupper(mb_substr($user['full_name'], 0, 1)); ?></div>
                                <a href="admin_custumer_detail.php?id=<?php echo $user['user_id']; ?>" class="user-link" style ="text-decoration: none; color: #1f2937;">
                                    <?php echo htmlspecialchars($user['full_name']); ?>
                                </a>
                            </div>
                        </td>
                        <td><?php echo !empty($user['phone']) ? $user['phone'] : '<span class="text-muted">--</span>'; ?></td>
                        <td><?php echo !empty($user['email']) ? $user['email'] : '<span class="text-muted">--</span>'; ?></td>
                        <td class="text-center">
                            <span class="badge-order"><?php echo $u_count; ?></span>
                        </td>
                        <td class="text-right">
                            <span class="fw-bold text-primary">
                                <?php echo number_format($u_total, 0, ',', '.'); ?>đ
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="admin_custumer_detail.php?id=<?php echo $user['user_id']; ?>" class="btn-detail">
                                <i class="fa fa-eye"></i> Xem
                            </a>
                        </td>
                    </tr>
                <?php 
                    } // Kết thúc while
                } else { 
                ?>
                    <tr>
                        <td colspan="6" class="text-center" style="padding: 30px;">
                            <i class="fa fa-user-slash" style="font-size: 30px; color: #ccc; margin-bottom: 10px;"></i><br>
                            Không có khách hàng nào.
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <?php if($totalPages > 1): ?>
        <div class="pagination">
            <?php 
                $q = $_GET; unset($q['page']); $qs = http_build_query($q);
                for($i=1; $i<=$totalPages; $i++): 
            ?>
            <a href="?page=<?php echo $i; ?>&<?php echo $qs; ?>" class="<?php echo ($page==$i)?'active':''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>

</div>

<script>
    function validateDate() {
        var from = document.getElementById('date_from').value;
        var to = document.getElementById('date_to').value;
        if (from && to && new Date(from) > new Date(to)) {
            alert('Lỗi: "Ngày từ" không được lớn hơn "Ngày đến"!');
            return false;
        }
        return true;
    }

    const ctx = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($labels); ?>,
            datasets: [{
                label: 'Doanh thu',
                data: <?php echo json_encode($values); ?>,
                borderColor: '#16515fff',
                backgroundColor: 'rgba(22, 81, 95, 0.1)',
                borderWidth: 2,
                pointRadius: 4,
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: {display: false} },
            scales: {
                y: { beginAtZero: true, ticks: { callback: function(val) { return val.toLocaleString('vi-VN') + 'đ'; } } },
                x: { grid: { display: false } }
            }
        }
    });

    
</script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Lấy ô input
        var dateToInput = document.getElementById('date_to');
        
        // Nếu ô này đang rỗng (chưa có giá trị PHP)
        if (!dateToInput.value) {
            var today = new Date();
            // Format ngày thành YYYY-MM-DD để gán vào input date
            var dd = String(today.getDate()).padStart(2, '0');
            var mm = String(today.getMonth() + 1).padStart(2, '0'); // Tháng bắt đầu từ 0
            var yyyy = today.getFullYear();
            
            dateToInput.value = yyyy + '-' + mm + '-' + dd;
        }
    });
</script>
</body>
</html>