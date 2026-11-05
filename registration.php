<?php
session_start();

require_once __DIR__ . '/conn.php';
$pdo = getDbConnection();
$isAdmin = !empty($_SESSION['admin_id']);
$adminCount = (int) $pdo->query('SELECT COUNT(*) FROM admin_users')->fetchColumn();
$error = '';
$username = '';
$registered = isset($_GET['registered']) && $_GET['registered'] === '1';

if ($adminCount > 0 && !$isAdmin) {
    header('Location: index.php');
    exit;
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameInput = $_POST['username'] ?? '';
    $passwordInput = $_POST['password'] ?? '';
    $confirmationInput = $_POST['password_confirmation'] ?? '';
    $csrfInput = $_POST['csrf_token'] ?? '';
    $username = is_string($usernameInput) ? trim($usernameInput) : '';
    $password = is_string($passwordInput) ? $passwordInput : '';
    $passwordConfirmation = is_string($confirmationInput) ? $confirmationInput : '';
    $csrfToken = is_string($csrfInput) ? $csrfInput : '';

    if (!hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        $error = 'Your session expired. Please try again.';
    } elseif (!preg_match('/^[A-Za-z0-9._-]{3,50}$/', $username)) {
        $error = 'Username must be 3 to 50 characters and may contain letters, numbers, periods, underscores, and hyphens.';
    } elseif (strlen($password) < 12 || strlen($password) > 72) {
        $error = 'Password must be between 12 and 72 bytes.';
    } elseif ($password !== $passwordConfirmation) {
        $error = 'The passwords do not match.';
    } else {
        $lockAcquired = (int) $pdo->query("SELECT GET_LOCK('merbless_shop_admin_reg', 5)")->fetchColumn();

        if ($lockAcquired !== 1) {
            $error = 'Admin registration is temporarily busy. Please try again.';
        } else {
            $redirectTo = null;

            try {
                $adminCount = (int) $pdo->query('SELECT COUNT(*) FROM admin_users')->fetchColumn();

                if ($adminCount > 0 && !$isAdmin) {
                    $redirectTo = 'index.php';
                } else {
                    $stmt = $pdo->prepare('SELECT id FROM admin_users WHERE username = ? LIMIT 1');
                    $stmt->execute([$username]);

                    if ($stmt->fetch()) {
                        $error = 'That username is already in use.';
                    } else {
                        $stmt = $pdo->prepare('INSERT INTO admin_users (username, password_hash) VALUES (?, ?)');
                        $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);
                        $redirectTo = $adminCount === 0
                            ? 'index.php?registered=1'
                            : 'registration.php?registered=1';
                    }
                }
            } finally {
                $pdo->query("SELECT RELEASE_LOCK('merbless_shop_admin_reg')");
            }

            if ($redirectTo !== null) {
                header('Location: ' . $redirectTo);
                exit;
            }
        }
    }
}

$pageTitle = 'Admin Registration | Merbless Shop';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="style.css?v=brown-theme">
</head>
<body class="login-wrap">
    <div class="login-box">
        <h2>Admin Registration</h2>
        <p class="muted">
            <?php echo $adminCount === 0 ? 'Create the first administrator account.' : 'Create an administrator account.'; ?>
        </p>

        <?php if ($registered): ?>
            <p role="status">Admin account created successfully.</p>
        <?php endif; ?>
        <?php if ($error !== ''): ?>
            <p role="alert"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>

        <form action="registration.php" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

            <label for="username">Username</label>
            <input id="username" name="username" type="text" minlength="3" maxlength="50" value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>" autocomplete="username" required>

            <label for="password">Password (12-72 bytes)</label>
            <input id="password" name="password" type="password" minlength="12" maxlength="72" autocomplete="new-password" required>

            <label for="password_confirmation">Confirm Password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" minlength="12" maxlength="72" autocomplete="new-password" required>

            <button type="submit">Register Admin</button>
        </form>

        <p><a href="index.php">Back to Login</a></p>
    </div>
</body>
</html>
