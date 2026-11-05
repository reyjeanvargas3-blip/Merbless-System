<?php
session_start();
if (empty($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/conn.php';

$pdo = getDbConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_client'])) {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $preferredStyle = trim($_POST['preferred_style'] ?? '');
    $fittingDate = $_POST['fitting_date'] ?? null;
    $fittingTime = $_POST['fitting_time'] ?? null;
    $fittingStatus = trim($_POST['fitting_status'] ?? 'Pending');
    $fittingNotes = trim($_POST['fitting_notes'] ?? '');

    if ($name !== '' && $phone !== '' && $email !== '') {
        $stmt = $pdo->prepare('INSERT INTO clients (name, phone, email, preferred_style, fitting_date, fitting_time, fitting_status, fitting_notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$name, $phone, $email, $preferredStyle, $fittingDate ?: null, $fittingTime ?: null, $fittingStatus, $fittingNotes]);
    }
}

if (isset($_GET['delete_id'])) {
    $deleteId = (int) $_GET['delete_id'];
    $pdo->prepare('DELETE FROM clients WHERE id = ?')->execute([$deleteId]);
    header('Location: clients.php');
    exit;
}

$clients = $pdo->query('SELECT * FROM clients ORDER BY id DESC')->fetchAll();
$pageTitle = 'Clients';
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
            <h2 class="page-title">Clients</h2>

            <div class="form-box">
                <h3>Add Client</h3>
                <form method="post">
                    <div class="form-grid">
                        <div>
                            <label>First Name</label>
                            <input type="text" name="name" placeholder="First Name" required>
                        </div>
                        <div>
                            <label>Middle Name</label>
                            <input type="text" name="middle_name" placeholder="Middle Name" required>
                        </div>
                        <div>
                            <label>Last Name</label>
                            <input type="text" name="last_name" placeholder="Last Name" required>
                        </div>
                      
                        <div>
                            <label>Phone</label>
                            <input type="tel" name="phone" placeholder="Enter phone number" required>
                        </div>
                        <div>
                            <label>Email</label>
                            <input type="email" name="email" placeholder="Enter email" required>
                        </div>
                        <div>
                            <label>Preferred Style</label>
                            <select name="preferred_style">
                                <option value="Ball Gown">Ball Gown</option>
                                <option value="A-line">A-line</option>
                                <option value="Sheath">Sheath</option>
                                <option value="Mermaid">Mermaid</option>
                            </select>
                        </div>
                        <div>
                            <label>Fitting Date</label>
                            <input type="date" name="fitting_date">
                        </div>
                        <div>
                            <label>Fitting Time</label>
                            <input type="time" name="fitting_time">
                        </div>
                        <div>
                            <label>Fitting Status</label>
                            <select name="fitting_status">
                                <option value="Pending">Pending</option>
                                <option value="Scheduled">Scheduled</option>
                                <option value="Completed">Completed</option>
                            </select>
                        </div>
                        <div>
                            <label>Fitting Notes</label>
                            <textarea name="fitting_notes" rows="3" placeholder="Fit concerns / notes"></textarea>
                        </div>
                    </div>
                    <button type="submit" name="save_client" value="1">Save Client</button>
                </form>
            </div>

            <div class="table-wrap" style="margin-top: 28px;">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Style</th>
                            <th>Fitting Date</th>
                            <th>Fitting Time</th>
                            <th>Fitting Status</th>
                            <th>Notes</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$clients): ?>
                            <tr>
                                <td colspan="9">No clients found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($clients as $client): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($client['name']); ?></td>
                                    <td><?php echo htmlspecialchars($client['phone']); ?></td>
                                    <td><?php echo htmlspecialchars($client['email']); ?></td>
                                    <td><?php echo htmlspecialchars($client['preferred_style'] ?: 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($client['fitting_date'] ?: 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($client['fitting_time'] ?: 'N/A'); ?></td>
                                    <td><span class="badge <?php echo ($client['fitting_status'] === 'Completed') ? 'success' : (($client['fitting_status'] === 'Scheduled') ? 'warning' : 'info'); ?>"><?php echo htmlspecialchars($client['fitting_status'] ?: 'Pending'); ?></span></td>
                                    <td><?php echo htmlspecialchars($client['fitting_notes'] ?: 'N/A'); ?></td>
                                    <td>
                                        <div class="client-actions">
                                            <a href="edit.php?id=<?php echo (int) $client['id']; ?>" class="small-link client-action-btn">Edit</a>
                                            <form method="get" action="clients.php">
                                                <input type="hidden" name="delete_id" value="<?php echo (int) $client['id']; ?>">
                                                <button type="submit" class="danger-btn client-action-btn" onclick="return confirm('Delete this client?');">Delete</button>
                                            </form>
                                        </div>
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
