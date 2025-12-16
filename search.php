<?php
include 'connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_id = $_SESSION['user_id'] ?? null;

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tìm kiếm sách</title>

    <link rel="stylesheet" href="CSS/style.css">
    <link rel="stylesheet" href="CSS/header.css">

    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

<?php include 'header.php'; ?>

<!-- ===== SEARCH ===== -->
<section class="search_cont">
    <form method="post" class="search_form" autocomplete="off" onsubmit="return submitSearch();">
        <input type="text" name="search" id="search_input" placeholder="🔍 Nhập tên sách..." required>

        <!-- Gợi ý sẽ hiển thị ở đây (vẫn nằm trong form để vị trí chuẩn) -->
        <div id="suggest_box" class="suggest_box" aria-hidden="true"></div>

        <input type="submit" name="submit" value="Tìm kiếm" class="search_btn">
    </form>
</section>

<!-- ===== PRODUCTS ===== -->
<section class="products_cont">
    <div class="pro_box_cont">

        <?php
        if(isset($_POST['submit'])){

            $search = mysqli_real_escape_string($ocon, $_POST['search']);

            $query = mysqli_query($ocon,"
                SELECT b.*, MIN(i.url) AS image
                FROM books b
                LEFT JOIN images i ON b.book_id = i.book_id
                WHERE b.title LIKE '%$search%' AND b.status = 'active'
                GROUP BY b.book_id
            ");

            if(mysqli_num_rows($query) > 0){

                while($row = mysqli_fetch_assoc($query)){
        ?>

<form method="post" class="pro_box">

    <span class="badge hot">Hot</span>

    <img src="<?php echo $row['image'] ?: 'img/no-image.png'; ?>">

    <!-- NÚT MUA NGAY CHỈ HIỆN KHI HOVER -->
    <a href="checkout.php?id=<?php echo $row['book_id']; ?>" class="buy_now_btn">
        MUA NGAY
    </a>

    <h3><?php echo htmlspecialchars($row['title']); ?></h3>

    <div class="price_row">
        <span class="price">
            <?php echo number_format($row['discounted_price'],0,',','.'); ?>₫
        </span>

        <input type="number" name="quantity" min="1" value="1" class="qty_input">
    </div>

    <input type="hidden" name="book_id" value="<?php echo $row['book_id']; ?>">

    <div class="product_actions">

        <a href="book_detail.php?id=<?php echo $row['book_id']; ?>" class="product_btn detail_btn">
            <i class="fas fa-eye"></i> Chi tiết
        </a>

<button type="submit" name="add_to_cart" class="product_btn add_to_cart_btn">
    <i class="fas fa-shopping-cart"></i> Thêm vào giỏ
</button>

    </div>

</form>

        <?php
                }
            } else {
                echo "<p class='empty'>❌ Không tìm thấy sách nào</p>";
            }

        } else {
            echo "<p class='empty'>📚 Nhập từ khóa để tìm sách</p>";
        }
        ?>

    </div>
</section>

<?php include 'footer.php'; ?>
<script>
// ---- Debounce helper để giảm request khi gõ ----
function debounce(fn, delay){
    let t;
    return function(...args){
        clearTimeout(t);
        t = setTimeout(()=> fn.apply(this,args), delay);
    }
}

const input = document.getElementById('search_input');
const suggestBox = document.getElementById('suggest_box');

async function fetchSuggestions(keyword){
    if (!keyword || keyword.trim().length === 0) {
        suggestBox.innerHTML = '';
        suggestBox.style.display = 'none';
        return;
    }

    // POST form data
    const form = new FormData();
    form.append('keyword', keyword.trim());

    try {
        const res = await fetch('search_suggest.php', {
            method: 'POST',
            body: form
        });
        const html = await res.text();

        if (html && html.trim().length > 0) {
            suggestBox.innerHTML = html;
            suggestBox.style.display = 'block';
            suggestBox.setAttribute('aria-hidden','false');
        } else {
            suggestBox.innerHTML = '';
            suggestBox.style.display = 'none';
        }
    } catch (err) {
        console.error('Fetch suggest error:', err);
        suggestBox.style.display = 'none';
    }
}

// Debounced handler (wait 220ms after user stops typing)
const onKey = debounce(function(e){
    fetchSuggestions(e.target.value);
}, 220);

input.addEventListener('input', onKey);

// Hide suggestions when clicking outside
document.addEventListener('click', function(e){
    if (!document.querySelector('.search_form').contains(e.target)) {
        suggestBox.style.display = 'none';
    }
});

// Called when user clicks a suggestion
function selectSuggestion(text){
    input.value = text;
    suggestBox.style.display = 'none';
    // auto-submit the form after selecting suggestion:
    document.querySelector('.search_form').submit();
}

// When user hits Enter / clicks search button, keep existing POST behavior.
// This function prevents double submission when autocomplete auto-submits.
function submitSearch(){
    // allow form to submit normally (POST) — no JS redirect
    return true;
}
</script>
</body>
</html>