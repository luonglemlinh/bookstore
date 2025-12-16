<?php
// Bật báo lỗi
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'connect.php';
date_default_timezone_set('Asia/Ho_Chi_Minh');

// 1. Kiểm tra kết nối
if (!isset($ocon) || $ocon->connect_error) {
    die("Lỗi kết nối Database: " . ($ocon->connect_error ?? "Biến kết nối không xác định"));
}

// 2. Kiểm tra quyền Admin (Bỏ comment khi chạy thật)
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    // header("Location: login.php");
    // exit();
}

$admin_name = $_SESSION['username'] ?? "Admin";

// --- XỬ LÝ BỘ LỌC ---
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$order_status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// --- XỬ LÝ THỜI GIAN (Đã sửa lại gọn gàng) ---
$date_from = $_GET['date_from'] ?? date('Y-m-01');
$date_to = $_GET['date_to'] ?? date('Y-m-d');

// Kiểm tra logic: Nếu "Từ ngày" lớn hơn "Đến ngày" thì đổi chỗ cho nhau (xử lý phía server để tránh lỗi query)
if (strtotime($date_from) > strtotime($date_to)) {
    $temp = $date_from;
    $date_from = $date_to;
    $date_to = $temp;
}

// Tạo chuỗi thời gian đầy đủ cho SQL (Thêm giờ phút giây)
$date_from_dt = "$date_from 00:00:00";
$date_to_dt   = "$date_to 23:59:59";

// --- 3. SỐ LIỆU TỔNG QUAN ---
$sql_stats = "
    SELECT 
        COUNT(*) as total_orders, 
        IFNULL(SUM(CASE WHEN order_status = 'completed' THEN total_amount ELSE 0 END), 0) as completed_revenue,
        COUNT(CASE WHEN order_status = 'completed' THEN order_id END) as completed_orders,
        COUNT(CASE WHEN order_status = 'canceled' THEN order_id END) as canceled_orders,
        COUNT(CASE WHEN order_status = 'shipping' THEN order_id END) as shipping_orders
    FROM orders 
    WHERE order_date BETWEEN '$date_from_dt' AND '$date_to_dt'
";
$stats = $ocon->query($sql_stats)->fetch_assoc();

// --- 4. BIỂU ĐỒ ---
$sql_chart = "
    SELECT DATE(order_date) as date, SUM(total_amount) as total
    FROM orders 
    WHERE order_status = 'completed' AND order_date BETWEEN '$date_from_dt' AND '$date_to_dt'
    GROUP BY DATE(order_date) ORDER BY date ASC
";
$chart_res = $ocon->query($sql_chart);
$labels = []; $values = [];
if ($chart_res) {
    while ($row = $chart_res->fetch_assoc()) {
        $labels[] = date('d/m', strtotime($row['date']));
        $values[] = (int)$row['total'];
    }
}

// --- 5. DANH SÁCH CHI TIẾT 
$limit = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Xây dựng điều kiện lọc
$sql_where = "WHERE order_date BETWEEN '$date_from_dt' AND '$date_to_dt'";

if (!empty($order_status_filter)) {
    $s_status = $ocon->real_escape_string($order_status_filter);
    $sql_where .= " AND order_status = '$s_status'";
}

if (!empty($search)) {
    $s = $ocon->real_escape_string($search);
    $sql_where .= " AND (order_id LIKE '%$s%' OR user_id IN (SELECT user_id FROM users WHERE full_name LIKE '%$s%' OR email LIKE '%$s%'))";
}

// Đếm tổng số
$sql_count = "SELECT COUNT(*) as total FROM orders $sql_where";
$count_res = $ocon->query($sql_count);
$total_rows = ($count_res) ? $count_res->fetch_assoc()['total'] : 0;
$totalPages = ceil($total_rows / $limit);

// Lấy danh sách
$sql_list = "SELECT * FROM orders $sql_where ORDER BY order_date DESC LIMIT $limit OFFSET $offset";
$result_query = $ocon->query($sql_list);

// ĐỔ DỮ LIỆU VÀO MẢNG
$orders_data = [];
if ($result_query && $result_query->num_rows > 0) {
    while ($row = $result_query->fetch_assoc()) {
        // Lấy tên khách hàng
        $u_id = $row['user_id'];
        $u_name = "Khách vãng lai";
        if ($u_id) {
            $u_res = $ocon->query("SELECT full_name FROM users WHERE user_id = '$u_id'");
            if ($u_res && $u_res->num_rows > 0) {
                $u_name = $u_res->fetch_assoc()['full_name'];
            }
        }
        $row['customer_name'] = $u_name; 
        $orders_data[] = $row;
    }
}

