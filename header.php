<?php
// header.php - shared <head>, navbar and product card.
// Include it AFTER session_start() and require_once "db.php".

if (!defined('IMG_PATH')) {
    define('IMG_PATH', 'images/');   // folder that holds product images
}

if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('money')) {
    function money($n) {
        return "Rs. " . number_format((float)$n, 2);
    }
}

if (!function_exists('render_product_card')) {
    function render_product_card($p) {
        $stock = (int)$p['stock'];
        $hasOld = $p['old_price'] !== null && (float)$p['old_price'] > (float)$p['price'];
        ?>
        <article class="product-card">
          <?php if (!empty($p['on_sale'])) { ?>
            <span class="sale-tag">Sale</span>
          <?php } ?>

          <div class="product-img">
            <img src="<?php echo e(IMG_PATH . $p['image']); ?>" alt="<?php echo e($p['product_name']); ?>" loading="lazy" />
          </div>

          <div class="product-info">
            <h3 class="product-name"><?php echo e($p['product_name']); ?></h3>

            <p class="product-price">
              <span class="price-now"><?php echo money($p['price']); ?></span>
              <?php if ($hasOld) { ?>
                <span class="price-old"><?php echo money($p['old_price']); ?></span>
              <?php } ?>
            </p>

            <?php if ($stock > 0 && $stock <= 3) { ?>
              <p class="stock-note">Only <?php echo $stock; ?> left</p>
            <?php } ?>

            <?php if ($stock > 0) { ?>
              <form action="addtocart.php" method="POST">
                <input type="hidden" name="product_id" value="<?php echo (int)$p['product_id']; ?>" />
                <button class="add-btn" type="submit">Add to cart</button>
              </form>
            <?php } else { ?>
              <button class="add-btn" type="button" disabled>Out of stock</button>
            <?php } ?>
          </div>
        </article>
        <?php
    }
}

$cartCount = (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) ? array_sum($_SESSION['cart']) : 0;
$pageTitle = $pageTitle ?? "Ecommerce | Shop Now";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?php echo e($pageTitle); ?></title>
  <link rel="stylesheet" href="styles.css" />
</head>
<body>
  <header class="navbar">
    <a href="index.php" class="brand">Ecommerce <span class="divider">|</span> Shop Now</a>

    <nav class="nav-links">
      <a href="products.php" class="nav-item">
        <span class="badge <?php echo $cartCount === 0 ? 'hidden' : ''; ?>"><?php echo (int)$cartCount; ?></span>
        products
      </a>

  <?php if (isset($_SESSION['username'])) { ?>
  <a href="#" class="nav-item">Hi, <?php echo e($_SESSION['username']); ?></a>
  <a href="logout.php" class="nav-item">Logout</a>
<?php } else { ?>
  <a href="login.php" class="nav-item">Login</a>
<?php } ?>

      <a href="cart.php" class="nav-item cart">
        Cart
        <svg viewBox="0 0 24 24" width="28" height="28" aria-hidden="true">
          <path fill="currentColor" d="M7 18a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM1 2v2h2l3.6 7.59-1.35 2.45A2 2 0 0 0 7 17h12v-2H7.42a.25.25 0 0 1-.22-.37L8.1 13h7.45a2 2 0 0 0 1.75-1.03l3.58-6.49A1 1 0 0 0 20 4H5.21l-.94-2H1zm16 16a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/>
        </svg>
      </a>
    </nav>
  </header>

  <?php if (!empty($_SESSION['cart_msg'])) { ?>
    <p class="flash" role="alert"><?php echo e($_SESSION['cart_msg']); unset($_SESSION['cart_msg']); ?></p>
  <?php } ?>