<?php
// checkout.php - login required, saves the order, updates stock, clears the cart
session_start();
require_once "db.php";

const VAT_RATE = 0.13;

// ---------- Must be logged in ----------
// ---------- Must be logged in ----------
$userId = (int)($_SESSION['id'] ?? $_SESSION['user_id'] ?? 0);
if ($userId <= 0) {
    $_SESSION['cart_msg'] = "Please log in to checkout.";
    header("Location: login.php");
    exit;
}



// ---------- Build cart contents + totals from the database ----------
function build_cart(mysqli $conn, bool $lock = false): array {
    $cart = $_SESSION['cart'] ?? [];
    $out  = ['items' => [], 'subtotal' => 0, 'sale' => 0, 'discount' => 0,
             'vat' => 0, 'total' => 0, 'discountCount' => 0];
    if (empty($cart)) return $out;

    $ids = array_keys($cart);
    $ph  = implode(',', array_fill(0, count($ids), '?'));
    $sql = "SELECT product_id AS id,
                   product_name AS name,
                   price,
                   IF(on_sale = 1 AND old_price > price, old_price, price) AS original_price,
                   image,
                   stock
            FROM products
            WHERE product_id IN ($ph)" . ($lock ? " FOR UPDATE" : "");
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        $row['qty'] = (int)$cart[$row['id']];
        $out['items'][] = $row;
        $out['subtotal'] += $row['original_price'] * $row['qty'];
        $out['sale']     += $row['price'] * $row['qty'];
        if ($row['original_price'] > $row['price']) $out['discountCount']++;
    }
    $out['discount'] = $out['subtotal'] - $out['sale'];
    $out['vat']      = $out['sale'] * VAT_RATE;
    $out['total']    = $out['sale'] + $out['vat'];
    return $out;
}

// ---------- Place the order ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'place') {
    $conn->begin_transaction();
    try {
        $c = build_cart($conn, true);   // lock the product rows while we check stock

        if (empty($c['items'])) {
            throw new RuntimeException("Your cart is empty.");
        }
        foreach ($c['items'] as $it) {
            if ($it['qty'] > (int)$it['stock']) {
                throw new RuntimeException("Only " . (int)$it['stock'] . " left of " . $it['name'] . ". Please update your cart.");
            }
        }

        // 1) order header
        $sub = round($c['subtotal'], 2);
        $dis = round($c['discount'], 2);
        $vat = round($c['vat'], 2);
        $tot = round($c['total'], 2);
        $stmt = $conn->prepare("INSERT INTO orders (user_id, subtotal, discount, vat, total) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("idddd", $userId, $sub, $dis, $vat, $tot);
        $stmt->execute();
        $orderId = $conn->insert_id;

        // 2) order lines + stock
        $lineStmt  = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stockStmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE product_id = ? AND stock >= ?");

        foreach ($c['items'] as $it) {
            $pid = (int)$it['id'];
            $qty = (int)$it['qty'];
            $prc = (float)$it['price'];

            $lineStmt->bind_param("iiid", $orderId, $pid, $qty, $prc);
            $lineStmt->execute();

            $stockStmt->bind_param("iii", $qty, $pid, $qty);
            $stockStmt->execute();
            if ($stockStmt->affected_rows !== 1) {
                throw new RuntimeException("Sorry, " . $it['name'] . " just went out of stock.");
            }
        }

        $conn->commit();
        $_SESSION['cart'] = [];
        header("Location: checkout.php?placed=" . $orderId);
        exit;

    } catch (Throwable $ex) {
        $conn->rollback();
        if ($ex instanceof RuntimeException) {
            $_SESSION['cart_msg'] = $ex->getMessage();
        } else {
            error_log("Checkout failed: " . $ex->getMessage());
            $_SESSION['cart_msg'] = "Something went wrong placing your order. Please try again.";
        }
        header("Location: cart.php");
        exit;
    }
}

