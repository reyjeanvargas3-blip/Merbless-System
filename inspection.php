<?php
session_start();
if (empty($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/conn.php';

$pdo = getDbConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_inspection'])) {
    $gownId = (int) ($_POST['gown_id'] ?? 0);
    $inspector = trim($_POST['inspector'] ?? '');
    $inspectionDate = $_POST['inspection_date'] ?? '';
    $result = trim($_POST['result'] ?? 'Pending');
    $notes = trim($_POST['notes'] ?? '');

    if ($gownId > 0 && $inspector !== '' && $inspectionDate !== '') {
        $stmt = $pdo->prepare('INSERT INTO inspections (gown_id, inspector, inspection_date, result, notes) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$gownId, $inspector, $inspectionDate, $result, $notes]);
    }
}

if (isset($_GET['delete_id'])) {
    $deleteId = (int) $_GET['delete_id'];
    $pdo->prepare('DELETE FROM inspections WHERE id = ?')->execute([$deleteId]);
    header('Location: inspection.php');
    exit;
}

$gowns = $pdo->query('SELECT * FROM gowns ORDER BY name')->fetchAll();
$inspections = $pdo->query(
    'SELECT i.*, g.name AS gown_name FROM inspections i JOIN gowns g ON g.id = i.gown_id ORDER BY i.inspection_date DESC'
)->fetchAll();
$pageTitle = 'Inspection';
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
            <h2 class="page-title">Inspection Checklist</h2>

            <div class="form-box">
                <h3>Add Inspection</h3>
                <form method="post">
                    <div class="form-grid">
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
                            <label>Inspector</label>
                            <input type="text" name="inspector" required>
                        </div>
                        <div>
                            <label>Date</label>
                            <input type="date" name="inspection_date" required>
                        </div>
                        <div>
                            <label>Result</label>
                            <select name="result">
                                <option value="Passed">Passed</option>
                                <option value="Needs Alteration">Needs Alteration</option>
                                <option value="Pending">Pending</option>
                            </select>
                        </div>
                        <div>
                            <label>Notes</label>
                            <textarea rows="3" name="notes" placeholder="Inspection notes"></textarea>
                        </div>
                    </div>
                    <button type="submit" name="save_inspection" value="1">Save Inspection</button>
                </form>
            </div>

            <div class="table-wrap" style="margin-top: 28px;">
                <table>
                    <thead>
                        <tr>
                            <th>Gown</th>
                            <th>Inspector</th>
                            <th>Date</th>
                            <th>Result</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$inspections): ?>
                            <tr>
                                <td colspan="5">No inspections found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($inspections as $inspection): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($inspection['gown_name']); ?></td>
                                    <td><?php echo htmlspecialchars($inspection['inspector']); ?></td>
                                    <td><?php echo htmlspecialchars($inspection['inspection_date']); ?></td>
                                    <td><span class="badge <?php echo ($inspection['result'] === 'Passed') ? 'success' : (($inspection['result'] === 'Needs Alteration') ? 'warning' : 'info'); ?>"><?php echo htmlspecialchars($inspection['result']); ?></span></td>
                                    <td>
                                        <form method="get" action="inspection.php" style="margin:0;">
                                            <input type="hidden" name="delete_id" value="<?php echo (int) $inspection['id']; ?>">
                                            <button type="submit" class="danger-btn" onclick="return confirm('Delete this inspection?');">Delete</button>
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
