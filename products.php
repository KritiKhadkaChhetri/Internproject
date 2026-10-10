<?php
session_start();
require_once "db.php";

// Allowed sort options (never put user input straight into ORDER BY)
$sorts = [
    'default'    => ['label' => 'Default',            'sql' => 'product_id ASC'],
    'price_asc'  => ['label' => 'Price: low to high', 'sql' => 'price ASC'],
    'price_desc' => ['label' => 'Price: high to low', 'sql' => 'price DESC'],
    'name'       => ['label' => 'Name: A to Z',       'sql' => 'product_name ASC'],
];

$sort = $_GET['sort'] ?? 'default';
if (!isset($sorts[$sort])) {
    $sort = 'default';
}

$q = trim($_GET['q'] ?? '');

$sql = "SELECT product_id, product_name, price, old_price, on_sale, stock, image FROM products";
if ($q !== '') {
    $sql .= " WHERE product_name LIKE ?";
}
$sql .= " ORDER BY " . $sorts[$sort]['sql'];

$stmt = $conn->prepare($sql);
if ($q !== '') {
    $like = "%" . $q . "%";
    $stmt->bind_param("s", $like);
}
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$pageTitle = "Products | Ecommerce";
require_once "header.php";
?>

  <main class="page">
    <h1 class="page-title">Our products</h1>

    <form method="GET" class="toolbar">
      <input type="search" name="q" value="<?php echo e($q); ?>" placeholder="Search products..." aria-label="Search products" />

      <select name="sort" aria-label="Sort products" onchange="this.form.submit()">
        <?php foreach ($sorts as $key => $s) { ?>
          <option value="<?php echo e($key); ?>" <?php echo $key === $sort ? 'selected' : ''; ?>>
            <?php echo e($s['label']); ?>
          </option>
        <?php } ?>
      </select>

      <button type="submit">Search</button>
    </form>

    <?php if (empty($products)) { ?>

      <p class="empty-note">
        No products found<?php echo $q !== '' ? ' for "' . e($q) . '"' : ''; ?>.
        <?php if ($q !== '') { ?><a href="products.php">Show all products</a><?php } ?>
      </p>

    <?php } else { ?>

      <div class="product-grid">
        <?php foreach ($products as $p) { render_product_card($p); } ?>
      </div>

    <?php } ?>
  </main>

</body>
</html>