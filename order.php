<?php
include 'connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$status = isset($_GET['status']) ? $_GET['status'] : 'none'; // ⬅ mặc định KHÔNG hiển thị

$filter = "";
if ($status !== "all" && $status !== "none") {
    $filter = "AND o.order_status = '$status'";
}

$query = "
    SELECT o.*, u.full_name 
    FROM orders o 
    JOIN users u ON o.user_id = u.user_id
    WHERE o.user_id = '$user_id' $filter
    ORDER BY o.order_id DESC
";

$orders = mysqli_query($ocon, $query);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ | Bookiee</title>
    <link rel="stylesheet" href="CSS/style.css">
     <link rel="stylesheet" href="CSS/order.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>


<body>

<?php include 'header.php'; ?>

<section class="orders_container">
    <h2>Đơn hàng của tôi</h2>

    <!-- Tabs -->
    <div class="order_tabs">
        <a href="order.php?status=all" class="tab <?= ($status=='all')?'active':'' ?>" data-tab="all">Tất cả</a>
        <a href="order.php?status=pending" class="tab <?= ($status=='pending')?'active':'' ?>" data-tab="pending">Chờ xác nhận</a>
        <a href="order.php?status=confirmed" class="tab <?= ($status=='confirmed')?'active':'' ?>" data-tab="confirmed">Đã xác nhận</a>
        <a href="order.php?status=shipping" class="tab <?= ($status=='shipping')?'active':'' ?>" data-tab="shipping">Đang vận chuyển</a>
        <a href="order.php?status=completed" class="tab <?= ($status=='completed')?'active':'' ?>" data-tab="completed">Đã hoàn thành</a>
        <a href="order.php?status=req_cancel" class="tab <?= ($status=='req_cancel')?'active':'' ?>" data-tab="req_cancel">Yêu cầu hủy</a>
        <a href="order.php?status=canceled" class="tab <?= ($status=='canceled')?'active':'' ?>" data-tab="canceled">Đã hủy</a>
        <a href="order.php?status=req_return" 
   class="tab <?= ($status=='req_return')?'active':'' ?>">
   Yêu cầu trả hàng
</a>
        <a href="order.php?status=accept_return" class="tab <?= ($status=='return_requested')?'active':'' ?>" data-tab="return_requested">Đã chấp nhận trả hàng</a>
        <a href="order.php?status=Reject_return" class="tab <?= ($status=='return_requested')?'active':'' ?>" data-tab="return_requested">Đã từ chối trả hàng</a>


    </div>

    <!-- Wrapper để ẩn / hiện -->
    <div id="order_list_wrapper">
        <div class="order_list">

            <?php if ($status !== "none") {  // Nếu chưa click tab nào thì KHÔNG render ?>
            
                <?php
                if (mysqli_num_rows($orders) > 0) {
                    while ($o = mysqli_fetch_assoc($orders)) {
                ?>
                <div class="order_item">
                    <h3>Đơn hàng #<?= $o['order_id'] ?></h3>

                    <p><strong>Ngày đặt:</strong> <?= $o['order_date'] ?></p>

                    <p><strong>Tổng tiền:</strong> 
                        <?= number_format($o['total_amount'] + $o['shipping_fee'], 0, ',', '.') ?>₫
                    </p>

                    <p><strong>Trạng thái:</strong> 
                        <span class="status <?= $o['order_status'] ?>">
                            <?= ucfirst($o['order_status']) ?>
                        </span>
                    </p>

                    <a href="order_detail.php?id=<?= $o['order_id']; ?>" class="order_detail_btn">
                        Xem chi tiết
                    </a>
                </div>
                <?php
                    }
                } else {
                    echo "<p class='empty'>Không có đơn hàng nào.</p>";
                }
                ?>

            <?php } ?>

        </div>
    </div>
</section>

<?php include 'footer.php'; ?>


<!-- Khi click vào tab "Tất cả" thì mới hiện -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    let wrapper = document.getElementById("order_list_wrapper");

    <?php if ($status !== "none") { ?>
        wrapper.style.display = "block";
    <?php } ?>

    const tabs = document.querySelectorAll(".order_tabs .tab");

    tabs.forEach(tab => {
        tab.addEventListener("click", () => {
            wrapper.style.display = "block";
        });
    });
});
</script>

</body>
</html>
