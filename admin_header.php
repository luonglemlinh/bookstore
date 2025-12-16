<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'connect.php';

// 1. ĐẾM THÔNG BÁO CHƯA ĐỌC
$sql_count = "SELECT COUNT(*) AS total FROM notifications WHERE is_read = 0";
$res_count = $ocon->query($sql_count);
$notify_count = $res_count->fetch_assoc()['total'] ?? 0;

// 2. LẤY DANH SÁCH THÔNG BÁO
$sql_list = "SELECT * FROM notifications ORDER BY created_at DESC LIMIT 6";
$list_result = $ocon->query($sql_list);
?>

<style>
.admin-header {
    height: 70px; background: #fff; color: #16515f;
    display: flex; justify-content: space-between; align-items: center;
    padding: 0 30px; position: fixed; top: 0; left: 260px;
    width: calc(100% - 260px); z-index: 999;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}
.header-title { font-size: 20px; font-weight: 700; }
.notify-wrapper { position: relative; }
.notify-bell { position: relative; cursor: pointer; font-size: 22px; padding: 5px; }
.notify-count {
    position: absolute; top: -5px; right: -5px; background: #e74c3c;
    color: white; padding: 2px 6px; border-radius: 50%; font-size: 11px;
}
.notify-dropdown {
    display: none; position: absolute; right: 0; top: 50px; width: 340px;
    background: white; border-radius: 8px; box-shadow: 0 5px 20px rgba(0,0,0,0.15);
    border: 1px solid #f0f0f0; overflow: hidden;
}
.notify-header-text { padding: 12px 15px; background: #f8f9fa; font-weight: bold; border-bottom: 1px solid #eee; margin: 0;}
.notify-item {
    padding: 12px 15px; border-bottom: 1px solid #f1f1f1; display: flex;
    text-decoration: none; color: #555; align-items: flex-start;
}
.notify-item:hover { background: #f0f8ff; }
.notify-icon {
    width: 36px; height: 36px; border-radius: 50%; display: flex;
    align-items: center; justify-content: center; margin-right: 12px;
    color: #fff; flex-shrink: 0; font-size: 14px;
}
.icon-new { background: #007bff; }
.icon-other { background: #28a745; }
.notify-content { flex: 1; }
.notify-content b { display: block; color: #333; font-size: 13px; margin-bottom: 2px; }
.notify-content p { margin: 0; font-size: 12px; color: #666; }
.notify-content small { color: #aaa; font-size: 11px; margin-top: 4px; display: block; }
.notify-footer {
    display: block; text-align: center; padding: 10px; background: #fff;
    color: #16515f; font-weight: 600; font-size: 13px; text-decoration: none;
}
@media (max-width: 768px) { .admin-header { left: 0; width: 100%; } }
</style>

<div class="admin-header">
    <div class="header-title">Quản trị Website</div>
    

    <div class="notify-wrapper">
        <div class="notify-bell" onclick="toggleNotify()">
            <i class="fa fa-bell"></i>
            <?php if ($notify_count > 0): ?>
                <span class="notify-count"><?php echo $notify_count; ?></span>
            <?php endif; ?>
        </div>

        <div class="notify-dropdown" id="notifyBox">
            <h4 class="notify-header-text">Thông báo hoạt động</h4>

            <?php if ($list_result && $list_result->num_rows > 0): ?>
                <?php while ($row = $list_result->fetch_assoc()): ?>
                    <?php 
                        // Tùy chỉnh hiển thị dựa trên loại thông báo (type)
                        $bg = 'icon-other';
                        $icon = 'fa-bell';

                        if ($row['type'] == 'new_order') {
                            $bg = 'icon-new';
                            $icon = 'fa-shopping-cart';
                        } elseif ($row['type'] == 'return_request') {
                            $bg = 'icon-new';
                            $icon = 'fa-undo';
                        } elseif ($row['type'] == 'return_cancel') {
                            $bg = 'icon-other';
                            $icon = 'fa-times';
                        }
                        $title_display = htmlspecialchars($row['title']);
                    ?>

                    <a href="admin_order_detail.php?id=<?= $row['order_id'] ?>" class="notify-item">
                        <div class="notify-icon <?= $bg ?>">
                            <i class="fa <?= $icon ?>"></i>
                        </div>
                        <div class="notify-content">
                            <b><?= $title_display ?></b>
                            <p><?= htmlspecialchars($row['message']) ?></p>
                            <small><?= date("d/m H:i", strtotime($row['created_at'])) ?></small>
                        </div>
                    </a>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="padding: 20px; text-align: center; color: #999;">Chưa có thông báo nào</div>
            <?php endif; ?>

            <a href="admin_notifications.php" class="notify-footer">Xem tất cả</a>
        </div>
    </div>
</div>

<script>
function toggleNotify() {
    var x = document.getElementById("notifyBox");
    x.style.display = (x.style.display === "block") ? "none" : "block";
}
// Click ra ngoài thì đóng
window.onclick = function(e) {
    if (!e.target.closest('.notify-wrapper')) {
        document.getElementById("notifyBox").style.display = "none";
    }
}
</script>
