<?php
session_start();
if (empty($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/conn.php';

$pdo = getDbConnection();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_client'])) {
    $id = (int) ($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $preferredStyle = trim($_POST['preferred_style'] ?? '');
    $fittingDate = $_POST['fitting_date'] ?? null;
    $fittingTime = $_POST['fitting_time'] ?? null;
    $fittingStatus = trim($_POST['fitting_status'] ?? 'Pending');
    $fittingNotes = trim($_POST['fitting_notes'] ?? '');

    if ($id > 0 && $name !== '' && $phone !== '' && $email !== '') {
        $stmt = $pdo->prepare('UPDATE clients SET name = ?, phone = ?, email = ?, preferred_style = ?, fitting_date = ?, fitting_time = ?, fitting_status = ?, fitting_notes = ? WHERE id = ?');
        $stmt->execute([$name, $phone, $email, $preferredStyle, $fittingDate ?: null, $fittingTime ?: null, $fittingStatus, $fittingNotes, $id]);
        header('Location: clients.php');
        exit;
    }
}

$client = null;
if ($id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM clients WHERE id = ?');
    $stmt->execute([$id]);
    $client = $stmt->fetch();
}

if (!$client) {
    header('Location: clients.php');
    exit;
}

$pageTitle = 'Edit Client';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="style.css?v=brown-theme">
</head>
<body>
    <header>
        <div class="container topbar">
            <div class="brand">Merbless Shop</div>
            <nav>
                <a href="dashboard.php">Dashboard</a>
                <a href="clients.php">Clients</a>
                <a href="gowns.php">Gowns</a>
                <a href="fitting.php">Fittings</a>
                <a href="inspection.php">Inspection</a>
                <a href="reservation.php">Reservations</a>
                <a href="index.php?action=logout">Logout</a>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <h2 class="page-title">Edit Client</h2>

            <div class="form-box">
                <form method="post" action="edit.php">
                    <input type="hidden" name="id" value="<?php echo (int) $client['id']; ?>">

                    <div class="form-grid">
                        <div>
                            <label>Client Name</label>
                            <input type="text" name="name" value="<?php echo htmlspecialchars($client['name']); ?>" required>
                        </div>
                        <div>
                            <label>Phone</label>
                            <input type="tel" name="phone" value="<?php echo htmlspecialchars($client['phone']); ?>" required>
                        </div>
                        <div>
                            <label>Email</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($client['email']); ?>" required>
                        </div>
                        <div>
                            <label>Preferred Style</label>
                            <select name="preferred_style">
                                <?php $styles = ['Ball Gown', 'A-line', 'Sheath', 'Mermaid']; ?>
                                <?php foreach ($styles as $style): ?>
                                    <option value="<?php echo htmlspecialchars($style); ?>" <?php echo ($client['preferred_style'] === $style) ? 'selected' : ''; ?>><?php echo htmlspecialchars($style); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label>Fitting Date</label>
                            <input type="date" name="fitting_date" value="<?php echo htmlspecialchars($client['fitting_date'] ?: ''); ?>">
                        </div>
                        <div>
                            <label>Fitting Time</label>
                            <input type="time" name="fitting_time" value="<?php echo htmlspecialchars($client['fitting_time'] ?: ''); ?>">
                        </div>
                        <div>
                            <label>Fitting Status</label>
                            <select name="fitting_status">
                                <?php $statuses = ['Pending', 'Scheduled', 'Completed']; ?>
                                <?php foreach ($statuses as $status): ?>
                                    <option value="<?php echo htmlspecialchars($status); ?>" <?php echo ($client['fitting_status'] === $status) ? 'selected' : ''; ?>><?php echo htmlspecialchars($status); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label>Fitting Notes</label>
                            <textarea name="fitting_notes" rows="3"><?php echo htmlspecialchars($client['fitting_notes'] ?: ''); ?></textarea>
                        </div>
                    </div>

                    <button type="submit" name="update_client" value="1">Update Client</button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
