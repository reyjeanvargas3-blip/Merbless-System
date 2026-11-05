<?php
session_start();
if (empty($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/conn.php';

$pdo = getDbConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_fitting'])) {
    $clientId = (int) ($_POST['client_id'] ?? 0);
    $gownId = (int) ($_POST['gown_id'] ?? 0);
    $date = $_POST['fitting_date'] ?? '';
    $time = $_POST['fitting_time'] ?? '';
    $notes = trim($_POST['notes'] ?? '');
    $status = trim($_POST['status'] ?? 'Pending');

    if ($clientId > 0 && $date !== '' && $time !== '') {
        $stmt = $pdo->prepare('INSERT INTO fittings (client_id, gown_id, fitting_date, fitting_time, notes, status) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$clientId, $gownId ?: null, $date, $time, $notes, $status]);
    }
}

if (isset($_GET['delete_id'])) {
    $deleteId = (int) $_GET['delete_id'];
    $pdo->prepare('DELETE FROM fittings WHERE id = ?')->execute([$deleteId]);
    header('Location: fitting.php');
    exit;
}

$clients = $pdo->query('SELECT * FROM clients ORDER BY name')->fetchAll();
$gowns = $pdo->query('SELECT * FROM gowns ORDER BY name')->fetchAll();
$fittings = $pdo->query(
    'SELECT f.*, c.name AS client_name, g.name AS gown_name FROM fittings f LEFT JOIN clients c ON c.id = f.client_id LEFT JOIN gowns g ON g.id = f.gown_id ORDER BY f.fitting_date DESC, f.fitting_time DESC'
)->fetchAll();
$pageTitle = 'Fittings';
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
            <h2 class="page-title">Fitting Schedule</h2>

            <div class="form-box">
                <h3>Book Fitting</h3>
                <form method="post">
                    <div class="form-grid">
                        <div>
                            <label>Client</label>
                            <select name="client_id" required>
                                <option value="">Select Client</option>
                                <?php foreach ($clients as $client): ?>
                                    <option value="<?php echo (int) $client['id']; ?>"><?php echo htmlspecialchars($client['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label>Gown (Optional)</label>
                            <select name="gown_id">
                                <option value="">None</option>
                                <?php foreach ($gowns as $gown): ?>
                                    <option value="<?php echo (int) $gown['id']; ?>"><?php echo htmlspecialchars($gown['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label>Date</label>
                            <input type="date" name="fitting_date" required>
                        </div>
                        <div>
                            <label>Time</label>
                            <input type="time" name="fitting_time" required>
                        </div>
                        <div>
                            <label>Status</label>
                            <select name="status">
                                <option value="Pending">Pending</option>
                                <option value="Scheduled">Scheduled</option>
                                <option value="Completed">Completed</option>
                            </select>
                        </div>
                        <div>
                            <label>Notes</label>
                            <textarea rows="3" name="notes" placeholder="Fit adjustments needed"></textarea>
                        </div>
                    </div>
                    <button type="submit" name="save_fitting" value="1">Save Fitting</button>
                </form>
            </div>

            <div class="table-wrap" style="margin-top: 28px;">
                <table>
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Gown</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$fittings): ?>
                            <tr>
                                <td colspan="6">No fittings found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($fittings as $fitting): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($fitting['client_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($fitting['gown_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($fitting['fitting_date']); ?></td>
                                    <td><?php echo htmlspecialchars($fitting['fitting_time']); ?></td>
                                    <td><span class="badge <?php echo ($fitting['status'] === 'Completed') ? 'success' : (($fitting['status'] === 'Scheduled') ? 'warning' : 'info'); ?>"><?php echo htmlspecialchars($fitting['status']); ?></span></td>
                                    <td>
                                        <form method="get" action="fitting.php" style="margin:0;">
                                            <input type="hidden" name="delete_id" value="<?php echo (int) $fitting['id']; ?>">
                                            <button type="submit" class="danger-btn" onclick="return confirm('Delete this fitting?');">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
