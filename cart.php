<?php
// cart.php - shopping cart (navbar, e(), money(), IMG_PATH come from header.php)
session_start();
require_once "db.php";

const VAT_RATE = 0.13;
$_SESSION['cart'] ??= [];

// ---------- Handle button actions ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id     = (int)($_POST['product_id'] ?? 0);

    if ($action === 'clear') {
        $_SESSION['cart'] = [];
    } elseif ($action === 'remove') {
        unset($_SESSION['cart'][$id]);
    } elseif (($action === 'inc' || $action === 'dec') && isset($_SESSION['cart'][$id])) {
        $qty = $_SESSION['cart'][$id] + ($action === 'inc' ? 1 : -1);

        $stmt = $conn->prepare("SELECT stock FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $max = $row ? max(1, (int)$row['stock']) : 1;

        $_SESSION['cart'][$id] = max(1, min($qty, $max));
    }
    header('Location: cart.php');
    exit;
}

// ---------- Load cart products ----------
$items = [];
if (!empty($_SESSION['cart'])) {
    $ids          = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $conn->prepare("
        SELECT product_id AS id,
               product_name AS name,
               price,
               IF(on_sale = 1 AND old_price > price, old_price, price) AS original_price,
               image,
               stock
        FROM products
        WHERE product_id IN ($placeholders)
    ");
    $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $row['qty'] = $_SESSION['cart'][$row['id']];
        $items[] = $row;
    }
}

// ---------- Totals ----------
$subtotal = 0;       // sum of original prices
$saleTotal = 0;      // sum of selling prices
$discountCount = 0;

foreach ($items as $it) {
    $subtotal  += $it['original_price'] * $it['qty'];
    $saleTotal += $it['price'] * $it['qty'];
    if ($it['original_price'] > $it['price']) $discountCount++;
}
$discount = $subtotal - $saleTotal;
$vat      = $saleTotal * VAT_RATE;
$total    = $saleTotal + $vat;

$pageTitle = "Your Cart | Ecommerce";
require_once "header.php";
?>

<style>
  .ct-wrap { max-width: 960px; margin: 0 auto; padding: 40px 20px; }
  .ct-title { text-align: center; font-size: 34px; margin: 10px 0 40px; }

  .ct-item { display: flex; align-items: center; gap: 20px; border: 1px solid #e5e7eb;
             padding: 8px 16px 8px 8px; margin-bottom: 12px; background: #fff; }
  .ct-item img { width: 70px; height: 80px; object-fit: cover; flex-shrink: 0; background: #f3f4f6; }
  .ct-info { flex: 1; min-width: 0; }
  .ct-name { font-size: 18px; margin-bottom: 4px; }
  .ct-new { font-weight: bold; margin-right: 8px; }
  .ct-old { color: #9ca3af; text-decoration: line-through; }

  .ct-qty { display: flex; align-items: center; gap: 10px; }
  .ct-qty button { width: 32px; height: 32px; border: 1px solid #d1d5db; background: #f9fafb;
                   border-radius: 6px; font-size: 18px; cursor: pointer; line-height: 1; }
  .ct-qty button:hover { background: #e5e7eb; }
  .ct-qty span { min-width: 24px; text-align: center; font-weight: bold; }

  .ct-trash { background: none; border: none; cursor: pointer; font-size: 20px; color: #6b7280; }
  .ct-trash:hover { color: #dc2626; }

  .ct-summary { max-width: 480px; margin: 40px 0 0 auto; border-top: 1px solid #f3f4f6; padding-top: 30px; }
  .ct-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 18px; color: #374151; }
  .ct-row.total { font-weight: bold; font-size: 20px; }
  .ct-badge { display: inline-block; background: #eef2ff; color: #4338ca; padding: 6px 14px;
              border-radius: 999px; font-size: 14px; margin-top: 12px; }
  .ct-actions { display: flex; justify-content: space-between; align-items: center; margin-top: 24px; }
  .ct-clear { background: none; border: 1px solid #d1d5db; padding: 12px 18px; border-radius: 4px;
              cursor: pointer; font-size: 16px; color: #374151; }
  .ct-clear:hover { background: #f3f4f6; }
  .ct-checkout { display: inline-block; background: #374151; color: #fff; padding: 14px 26px;
                 border-radius: 4px; text-decoration: none; font-size: 18px; }
  .ct-checkout:hover { background: #1f2937; }
  .ct-empty { text-align: center; color: #6b7280; font-size: 18px; }
</style>

<main class="ct-wrap">
  <h1 class="ct-title">Your Cart</h1>

  <?php if (empty($items)) { ?>
    <p class="ct-empty">Your cart is empty. <a href="products.php">Continue shopping</a></p>
  <?php } else { ?>

    <?php foreach ($items as $it) { ?>
      <div class="ct-item">
        <img src="<?php echo e(IMG_PATH . $it['image']); ?>" alt="<?php echo e($it['name']); ?>">

        <div class="ct-info">
          <div class="ct-name"><?php echo e($it['name']); ?></div>
          <span class="ct-new"><?php echo money($it['price']); ?></span>
          <?php if ($it['original_price'] > $it['price']) { ?>
            <span class="ct-old"><?php echo money($it['original_price']); ?></span>
          <?php } ?>
        </div>

        <div class="ct-qty">
          <form method="post">
            <input type="hidden" name="product_id" value="<?php echo (int)$it['id']; ?>">
            <input type="hidden" name="action" value="dec">
            <button type="submit" aria-label="Decrease quantity">−</button>
          </form>
          <span><?php echo (int)$it['qty']; ?></span>
          <form method="post">
            <input type="hidden" name="product_id" value="<?php echo (int)$it['id']; ?>">
            <input type="hidden" name="action" value="inc">
            <button type="submit" aria-label="Increase quantity">+</button>
          </form>
        </div>

        <form method="post">
          <input type="hidden" name="product_id" value="<?php echo (int)$it['id']; ?>">
          <input type="hidden" name="action" value="remove">
          <button class="ct-trash" type="submit" title="Remove">🗑</button>
        </form>
      </div>
    <?php } ?>

    <div class="ct-summary">
      <div class="ct-row"><span>Subtotal</span><span><?php echo money($subtotal); ?></span></div>
      <div class="ct-row"><span>VAT</span><span><?php echo money($vat); ?></span></div>
      <div class="ct-row"><span>Discount</span><span>-<?php echo money($discount); ?></span></div>
      <div class="ct-row total"><span>Total</span><span><?php echo money($total); ?></span></div>

      <?php if ($discountCount > 0) { ?>
        <div style="text-align:right">
          <span class="ct-badge">🎟 <?php echo $discountCount; ?> Discount<?php echo $discountCount > 1 ? 's' : ''; ?> Applied</span>
        </div>
      <?php } ?>

      <div class="ct-actions">
        <form method="post">
          <input type="hidden" name="action" value="clear">
          <button type="submit" class="ct-clear">Clear cart</button>
        </form>
        <a href="checkout.php" class="ct-checkout">Proceed to Checkout</a>
      </div>
    </div>

  <?php } ?>
</main>

</body>
</html>