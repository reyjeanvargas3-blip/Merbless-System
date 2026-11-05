<?php
session_start();

require_once __DIR__ . '/conn.php';
$pdo = getDbConnection();
$adminCount = (int) $pdo->query('SELECT COUNT(*) FROM admin_users')->fetchColumn();
$loginError = '';

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit;
}

$pageTitle = 'Merbless Shop | Login';

if (!empty($_SESSION['admin_id'])) {
    header('Location: home.php');
    exit;
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameInput = $_POST['username'] ?? '';
    $passwordInput = $_POST['password'] ?? '';
    $csrfInput = $_POST['csrf_token'] ?? '';
    $username = is_string($usernameInput) ? trim($usernameInput) : '';
    $password = is_string($passwordInput) ? $passwordInput : '';
    $csrfToken = is_string($csrfInput) ? $csrfInput : '';

    if (!hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        $loginError = 'Your session expired. Please try again.';
    } else {
        $stmt = $pdo->prepare('SELECT id, username, password_hash FROM admin_users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int) $admin['id'];
            $_SESSION['admin_user'] = $admin['username'];
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            header('Location: home.php');
            exit;
        }

        $loginError = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="style.css?v=brown-theme">
</head>
<body class="login-wrap">
    <div class="login-box">
        <h2>Merbless Shop</h2>
        <p class="muted">Sign in to manage clients, gowns, fittings, and reservations.</p>
        <?php if (isset($_GET['registered']) && $_GET['registered'] === '1'): ?>
            <p role="status">Admin account created. You can now sign in.</p>
        <?php endif; ?>
        <?php if ($loginError !== ''): ?>
            <p role="alert"><?php echo htmlspecialchars($loginError, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>

        <form action="index.php" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
            <label for="username">Username</label>
            <input id="username" name="username" type="text" placeholder="Enter username" required>

            <label for="password">Password</label>
            <input id="password" name="password" type="password" placeholder="Enter password" required>

            <button type="submit">Login</button>
        </form>
        <?php if ($adminCount === 0): ?>
            <p><a href="registration.php">Create the first admin account</a></p>
        <?php endif; ?>
    </div>
</body>
</html>
