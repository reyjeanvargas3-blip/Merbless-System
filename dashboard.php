<?php
session_start();
if (empty($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/conn.php';

$pdo = getDbConnection();

$totalClients = (int) $pdo->query('SELECT COUNT(*) FROM clients')->fetchColumn();
$totalGowns = (int) $pdo->query('SELECT COUNT(*) FROM gowns')->fetchColumn();
$availableGowns = (int) $pdo->query("SELECT COUNT(*) FROM gowns WHERE status = 'Available'")->fetchColumn();
$pendingFittings = (int) $pdo->query("SELECT COUNT(*) FROM fittings WHERE status = 'Pending'")->fetchColumn();
$totalReservations = (int) $pdo->query('SELECT COUNT(*) FROM reservations')->fetchColumn();

$recentActivity = $pdo->query(
    'SELECT c.name, ' . "'Reservation' AS type, r.status, r.event_date AS date_value FROM reservations r JOIN clients c ON c.id = r.client_id UNION ALL SELECT c.name, 'Fitting' AS type, f.status, f.fitting_date AS date_value FROM fittings f JOIN clients c ON c.id = f.client_id UNION ALL SELECT g.name, 'Inspection' AS type, i.result AS status, i.inspection_date AS date_value FROM inspections i JOIN gowns g ON g.id = i.gown_id ORDER BY date_value DESC LIMIT 6"
)->fetchAll();

$pageTitle = 'Dashboard';
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
                <a href="registration.php">Register Admin</a>
                <a href="index.php?action=logout">Logout</a>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="hero">
                <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['admin_user'] ?? 'Admin'); ?></h1>
                <p class="muted">Track your shop activity, client bookings, gown availability, and fitting schedule.</p>
            </div>

            <div class="grid">
                <div class="card stat">
                    <h3>Total Clients</h3>
                    <strong><?php echo $totalClients; ?></strong>
                </div>
                <div class="card stat">
                    <h3>Available Gowns</h3>
                    <strong><?php echo $availableGowns; ?></strong>
                </div>
                <div class="card stat">
                    <h3>Pending Fittings</h3>
                    <strong><?php echo $pendingFittings; ?></strong>
                </div>
                <div class="card stat">
                    <h3>Reservations</h3>
                    <strong><?php echo $totalReservations; ?></strong>
                </div>
            </div>

            <div class="card" style="margin-top: 28px;">
                <h3>Recent Activity</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Client / Item</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$recentActivity): ?>
                            <tr>
                                <td colspan="4">No recent activity yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentActivity as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($item['type'] ?? ''); ?></td>
                                    <td>
                                        <?php
                                            $status = $item['status'] ?? '';
                                            $badgeClass = 'info';
                                            if (stripos($status, 'confirmed') !== false || stripos($status, 'passed') !== false || stripos($status, 'completed') !== false) {
                                                $badgeClass = 'success';
                                            } elseif (stripos($status, 'pending') !== false || stripos($status, 'scheduled') !== false || stripos($status, 'warning') !== false) {
                                                $badgeClass = 'warning';
                                            }
                                        ?>
                                        <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($status); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars(date('F d, Y') ?? ''); ?></td>
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
  