// --- HÀM BADGE ---
function get_status_badge($status) {
    $map = [
        'pending' => ['Chờ xử lý', 'secondary'],
        'confirmed' => ['Đã xác nhận', 'info'],
        'shipping' => ['Đang giao', 'primary'],
        'completed' => ['Hoàn thành', 'success'],
        'canceled' => ['Đã hủy', 'danger'],
        'req_cancel' => ['Y/C Hủy', 'warning'],
        'reject_cancel' => ['Từ chối Hủy', 'dark'],
        'req_return' => ['Y/C Trả', 'warning'],
        'accept_return' => ['Chấp nhận Trả', 'success'],
        'reject_return' => ['Từ chối Trả', 'danger'],
    ];
    $s = strtolower($status ?? '');
    $text = $map[$s][0] ?? ucfirst($s);
    $class = $map[$s][1] ?? 'secondary';
    return "<span class='badge-status badge-$class'>$text</span>";
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Đơn Hàng</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="CSS/admin.css">
    <link rel="stylesheet" href="CSS/admin_stats_orders.css">
    <style>
        .badge-status { padding: 5px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; color: #fff; display: inline-block; white-space: nowrap; }
        .badge-secondary { background: #6c757d; }
        .badge-info { background: #17a2b8; }
        .badge-primary { background: #007bff; }
        .badge-success { background: #28a745; }
        .badge-danger { background: #dc3545; }
        .badge-warning { background: #ffc107; color: #333; }
        .badge-dark { background: #343a40; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: 600; }
        .text-primary { color: #16515fff; }
    </style>
</head>
<body>

<?php include 'admin_nav.php'; ?>
<?php include 'admin_header.php'; ?>

<div class="dashboard-container">
    
    <div class="dashboard-header">
        <div class="title-section">
            <h2><i class="fa-solid fa-receipt"></i> Phân Tích Đơn Hàng</h2>
        </div>
        
        <form class="filter-box" method="GET" id="filterForm" onsubmit="return validateDate()">
            <div class="f-group">
                <input type="text" name="search" class="search-input" placeholder="Tìm ID đơn, tên..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="f-group">
                <select name="status" class="status-select">
                    <option value="">-- Trạng thái --</option>
                    <option value="pending" <?php echo ($order_status_filter=='pending'?'selected':''); ?>>Chờ xử lý</option>
                    <option value="confirmed" <?php echo ($order_status_filter=='confirmed'?'selected':''); ?>>Đã xác nhận</option>
                    <option value="shipping" <?php echo ($order_status_filter=='shipping'?'selected':''); ?>>Đang giao</option>
                    <option value="completed" <?php echo ($order_status_filter=='completed'?'selected':''); ?>>Hoàn thành</option>
                    <option value="canceled" <?php echo ($order_status_filter=='canceled'?'selected':''); ?>>Đã hủy</option>
                </select>
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
            <?php if(!empty($search) || !empty($order_status_filter) || $date_from != '2020-01-01'): ?>
                <a href="admin_stats_orders .php" class="btn-reset">Xóa lọc</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="icon-box blue"><i class="fa fa-shopping-cart"></i></div>
            <div class="info-box">
                <h4>Tổng Đơn Hàng</h4>
                <div class="number"><?php echo number_format($stats['total_orders']); ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="icon-box green-alt"><i class="fa fa-check-circle"></i></div>
            <div class="info-box">
                <h4>Đơn Hoàn Thành</h4>
                <div class="number"><?php echo number_format($stats['completed_orders']); ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="icon-box red"><i class="fa fa-times-circle"></i></div>
            <div class="info-box">
                <h4>Đơn Đã Hủy</h4>
                <div class="number"><?php echo number_format($stats['canceled_orders']); ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="icon-box orange"><i class="fa fa-wallet"></i></div>
            <div class="info-box">
                <h4>Doanh Thu (Thực thu)</h4>
                <div class="number"><?php echo number_format($stats['completed_revenue'], 0, ',', '.'); ?>đ</div>
            </div>
        </div>
    </div>

    <div class="chart-section box-shadow" style="margin-top: 20px;">
        <div class="box-header">
            <h3><i class="fa fa-line-chart"></i> Biểu đồ doanh thu hoàn thành</h3>
        </div>
        <div class="chart-wrapper">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    <div class="table-section box-shadow" style="margin-top: 20px;">
        <div class="table-header-row">
            <h3>Danh sách đơn hàng (<?php echo $total_rows; ?> đơn)</h3>
        </div>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 10%;">ID Đơn</th>
                    <th style="width: 15%;">Ngày đặt</th>
                    <th style="width: 20%;">Khách hàng</th>
                    <th style="width: 15%;">Thanh toán</th>
                    <th style="width: 15%;" class="text-right">Tổng tiền</th>
                    <th style="width: 10%;" class="text-center">Trạng thái</th>
                    <th style="width: 10%;" class="text-center">Chi tiết</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($orders_data) > 0): ?>
                    <?php foreach ($orders_data as $row): ?>
                    <tr>
                        <td><strong>#<?php echo $row['order_id']; ?></strong></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($row['order_date'])); ?></td>
                        <td>
                            <a href="admin_customer_detail.php?id=<?php echo $row['user_id']; ?>" class="user-link">
                                <?php echo htmlspecialchars($row['customer_name']); ?>
                            </a>
                        </td>
                        <td><?php echo ($row['payment_method'] == 'COD') ? 'COD' : 'Online'; ?></td>
                        <td class="text-right">
                            <span class="fw-bold text-primary">
                                <?php echo number_format($row['total_amount'], 0, ',', '.'); ?>đ
                            </span>
                        </td>
                        <td class="text-center">
                            <?php echo get_status_badge($row['order_status']); ?>
                        </td>
                        <td class="text-center">
                            <a href="admin_order_detail.php?id=<?php echo $row['order_id']; ?>" class="btn-detail">
                                <i class="fa fa-eye"></i> Xem
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center" style="padding: 30px; color: #999;">
                            <i class="fa fa-search" style="font-size: 24px; margin-bottom: 10px;"></i><br>
                            Không tìm thấy đơn hàng nào trong khoảng thời gian này.<br>
                            (Hãy thử chỉnh ngày bắt đầu về năm 2020)
                        </td>
                    </tr>
                <?php endif; ?>
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

    const ctx = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($labels); ?>,
            datasets: [{
                label: 'Doanh thu (Đã hoàn thành)',
                data: <?php echo json_encode($values); ?>,
                borderColor: '#1e88e5',
                backgroundColor: 'rgba(30, 136, 229, 0.1)',
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

</body>
</html>