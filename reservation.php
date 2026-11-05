<?php
session_start();
if (empty($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/conn.php';

$pdo = getDbConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_reservation'])) {
    $clientId = (int) ($_POST['client_id'] ?? 0);
    $gownId = (int) ($_POST['gown_id'] ?? 0);
    $eventDate = $_POST['event_date'] ?? '';
    $status = trim($_POST['status'] ?? 'Pending');

    if ($clientId > 0 && $gownId > 0 && $eventDate !== '') {
        $stmt = $pdo->prepare('INSERT INTO reservations (client_id, gown_id, event_date, status) VALUES (?, ?, ?, ?)');
        $stmt->execute([$clientId, $gownId, $eventDate, $status]);
    }
}

if (isset($_GET['delete_id'])) {
    $deleteId = (int) $_GET['delete_id'];
    $pdo->prepare('DELETE FROM reservations WHERE id = ?')->execute([$deleteId]);
    header('Location: reservation.php');
    exit;
}

$clients = $pdo->query('SELECT * FROM clients ORDER BY name')->fetchAll();
$gowns = $pdo->query('SELECT * FROM gowns ORDER BY name')->fetchAll();
$reservations = $pdo->query(
    'SELECT r.*, c.name AS client_name, g.name AS gown_name FROM reservations r JOIN clients c ON c.id = r.client_id JOIN gowns g ON g.id = r.gown_id ORDER BY r.event_date DESC'
)->fetchAll();
$pageTitle = 'Reservations';
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
            <h2 class="page-title">Reservations</h2>

            <div class="form-box">
                <h3>Add Reservation</h3>
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
                            <label>Gown</label>
                            <select name="gown_id" required>
                                <option value="">Select Gown</option>
                                <?php foreach ($gowns as $gown): ?>
                                    <option value="<?php echo (int) $gown['id']; ?>"><?php echo htmlspecialchars($gown['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label>Event Date</label>
                            <input type="date" name="event_date" required>
                        </div>
                        <div>
                            <label>Status</label>
                            <select name="status">
                                <option value="Pending">Pending</option>
                                <option value="Confirmed">Confirmed</option>
                                <option value="On Hold">On Hold</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" name="save_reservation" value="1">Save Reservation</button>
                </form>
            </div>

            <div class="table-wrap" style="margin-top: 28px;">
                <table>
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Gown</th>
                            <th>Event Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$reservations): ?>
                            <tr>
                                <td colspan="5">No reservations found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($reservations as $reservation): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($reservation['client_name']); ?></td>
                                    <td><?php echo htmlspecialchars($reservation['gown_name']); ?></td>
                                    <td><?php echo htmlspecialchars($reservation['event_date']); ?></td>
                                    <td><span class="badge <?php echo ($reservation['status'] === 'Confirmed') ? 'success' : (($reservation['status'] === 'On Hold') ? 'info' : 'warning'); ?>"><?php echo htmlspecialchars($reservation['status']); ?></span></td>
                                    <td>
                                        <form method="get" action="reservation.php" style="margin:0;">
                                            <input type="hidden" name="delete_id" value="<?php echo (int) $reservation['id']; ?>">
                                            <button type="submit" class="danger-btn" onclick="return confirm('Delete this reservation?');">Delete</button>
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
