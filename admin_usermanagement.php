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

// Đánh dấu trang hiện tại để active menu
$current_page = basename($_SERVER['PHP_SELF']);

$message = [];

// XỬ LÝ CẬP NHẬT THÔNG TIN USER
if (isset($_POST['update_user'])) {
    $edit_id = intval($_POST['user_id']);
    $full_name = mysqli_real_escape_string($ocon, $_POST['full_name']);
    $phone = mysqli_real_escape_string($ocon, $_POST['phone']);
    $role = mysqli_real_escape_string($ocon, $_POST['role']);
    $status = mysqli_real_escape_string($ocon, $_POST['status']);
    
    $update_query = "UPDATE users SET 
                     full_name = '$full_name',
                     phone = '$phone',
                     role = '$role',
                     status = '$status'
                     WHERE user_id = $edit_id";
    
    if (mysqli_query($ocon, $update_query)) {
        $message[] = "Cập nhật thông tin người dùng thành công!";
    } else {
        $message[] = "Lỗi: " . mysqli_error($ocon);
    }
}

// XỬ LÝ KHÓA/MỞ KHÓA TÀI KHOẢN
if (isset($_GET['toggle_status'])) {
    $user_id = intval($_GET['toggle_status']);
    
    $check = mysqli_query($ocon, "SELECT status FROM users WHERE user_id = $user_id");
    $user = mysqli_fetch_assoc($check);
    
    $new_status = ($user['status'] == 'active') ? 'banned' : 'active';
    
    mysqli_query($ocon, "UPDATE users SET status = '$new_status' WHERE user_id = $user_id");
    header("Location: admin_usermanagement.php");
    exit();
}

// XỬ LÝ XÓA TÀI KHOẢN
if (isset($_GET['delete'])) {
    $user_id = intval($_GET['delete']);
    
    // Không cho xóa chính mình
    if ($user_id == $_SESSION['user_id']) {
        $message[] = "Không thể xóa tài khoản của chính bạn!";
    } else {
        mysqli_query($ocon, "DELETE FROM users WHERE user_id = $user_id");
        $message[] = "Đã xóa người dùng thành công!";
    }
}

// TÌM KIẾM
$search = '';
$where = "WHERE 1=1";

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($ocon, $_GET['search']);
    $where .= " AND (full_name LIKE '%$search%' 
                OR email LIKE '%$search%' 
                OR phone LIKE '%$search%')";
}

if (isset($_GET['role']) && !empty($_GET['role'])) {
    $role_filter = mysqli_real_escape_string($ocon, $_GET['role']);
    $where .= " AND role = '$role_filter'";
}

if (isset($_GET['status']) && !empty($_GET['status'])) {
    $status_filter = mysqli_real_escape_string($ocon, $_GET['status']);
    $where .= " AND status = '$status_filter'";
}

// LẤY DANH SÁCH NGƯỜI DÙNG
$users_query = "SELECT * FROM users $where ORDER BY user_id DESC";
$users_result = mysqli_query($ocon, $users_query);

