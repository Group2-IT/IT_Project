<?php
$conn = new mysqli('localhost', 'root', '', 'vegetable_database');
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}
$products = $conn->query("SELECT p.*, f.f_name, f.l_name FROM product p LEFT JOIN farmer f ON p.f_id = f.f_id WHERE p.qty_in_stock > 0 ORDER BY p.pdt_name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Vegetable Sales And Marketing Information System - Vegetables In Stock</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f4f7f1; color: #1b3c1a; }
        header { background: linear-gradient(135deg, #1b5e20 0%, #0a3b0e 100%); color: white; text-align: center; padding: 2rem 1rem; position: relative; }
        header h1 { margin: 0; font-size: 2rem; }
        nav { background: rgba(255,255,255,0.95); backdrop-filter: blur(6px); border-bottom: 1px solid rgba(46,125,50,0.2); }
        nav ul { list-style: none; display: flex; justify-content: center; flex-wrap: wrap; max-width: 1200px; margin: 0 auto; padding: 0.75rem 0; }
        nav ul li { margin: 0 0.5rem; }
        nav ul li a { color: #1b3b1a; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 0.5rem; }
        nav ul li a:hover { color: #2e7d32; }
        .container { max-width: 1200px; margin: 2rem auto; padding: 0 1.5rem; display: grid; gap: 1.5rem; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); }
        .card { background: white; border-radius: 18px; overflow: hidden; box-shadow: 0 16px 40px rgba(0,0,0,0.08); transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .card:hover { transform: translateY(-4px); box-shadow: 0 22px 45px rgba(0,0,0,0.12); }
        .card-content { padding: 1.5rem; }
        .card-content h3 { margin-bottom: 0.75rem; font-size: 1.2rem; color: #145a20; }
        .price { font-size: 1.1rem; font-weight: 700; color: #1b5e20; margin-bottom: 0.85rem; }
        .meta { font-size: 0.94rem; color: #4f6253; line-height: 1.6; }
        .meta span { display: block; margin-top: 0.35rem; }
        .no-stock { text-align: center; color: #556c56; padding: 2.5rem; background: white; border-radius: 18px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); }
        footer { text-align: center; padding: 2rem 1rem; color: #d0e6c8; background: #10210f; }
        @media (max-width: 640px) { nav ul { flex-direction: column; gap: 0.5rem; } }
    </style>
</head>
<body>
<header>
    <h1>Vegetables In Stock</h1>
    <p style="opacity:0.82; margin-top: 0.75rem;">Live stock data from our farmers, updated from the same source used in the vendor dashboard.</p>
</header>
<nav>
    <ul>
        <li><a href="index.html"><i class="fas fa-home"></i> Home</a></li>
        <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
        <li><a href="vendor_dashboard.php"><i class="fas fa-store"></i> Vendor Dashboard</a></li>
    </ul>
</nav>
<main>
    <?php $showOrderButton = false; ?>
    <?php include 'stock_cards.php'; ?>
</main>
<footer>
    <p>&copy; 2026 Online Vegetable Sales And Marketing Information System</p>
</footer>
</body>
</html>
<?php $conn->close(); ?>