// ---------- Confirmation view ----------
$placed = null;
$placedId = (int)($_GET['placed'] ?? 0);
if ($placedId > 0) {
    $stmt = $conn->prepare("SELECT id, total, created_at FROM orders WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $placedId, $userId);
    $stmt->execute();
    $placed = $stmt->get_result()->fetch_assoc();
}

// ---------- Summary view ----------
$c = build_cart($conn);
if (!$placed && empty($c['items'])) {
    $_SESSION['cart_msg'] = "Your cart is empty.";
    header("Location: cart.php");
    exit;
}

$pageTitle = "Checkout | Ecommerce";
require_once "header.php";
?>

<style>
  .co-wrap { max-width: 760px; margin: 0 auto; padding: 40px 20px; }
  .co-title { text-align: center; font-size: 34px; margin: 10px 0 30px; }
  .co-box { border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; background: #fff; }
  .co-user { color: #6b7280; margin-bottom: 16px; }

  .co-item { display: flex; align-items: center; gap: 14px; padding: 10px 0; border-bottom: 1px solid #f3f4f6; }
  .co-item img { width: 50px; height: 58px; object-fit: cover; background: #f3f4f6; }
  .co-item .co-name { flex: 1; }
  .co-item .co-qty { color: #6b7280; }

  .co-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 17px; color: #374151; }
  .co-row.total { font-weight: bold; font-size: 20px; border-top: 1px solid #e5e7eb; margin-top: 8px; padding-top: 12px; }

  .co-actions { display: flex; justify-content: space-between; align-items: center; margin-top: 24px; }
  .co-back { color: #374151; }
  .co-btn { background: #374151; color: #fff; border: none; padding: 14px 28px; border-radius: 4px; font-size: 18px; cursor: pointer; }
  .co-btn:hover { background: #1f2937; }

  .co-done { text-align: center; }
  .co-done h2 { color: #166534; }
  .co-done a { display: inline-block; margin-top: 16px; background: #374151; color: #fff; padding: 12px 24px; border-radius: 4px; text-decoration: none; }
</style>

<main class="co-wrap">

<?php if ($placed) { ?>

  <div class="co-box co-done">
    <h2>✅ Order placed!</h2>
    <p>Thank you, <?php echo e($_SESSION['username'] ?? ''); ?>. Your order number is <strong>#<?php echo (int)$placed['id']; ?></strong>.</p>
    <p>Total paid: <strong><?php echo money($placed['total']); ?></strong></p>
    <a href="products.php">Continue shopping</a>
  </div>

<?php } else { ?>

  <h1 class="co-title">Checkout</h1>

  <div class="co-box">
    <p class="co-user">Ordering as <strong><?php echo e($_SESSION['username'] ?? ''); ?></strong></p>

    <?php foreach ($c['items'] as $it) { ?>
      <div class="co-item">
        <img src="<?php echo e(IMG_PATH . $it['image']); ?>" alt="">
        <span class="co-name"><?php echo e($it['name']); ?></span>
        <span class="co-qty">× <?php echo (int)$it['qty']; ?></span>
        <span><?php echo money($it['price'] * $it['qty']); ?></span>
      </div>
    <?php } ?>

    <div style="margin-top:14px">
      <div class="co-row"><span>Subtotal</span><span><?php echo money($c['subtotal']); ?></span></div>
      <div class="co-row"><span>VAT</span><span><?php echo money($c['vat']); ?></span></div>
      <div class="co-row"><span>Discount</span><span>-<?php echo money($c['discount']); ?></span></div>
      <div class="co-row total"><span>Total</span><span><?php echo money($c['total']); ?></span></div>
    </div>

    <div class="co-actions">
      <a href="cart.php" class="co-back">&larr; Back to cart</a>
      <form method="post">
        <input type="hidden" name="action" value="place">
        <button type="submit" class="co-btn">Place order</button>
      </form>
    </div>
  </div>

<?php } ?>

</main>

</body>
</html>