// ĐẾM TỔNG
$total_users = mysqli_num_rows($users_result);
$total_active = mysqli_num_rows(mysqli_query($ocon, "SELECT user_id FROM users WHERE status='active'"));
$total_banned = mysqli_num_rows(mysqli_query($ocon, "SELECT user_id FROM users WHERE status='banned'"));
$total_admin = mysqli_num_rows(mysqli_query($ocon, "SELECT user_id FROM users WHERE role='admin'"));
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý người dùng | Babo Bookstore</title>
    <link rel="stylesheet" href="CSS/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .user_table_container {
            background: white;
            padding: 1.5rem;
            border-radius: 16px;
            box-shadow: 0 6px 12px rgba(0,0,0,0.05);
            margin-top: 1.5rem;
        }

        .search_bar {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .search_bar input,
        .search_bar select {
            padding: 0.6rem 1rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 0.95rem;
        }

        .search_bar input[type="text"] {
            flex: 1;
            min-width: 250px;
        }

        .search_bar button {
            padding: 0.6rem 1.5rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: 0.3s;
        }

        .search_bar button:hover {
            background: var(--accent);
        }

        .user_table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .user_table th {
            background: var(--primary);
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
        }

        .user_table td {
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
        }

        .user_table tr:hover {
            background: #f9f9f9;
        }

        .badge {
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .badge.admin {
            background: #fef3c7;
            color: #92400e;
        }

        .badge.customer {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge.active {
            background: #d1fae5;
            color: #065f46;
        }

        .badge.banned {
            background: #fee2e2;
            color: #991b1b;
        }

        .action_btns {
            display: flex;
            gap: 0.5rem;
        }

        .btn {
            padding: 0.4rem 0.8rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.85rem;
            transition: 0.3s;
            text-decoration: none;
            color: white;
        }

        .btn.edit {
            background: #3b82f6;
        }

        .btn.edit:hover {
            background: #2563eb;
        }

        .btn.block {
            background: #f59e0b;
        }

        .btn.block:hover {
            background: #d97706;
        }

        .btn.unblock {
            background: #10b981;
        }

        .btn.unblock:hover {
            background: #059669;
        }

        .btn.delete {
            background: #ef4444;
        }

        .btn.delete:hover {
            background: #dc2626;
        }

        .stats_grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat_card {
            background: white;
            padding: 1.2rem;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
            border-left: 4px solid var(--primary);
        }

        .stat_card h4 {
            font-size: 0.9rem;
            color: #6b7280;
            margin-bottom: 0.5rem;
        }

        .stat_card p {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary);
        }

        .message {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 8px;
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }

        .message.error {
            background: #fee2e2;
            color: #991b1b;
            border-left-color: #ef4444;
        }

        /* MODAL */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }

        .modal.active {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .modal_content {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            width: 90%;
            max-width: 500px;
        }

        .modal_content h3 {
            margin-bottom: 1.5rem;
            color: var(--primary);
        }

        .form_group {
            margin-bottom: 1rem;
        }

        .form_group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #374151;
        }

        .form_group input,
        .form_group select {
            width: 100%;
            padding: 0.6rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 0.95rem;
        }

        .modal_btns {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .modal_btns button {
            flex: 1;
            padding: 0.7rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }

        .modal_btns .save {
            background: var(--primary);
            color: white;
        }

        .modal_btns .cancel {
            background: #e5e7eb;
            color: #374151;
        }

        .empty_state {
            text-align: center;
            padding: 3rem;
            color: #9ca3af;
        }

        .empty_state i {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
    </style>
</head>

<body>
<?php include 'admin_header.php'; ?>
<?php include 'admin_nav.php'; ?>

<main class="admin_main">

    <div class="admin_topbar">
        <h2><i class="fas fa-users"></i> Quản lý người dùng</h2>
    </div>

    <?php if (!empty($message)): ?>
        <?php foreach ($message as $msg): ?>
            <div class="message"><?= htmlspecialchars($msg) ?></div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- THỐNG KÊ -->
    <div class="stats_grid">
        <div class="stat_card">
            <h4>Tổng người dùng</h4>
            <p><?= $total_users ?></p>
        </div>
        <div class="stat_card">
            <h4>Đang hoạt động</h4>
            <p><?= $total_active ?></p>
        </div>
        <div class="stat_card">
            <h4>Bị khóa</h4>
            <p><?= $total_banned ?></p>
        </div>
        <div class="stat_card">
            <h4>Quản trị viên</h4>
            <p><?= $total_admin ?></p>
        </div>
    </div>

    <div class="user_table_container">
        
        <!-- TÌM KIẾM -->
        <form method="GET" class="search_bar">
            <input type="text" name="search" placeholder="Tìm theo tên, email, số điện thoại..." 
                   value="<?= htmlspecialchars($search) ?>">
            
            <select name="role">
                <option value="">Tất cả vai trò</option>
                <option value="admin" <?= (isset($_GET['role']) && $_GET['role']=='admin') ? 'selected' : '' ?>>Admin</option>
                <option value="customer" <?= (isset($_GET['role']) && $_GET['role']=='customer') ? 'selected' : '' ?>>Customer</option>
            </select>

            <select name="status">
                <option value="">Tất cả trạng thái</option>
                <option value="active" <?= (isset($_GET['status']) && $_GET['status']=='active') ? 'selected' : '' ?>>Active</option>
                <option value="banned" <?= (isset($_GET['status']) && $_GET['status']=='banned') ? 'selected' : '' ?>>Banned</option>
            </select>

            <button type="submit"><i class="fas fa-search"></i> Tìm kiếm</button>
            <a href="admin_usermanagement.php" class="btn" style="background:#6b7280;padding:0.6rem 1rem;">
                <i class="fas fa-redo"></i> Reset
            </a>
        </form>

        <!-- BẢNG NGƯỜI DÙNG -->
        <?php if (mysqli_num_rows($users_result) > 0): ?>
            <table class="user_table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Họ tên</th>
                        <th>Email</th>
                        <th>Số điện thoại</th>
                        <th>Vai trò</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = mysqli_fetch_assoc($users_result)): ?>
                        <tr>
                            <td>#<?= $user['user_id'] ?></td>
                            <td><strong><?= htmlspecialchars($user['full_name']) ?></strong></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['phone']) ?></td>
                            <td>
                                <span class="badge <?= $user['role'] ?>">
                                    <?= ucfirst($user['role']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?= $user['status'] ?>">
                                    <?= ucfirst($user['status']) ?>
                                </span>
                            </td>
                            <td><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                            <td>
                                <div class="action_btns">
                                    <button class="btn edit" onclick="openEditModal(<?= htmlspecialchars(json_encode($user)) ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                        <a href="?toggle_status=<?= $user['user_id'] ?>" 
                                           class="btn <?= $user['status']=='active' ? 'block' : 'unblock' ?>"
                                           onclick="return confirm('Xác nhận thay đổi trạng thái?')">
                                            <i class="fas fa-<?= $user['status']=='active' ? 'ban' : 'check' ?>"></i>
                                        </a>

                                        <a href="?delete=<?= $user['user_id'] ?>" 
                                           class="btn delete"
                                           onclick="return confirm('Bạn có chắc muốn xóa người dùng này?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty_state">
                <i class="fas fa-users-slash"></i>
                <p>Không tìm thấy người dùng nào</p>
            </div>
        <?php endif; ?>

    </div>

</main>

<!-- MODAL SỬA THÔNG TIN -->
<div id="editModal" class="modal">
    <div class="modal_content">
        <h3><i class="fas fa-user-edit"></i> Sửa thông tin người dùng</h3>
        
        <form method="POST">
            <input type="hidden" name="user_id" id="edit_user_id">
            
            <div class="form_group">
                <label>Họ tên</label>
                <input type="text" name="full_name" id="edit_full_name" required>
            </div>

            <div class="form_group">
                <label>Số điện thoại</label>
                <input type="text" name="phone" id="edit_phone">
            </div>

            <div class="form_group">
                <label>Vai trò</label>
                <select name="role" id="edit_role">
                    <option value="customer">Customer</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <div class="form_group">
                <label>Trạng thái</label>
                <select name="status" id="edit_status">
                    <option value="active">Active</option>
                    <option value="banned">Banned</option>
                </select>
            </div>

            <div class="modal_btns">
                <button type="submit" name="update_user" class="save">
                    <i class="fas fa-save"></i> Lưu thay đổi
                </button>
                <button type="button" class="cancel" onclick="closeEditModal()">
                    <i class="fas fa-times"></i> Hủy
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(user) {
    document.getElementById('edit_user_id').value = user.user_id;
    document.getElementById('edit_full_name').value = user.full_name;
    document.getElementById('edit_phone').value = user.phone;
    document.getElementById('edit_role').value = user.role;
    document.getElementById('edit_status').value = user.status;
    
    document.getElementById('editModal').classList.add('active');
}

function closeEditModal() {
    document.getElementById('editModal').classList.remove('active');
}

// Đóng modal khi click ra ngoài
document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeEditModal();
    }
});
</script>

</body>
</html>