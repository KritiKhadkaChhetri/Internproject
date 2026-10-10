<?php
// index.php - home page (navbar, session info and helpers come from header.php)
session_start();
require_once "db.php";

$pageTitle = "Ecommerce | Shop Now";
require_once "header.php";
?>

  <main>
    <section class="hero">
      <h1>Now it's easier than ever to shop products</h1>
      <p>Ecommerce is a large Shoping application, with lot of Products.</p>

      <a href="products.php" class="cta" id="getStarted" style="text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
        Get started
        <svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true">
          <path fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
        </svg>
      </a>
    </section>
  </main>

</body>
</html>