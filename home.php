<?php
session_start();

if (empty($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$pageTitle = 'Home';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="style.css?v=brown-logo">
</head>
<body>
    <header>
        <div class="container topbar">
            <div class="brand">Merbless Shop</div>
            <nav>
                <a href="home.php">Home</a>
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
        <div class="container home-page">
            <section class="home-hero">
                <div class="home-copy">
                    <span class="eyebrow">Elegant bridal boutique</span>
                    <h1>Dress your dream day in timeless beauty.</h1>
                    <p>
                        Merbless Shop offers premium bridal gowns and formalwear designed to make every celebration feel
                        magical. From graceful silhouettes to statement details, each dress is carefully selected to match your
                        story and style.
                    </p>
                    <div class="home-actions">
                        <a class="small-link" href="gowns.php">Browse Gowns</a>
                        <a class="small-link secondary" href="dashboard.php">Go to Dashboard</a>
                    </div>
                </div>

                <div class="home-visual">
                    <img class="home-logo-image" src="logo%20(3).jpg" alt="Merbless Dress Shop and Tailoring logo" />
                </div>
            </section>

            <section class="feature-grid">
                <div class="feature-card">
                    <h3>Luxury Style</h3>
                    <p>Beautiful gowns crafted for weddings, pageants, and unforgettable special occasions.</p>
                </div>
                <div class="feature-card">
                    <h3>Perfect Fit</h3>
                    <p>Every detail is designed to help clients feel confident, comfortable, and radiant.</p>
                </div>
                <div class="feature-card">
                    <h3>Shop Management</h3>
                    <p>Track clients, fittings, inventory, and reservations from one organized system.</p>
                </div>
            </section>
        </div>
    </main>
</body>
</html>
