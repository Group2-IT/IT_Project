<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'farmer') {
    header("Location: login.php");
    exit();
}
$farmer_name = htmlspecialchars($_SESSION['username']);
$farmer_id   = htmlspecialchars($_SESSION['user_id']);

$conn = new mysqli("localhost", "root", "", "vegetable_database");
if ($conn->connect_error) die("Connection failed.");

// Fetch this farmer's products
$sql = "SELECT * FROM product WHERE f_id = '$farmer_id' ORDER BY pdt_id DESC";
$products = $conn->query($sql);

// Count orders involving this farmer
$ord_sql = "SELECT COUNT(*) as cnt FROM orders WHERE f_id = '$farmer_id'";
$ord_res = $conn->query($ord_sql);
$order_count = $ord_res->fetch_assoc()['cnt'];

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8faf6;
            color: #1a2e1f;
            line-height: 1.5;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #e0e8dc;
        }
        ::-webkit-scrollbar-thumb {
            background: #2e7d32;
            border-radius: 10px;
        }

        /* Header - clean gradient */
        header {
            background: linear-gradient(135deg, #1b5e20 0%, #0a3b0e 100%);
            color: white;
            text-align: center;
            padding: 2rem 1.5rem;
            position: relative;
            overflow: hidden;
        }
        header::before {
            content: "🌿";
            font-size: 180px;
            opacity: 0.08;
            position: absolute;
            bottom: -30px;
            right: -20px;
            pointer-events: none;
        }
        header h1 {
            font-size: 2rem;
            font-weight: 700;
            letter-spacing: -0.3px;
            max-width: 900px;
            margin: 0 auto;
            text-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        /* Navigation - Sidebar style */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            width: 280px;
            background: linear-gradient(135deg, #f57c00 0%, #e65100 100%);
            box-shadow: 4px 0 15px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            overflow-y: auto;
            transform: translateX(-100%);
            transition: transform 0.3s ease;
        }
        .sidebar.active {
            transform: translateX(0);
        }

        .sidebar-header {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
        }
        .sidebar-header h3 {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .sidebar-header p {
            font-size: 0.85rem;
            opacity: 0.9;
        }

        .sidebar-menu {
            list-style: none;
            padding: 1.5rem 0;
        }
        .sidebar-menu li {
            margin: 0;
        }
        .sidebar-menu li a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 1rem 1.5rem;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.25s ease;
            border-left: 4px solid transparent;
        }
        .sidebar-menu li a i {
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }
        .sidebar-menu li a:hover {
            background: rgba(255, 255, 255, 0.1);
            border-left-color: #ffb74d;
            padding-left: 1.8rem;
        }
        .sidebar-menu li a.active {
            background: rgba(255, 183, 77, 0.2);
            border-left-color: #ffb74d;
            color: #fff;
        }

        .sidebar-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            background: rgba(0, 0, 0, 0.2);
        }
        .sidebar-footer a {
            display: flex;
            align-items: center;
            gap: 12px;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            font-weight: 600;
            padding: 0.75rem 1rem;
            background: rgba(211, 47, 47, 0.2);
            border-radius: 8px;
            transition: all 0.25s ease;
        }
        .sidebar-footer a:hover {
            background: rgba(211, 47, 47, 0.4);
            color: #fff;
        }

        .sidebar-toggle {
            position: fixed;
            top: 1.5rem;
            left: 1.5rem;
            z-index: 999;
            background: #f57c00;
            color: white;
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 12px;
            font-size: 1.5rem;
            cursor: pointer;
            display: none;
            align-items: center;
            justify-content: center;
            transition: all 0.25s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .sidebar-toggle:hover {
            background: #e65100;
            transform: scale(1.05);
        }

        body.sidebar-open {
            overflow: hidden;
        }
        body.sidebar-open .sidebar-overlay {
            display: block;
        }

        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            display: none;
        }

        nav {
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(4px);
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid rgba(46, 125, 50, 0.2);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            display: none;
        }

        /* Main container spacing */
        .main-container {
            max-width: 1280px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }

        /* Card style for all sections */
        .modern-card {
            background: white;
            border-radius: 28px;
            box-shadow: 0 10px 30px -12px rgba(0, 0, 0, 0.08);
            padding: 2rem 2.2rem;
            margin-bottom: 2.5rem;
            transition: transform 0.2s, box-shadow 0.2s;
            border: 1px solid rgba(0, 0, 0, 0.03);
        }
        .modern-card:hover {
            box-shadow: 0 20px 35px -12px rgba(0, 0, 0, 0.12);
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 1.5rem;
            border-left: 5px solid #2e7d32;
            padding-left: 1.2rem;
        }
        .section-title i {
            font-size: 1.8rem;
            color: #2e7d32;
        }
        .section-title h2 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1a3b1a;
            margin: 0;
            letter-spacing: -0.3px;
        }

        .welcome-text {
            font-size: 1.08rem;
            line-height: 1.6;
            color: #2c3e2b;
            margin-bottom: 1rem;
        }

        /* Dashboard specific styles */
        .user-info {
            background: rgba(255, 255, 255, 0.95);
            padding: 0.5rem 1.5rem;
            border-bottom: 1px solid rgba(46, 125, 50, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.9rem;
        }
        .user-info .user-details {
            color: #1f3b1a;
            font-weight: 600;
        }
        .user-info .logout-link {
            color: #d32f2f;
            text-decoration: none;
            font-weight: 600;
            padding: 0.25rem 0.75rem;
            border-radius: 6px;
            transition: all 0.25s ease;
        }
        .user-info .logout-link:hover {
            background: #ffebee;
            color: #b71c1c;
        }

        .stat-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .stat-card {
            background: white;
            border-radius: 14px;
            padding: 1.2rem 1.5rem;
            box-shadow: 0 4px 16px rgba(0,0,0,0.06);
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .stat-icon {
            width: 46px; height: 46px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem;
        }
        .stat-icon.green  { background: #e8f5e9; color: #2e7d32; }
        .stat-icon.blue   { background: #e3f2fd; color: #1565c0; }
        .stat-value { font-size: 1.6rem; font-weight: 700; color: #1a3b1a; }
        .stat-label { font-size: 0.82rem; color: #888; }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .action-card {
            background: white;
            border-radius: 14px;
            padding: 1.5rem;
            box-shadow: 0 4px 16px rgba(0,0,0,0.06);
            text-decoration: none;
            color: inherit;
            border: 2px solid transparent;
            transition: 0.25s;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .action-card:hover {
            border-color: #4caf50;
            transform: translateY(-2px);
            box-shadow: 0 10px 24px rgba(0,0,0,0.1);
        }
        .action-icon {
            width: 44px; height: 44px;
            background: #e8f5e9;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem;
            color: #2e7d32;
            flex-shrink: 0;
        }
        .action-title { font-weight: 600; font-size: 0.95rem; color: #1a3b1a; }
        .action-desc  { font-size: 0.8rem; color: #888; margin-top: 2px; }

        table { width:100%; border-collapse:collapse; font-size: 0.9rem; }
        th, td { padding: 10px 12px; text-align: left; border-bottom: 1px solid #f0f0f0; }
        th { background: #f5faf3; color: #2e7d32; font-weight: 600; font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.5px; }
        tr:hover td { background: #fafff9; }

        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .badge.high   { background: #e8f5e9; color: #2e7d32; }
        .badge.medium { background: #fff8e1; color: #f57f17; }
        .badge.low    { background: #ffebee; color: #c62828; }

        /* Footer refined */
        footer {
            background: #0f2a0c;
            color: #d0e6c8;
            text-align: center;
            padding: 2rem 1rem;
            margin-top: 2rem;
            font-size: 0.85rem;
            border-top: 1px solid #2a5a24;
        }

        /* responsive */
        @media (max-width: 768px) {
            .modern-card {
                padding: 1.5rem;
            }
            .section-title h2 {
                font-size: 1.4rem;
            }
            header h1 {
                font-size: 1.4rem;
            }
            .sidebar-toggle {
                display: flex;
            }
            .sidebar {
                width: 100%;
                max-width: 280px;
            }
            .sidebar-footer {
                position: relative;
                margin-top: 2rem;
            }
        }
        @media (max-width: 480px) {
            .section-title {
                flex-wrap: wrap;
            }
            header h1 {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>

<button class="sidebar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h3><i class="fas fa-tractor"></i> Farmer</h3>
        <p><?php echo htmlspecialchars($farmer_name); ?></p>
    </div>
    <ul class="sidebar-menu">
        <li><a href="index.html"><i class="fas fa-home"></i> Home</a></li>
        <li><a href="farmer_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
    <div class="sidebar-footer">
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</aside>

<header>
    <h1>ONLINE VEGETABLE SALES AND MARKETING INFORMATION SYSTEM</h1>
</header>

<div class="user-info">
    <div class="user-details">
        <i class="fas fa-tractor"></i> Farmer Dashboard - Welcome, <?php echo $farmer_name; ?> (<?php echo $farmer_id; ?>)
    </div>
    <a href="logout.php" class="logout-link">Sign Out</a>
</div>

<div class="main-container">

    <div class="modern-card">
        <div class="section-title">
            <i class="fas fa-tractor"></i>
            <h2>Welcome back, <?php echo $farmer_name; ?>!</h2>
        </div>
        <p class="welcome-text">Manage your products and track orders from this dashboard.</p>
    </div>

    <div class="stat-row">
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-box"></i></div>
            <div>
                <div class="stat-value"><?php echo $products ? $products->num_rows : 0; ?></div>
                <div class="stat-label">Products listed</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-shopping-cart"></i></div>
            <div>
                <div class="stat-value"><?php echo $order_count; ?></div>
                <div class="stat-label">Orders received</div>
            </div>
        </div>
    </div>

    <div class="quick-actions">
        <a href="product.html" class="action-card">
            <div class="action-icon"><i class="fas fa-plus"></i></div>
            <div>
                <div class="action-title">Add New Product</div>
                <div class="action-desc">List a new vegetable for sale</div>
            </div>
        </a>
        <a href="view_products.php" class="action-card">
            <div class="action-icon"><i class="fas fa-list"></i></div>
            <div>
                <div class="action-title">View All Products</div>
                <div class="action-desc">See your current stock</div>
            </div>
        </a>
        <a href="farmer.html" class="action-card">
            <div class="action-icon"><i class="fas fa-user-edit"></i></div>
            <div>
                <div class="action-title">Update Profile</div>
                <div class="action-desc">Edit your farmer details</div>
            </div>
        </a>
    </div>

    <?php
    // Re-query since we closed the connection
    $conn2 = new mysqli("localhost", "root", "", "vegetable_database");
    $products2 = $conn2->query("SELECT * FROM product WHERE f_id = '$farmer_id' ORDER BY pdt_id DESC");
    ?>
    <div class="modern-card">
        <div class="section-title">
            <i class="fas fa-carrot"></i>
            <h2>My Products</h2>
        </div>
        <?php if ($products2 && $products2->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Product ID</th><th>Name</th><th>Type</th>
                    <th>Price (UGX)</th><th>Stock</th><th>Unit</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($p = $products2->fetch_assoc()):
                    $stock = $p['qty_in_stock'];
                    $level = $stock <= 10 ? 'low' : ($stock <= 50 ? 'medium' : 'high');
                ?>
                <tr>
                    <td><?php echo $p['pdt_id']; ?></td>
                    <td><?php echo htmlspecialchars($p['pdt_name']); ?></td>
                    <td><?php echo htmlspecialchars($p['pdt_type']); ?></td>
                    <td><?php echo number_format($p['price_per_unit']); ?></td>
                    <td><span class="badge <?php echo $level; ?>"><?php echo $stock; ?></span></td>
                    <td><?php echo htmlspecialchars($p['unit_of_measure']); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p style="color:#888;text-align:center;padding:2rem 0">No products listed yet. <a href="product.html" style="color:#2e7d32">Add your first product →</a></p>
        <?php endif; ?>
    </div>
    <?php $conn2->close(); ?>

</div>

<footer>
    <div style="max-width: 1200px; margin: 0 auto;">
        <p>&copy; 2026 Online Vegetable Sales And Marketing Information System</p>
        <p style="margin-top: 0.5rem; font-size: 0.8rem; opacity: 0.8;">
            Connecting farmers, vendors, and customers for fresh, quality vegetables across Uganda
        </p>
    </div>
</footer>

<script>
    // Sidebar toggle functionality
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            document.body.classList.toggle('sidebar-open');
        });
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('active');
            document.body.classList.remove('sidebar-open');
        });
    }

    // Close sidebar when a link is clicked
    const sidebarLinks = document.querySelectorAll('.sidebar-menu a');
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function() {
            sidebar.classList.remove('active');
            document.body.classList.remove('sidebar-open');
        });
    });

    // Highlight active menu item
    const currentPage = window.location.pathname.split('/').pop();
    sidebarLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href === currentPage || (currentPage === '' && href === 'farmer_dashboard.php')) {
            link.classList.add('active');
        }
    });
</script>

</body>
</html>
