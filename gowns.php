<?php
session_start();
if (empty($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/conn.php';

$pdo = getDbConnection();

$uploadDir = __DIR__ . '/uploads/gowns';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_gown'])) {
    $name = trim($_POST['name'] ?? '');
    $style = trim($_POST['style'] ?? '');
    $size = trim($_POST['size'] ?? '');
    $price = (float) ($_POST['price'] ?? 0);
    $status = trim($_POST['status'] ?? 'Available');
    $imagePath = null;

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['image'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $fileType = mime_content_type($file['tmp_name']);
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (in_array($fileType, $allowedTypes, true) || in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
            $safeName = time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
            $destination = $uploadDir . '/' . $safeName;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $imagePath = 'uploads/gowns/' . $safeName;
            }
        }
    }

    if ($name !== '' && $style !== '' && $size !== '') {
        $stmt = $pdo->prepare('INSERT INTO gowns (name, style, size, price, status, image) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$name, $style, $size, $price, $status, $imagePath]);
    }
}

if (isset($_GET['delete_id'])) {
    $deleteId = (int) $_GET['delete_id'];
    $stmt = $pdo->prepare('SELECT image FROM gowns WHERE id = ?');
    $stmt->execute([$deleteId]);
    $gown = $stmt->fetch();

    if ($gown && !empty($gown['image'])) {
        $filePath = __DIR__ . '/' . $gown['image'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    $pdo->prepare('DELETE FROM gowns WHERE id = ?')->execute([$deleteId]);
    header('Location: gowns.php');
    exit;
}

$gowns = $pdo->query('SELECT * FROM gowns ORDER BY id DESC')->fetchAll();
$pageTitle = 'Gowns';
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
            <h2 class="page-title">Gowns Inventory</h2>

            <div class="form-box">
                <h3>Add Gown</h3>
                <form method="post" enctype="multipart/form-data">
                    <div class="form-grid">
                        <div>
                            <label>Gown Name</label>
                            <input type="text" name="name" placeholder="Enter gown name" required>
                        </div>
                        <div>
                            <label>Style</label>
                            <select name="style">
                                <option value="Ball Gown">Ball Gown</option>
                                <option value="Wedding Gown">Wedding Gown</option>
                                <option value="Infinity Gown">Infinity Gown</option>
                                <option value="A-Line">A-Line</option>
                                <option value="Filipiniana Dress">Filipiniana Dress</option>
                            </select>
                        </div>
                        <div>
                            <label>Size</label>
                            <input type="text" name="size" placeholder="e.g. 8" required>
                        </div>
                        <div>
                            <label>Price</label>
                            <input type="number" name="price" step="0.01" min="0" required>
                        </div>
                        <div>
                            <label>Status</label>
                            <select name="status">
                                <option value="Available">Available</option>
                                <option value="Reserved">Reserved</option>
                                <option value="In Shop">In Shop</option>
                            </select>
                        </div>
                        <div>
                            <label>Gown Picture</label>
                            <input type="file" name="image" accept="image/*">
                        </div>
                    </div>
                    <button type="submit" name="save_gown" value="1">Save Gown</button>
                </form>
            </div>

            <div class="table-wrap" style="margin-top: 28px;">
                <table>
                    <thead>
                        <tr>
                            <th>Picture</th>
                            <th>Name</th>
                            <th>Style</th>
                            <th>Size</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$gowns): ?>
                            <tr>
                                <td colspan="7">No gowns found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($gowns as $gown): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($gown['image'])): ?>
                                            <img src="<?php echo htmlspecialchars($gown['image']); ?>" alt="<?php echo htmlspecialchars($gown['name']); ?>" class="gown-image">
                                        <?php else: ?>
                                            <span class="muted">No image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($gown['name']); ?></td>
                                    <td><?php echo htmlspecialchars($gown['style']); ?></td>
                                    <td><?php echo htmlspecialchars($gown['size']); ?></td>
                                    <td>₱<?php echo number_format((float) $gown['price'], 2); ?></td>
                                    <td><span class="badge <?php echo $gown['status'] === 'Available' ? 'success' : ($gown['status'] === 'Reserved' ? 'warning' : 'info'); ?>"><?php echo htmlspecialchars($gown['status']); ?></span></td>
                                    <td>
                                        <form method="get" action="gowns.php" style="margin:0;">
                                            <input type="hidden" name="delete_id" value="<?php echo (int) $gown['id']; ?>">
                                            <button type="submit" class="danger-btn" onclick="return confirm('Delete this gown?');">Delete</button>
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
