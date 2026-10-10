<?php
session_start();
require_once "db.php";

function e($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

// Already logged in? Go to the shop.
if (isset($_SESSION['id'])) {
    header("Location: products.php");
    exit();
}

$error = "";
$email = "";
$success = isset($_GET['registered']) ? "Account created. Please log in." : "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? "");
    $password = $_POST['password'] ?? "";

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === "") {
        $error = "Please enter your email and password.";
    } else {
        // SELECT * so this works with or without a "role" column
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);   // stops session fixation; the cart is kept

            $_SESSION['id'] = (int)$user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'] ?? 'customer';   // defaults to customer if no role column

            header("Location: products.php");
            exit();
        }

        // Same message for "no such email" and "wrong password"
        $error = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login | Ecommerce</title>
  <link rel="stylesheet" href="login.css" />
</head>
<body>
  <main class="login-page">
    <section class="login-left">
      <a href="index.php" class="back-link">&larr; Back to shop</a>

      <div class="login-box">
        <h1>Get started today!</h1>
        <p class="login-text">
          Lorem ipsum dolor sit amet consectetur adipisicing elit. Et libero nulla
          eaque error neque ipsa culpa autem, at itaque nostrum!
        </p>

        <form method="POST" novalidate>
          <div class="field">
            <label class="sr-only" for="email">Email</label>
            <input id="email" name="email" type="email" placeholder="Enter email"
                   value="<?php echo e($email); ?>" autocomplete="email" />
          </div>

          <div class="field">
            <label class="sr-only" for="password">Password</label>
            <input id="password" name="password" type="password" placeholder="Enter password"
                   autocomplete="current-password" />
          </div>

          <?php if ($success !== "") { ?>
            <p class="form-success" role="status"><?php echo e($success); ?></p>
          <?php } ?>
          <p class="form-error" role="alert"><?php echo e($error); ?></p>

          <div class="form-bottom">
            <p class="switch-text">
              <span>You Don't have an account ?</span><a href="register.php">Signup</a>
            </p>
            <button type="submit" class="login-btn">Login</button>
          </div>
        </form>
      </div>
    </section>

    <aside class="login-image" aria-hidden="true"></aside>
  </main>
</body>
</html>