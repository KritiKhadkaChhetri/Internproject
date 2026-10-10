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
$username = "";
$email = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? "");
    $email = trim($_POST['email'] ?? "");
    $password = $_POST['password'] ?? "";

    if ($username === "") {
        $error = "Please enter a username.";
    } elseif (mb_strlen($username) > 100) {
        $error = "Username must be 100 characters or fewer.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        // Is the email already used?
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();

        if ($stmt->get_result()->num_rows > 0) {
            $error = "An account with this email already exists.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            try {
                $stmt = $conn->prepare(
                    "INSERT INTO users (username, email, password) VALUES (?, ?, ?)"
                );
                $stmt->bind_param("sss", $username, $email, $hash);
                $stmt->execute();

                header("Location: login.php?registered=1");
                exit();

            } catch (mysqli_sql_exception $ex) {
                // 1062 = duplicate email (two people signing up at the same moment)
                if ($ex->getCode() === 1062) {
                    $error = "An account with this email already exists.";
                } else {
                    error_log("Register failed: " . $ex->getMessage());
                    $error = "Something went wrong. Please try again.";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Signup | Ecommerce</title>
  <link rel="stylesheet" href="login.css" />
</head>
<body>
  <main class="login-page">
    <section class="login-left">
      <a href="index.php" class="back-link">&larr; Back to shop</a>

      <div class="login-box">
        <h1>Create your account</h1>
        <p class="login-text">
          Join us to track your orders and check out faster.
        </p>

        <form method="POST" novalidate>
          <div class="field">
            <label class="sr-only" for="username">Username</label>
            <input id="username" name="username" type="text" placeholder="Enter username"
                   value="<?php echo e($username); ?>" autocomplete="username" maxlength="100" />
          </div>

          <div class="field">
            <label class="sr-only" for="email">Email</label>
            <input id="email" name="email" type="email" placeholder="Enter email"
                   value="<?php echo e($email); ?>" autocomplete="email" />
          </div>

          <div class="field">
            <label class="sr-only" for="password">Password</label>
            <input id="password" name="password" type="password" placeholder="Enter password"
                   autocomplete="new-password" />
          </div>

          <p class="form-error" role="alert"><?php echo e($error); ?></p>

          <div class="form-bottom">
            <p class="switch-text">
              <span>Already have an account ?</span><a href="login.php">Login</a>
            </p>
            <button type="submit" class="login-btn">Signup</button>
          </div>
        </form>
      </div>
    </section>

    <aside class="login-image" aria-hidden="true"></aside>
  </main>
</body>
</html>