<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'vendor') {
    header("Location: login.php");
    exit();
}
$vendor_name = htmlspecialchars($_SESSION['username']);
$vendor_id   = htmlspecialchars($_SESSION['user_id']);

$conn = new mysqli("localhost", "root", "", "vegetable_database");
if ($conn->connect_error) die("Connection failed.");

function getProductImage($name) {
    $filename = 'pics/' . str_replace(' ', '_', strtolower($name)) . '.jpg';
    if (file_exists(__DIR__ . '/' . $filename)) {
        return $filename;
    }
    return 'pics/vegetables.jpg';
}

function getProductDescription($name) {
    $descriptions = [
        'Carrots' => 'Rich in vitamin A and great for healthy eyesight.',
        'Broccoli' => 'Packed with nutrients, antioxidants, and fiber.',
        'Tomatoes' => 'Fresh and juicy tomatoes perfect for salads and sauces.',
        'Cabbage' => 'Leafy green vegetable rich in vitamins and minerals.',
        'Green Pepper' => 'Fresh green peppers loaded with vitamin C and flavor.',
        'Onions' => 'Fresh onions with a pungent flavor.',
        'Garlic' => 'Aromatic garlic cloves great for cooking.',
        'Zucchini' => 'Versatile zucchini perfect for grilling or sautéing.',
    ];
    return $descriptions[$name] ?? '';
}

// All products in stock
$products = $conn->query("SELECT p.*, f.f_name, f.l_name FROM product p LEFT JOIN farmer f ON p.f_id = f.f_id WHERE p.qty_in_stock > 0 ORDER BY p.pdt_name ASC");

// This vendor's orders
$orders = $conn->query("SELECT o.*, p.pdt_name FROM orders o LEFT JOIN product p ON o.pdt_id = p.pdt_id WHERE o.v_id = '$vendor_id' ORDER BY o.order_date DESC");

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendor Dashboard</title>
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
            background: linear-gradient(135deg, #1565c0 0%, #0d47a1 100%);
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
            border-left-color: #42a5f5;
            padding-left: 1.8rem;
        }
        .sidebar-menu li a.active {
            background: rgba(66, 165, 245, 0.2);
            border-left-color: #42a5f5;
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
            background: #1565c0;
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
            background: #0d47a1;
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
        }
        nav ul li a i {
            font-size: 1rem;
            color: #2e7d32;
        }
        nav ul li a:hover {
            color: #1b5e20;
            background: #eef5e9;
            border-bottom-color: #2e7d32;
        }
        nav ul li a:active {
            transform: scale(0.97);
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

        /* Vendor specific styles */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-top: 1rem;
        }
        .product-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 16px rgba(0,0,0,0.06);
            border: 1px solid #f0f0f0;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .product-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
        }
        .product-image {
            width: 100%;
            height: 180px;
            border-radius: 18px;
            overflow: hidden;
            margin-bottom: 1rem;
            background: #f4fbf4;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .product-name {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1a3b1a;
            margin-bottom: 0.5rem;
        }
        .product-price {
            font-size: 1.05rem;
            font-weight: 700;
            color: #226622;
            margin-bottom: 0.75rem;
        }
        .product-description {
            font-size: 0.95rem;
            color: #4f4f4f;
            line-height: 1.5;
            margin-bottom: 1rem;
            min-height: 3rem;
        }
        .product-meta {
            font-size: 0.85rem;
            color: #666;
            line-height: 1.4;
            margin-bottom: 1rem;
        }
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.45);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2000;
        }
        .modal-overlay.show {
            display: flex;
        }
        .order-modal {
            width: min(540px, calc(100% - 2rem));
            background: white;
            border-radius: 18px;
            box-shadow: 0 24px 60px rgba(0,0,0,0.14);
            padding: 1.75rem;
            position: relative;
        }
        .order-modal h3 {
            margin: 0 0 1rem;
            font-size: 1.3rem;
            color: #1b5e20;
        }
        .order-modal label {
            display: block;
            margin-bottom: 0.5rem;
            color: #2f4f2f;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .order-modal input {
            width: 100%;
            padding: 0.9rem 1rem;
            border: 1px solid #dfe4de;
            border-radius: 12px;
            margin-bottom: 1rem;
            font-size: 0.95rem;
            color: #243a21;
        }
        .order-modal .modal-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .order-modal .modal-row.full {
            grid-template-columns: 1fr;
        }
        .order-modal .modal-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            flex-wrap: wrap;
        }
        .order-modal .btn-secondary,
        .order-modal .btn-primary {
            border: none;
            border-radius: 12px;
            padding: 0.9rem 1.2rem;
            font-size: 0.95rem;
            cursor: pointer;
        }
        .order-modal .btn-secondary {
            background: #f0f3ef;
            color: #2f4f2f;
        }
        .order-modal .btn-primary {
            background: #2e7d32;
            color: white;
        }
        .order-btn {
            display: inline-block;
            background: #2e7d32;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: background 0.25s;
        }
        .order-btn:hover {
            background: #1b5e20;
        }

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

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 2000;
            padding: 14px 18px;
            border-radius: 12px;
            background: rgba(46, 125, 50, 0.95);
            color: #fff;
            font-weight: 600;
            box-shadow: 0 18px 40px rgba(0,0,0,0.18);
            display: none;
            min-width: 260px;
        }
        .notification.show {
            display: block;
            animation: fadeIn 0.25s ease;
        }
    </style>
</head>
<body>

<button class="sidebar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h3><i class="fas fa-store"></i> Vendor</h3>
        <p><?php echo htmlspecialchars($vendor_name); ?></p>
    </div>
    <ul class="sidebar-menu">
        <li><a href="index.html"><i class="fas fa-home"></i> Home</a></li>
        <li><a href="vendor_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
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
        <i class="fas fa-store"></i> Vendor Dashboard - Welcome, <?php echo $vendor_name; ?> (<?php echo $vendor_id; ?>)
    </div>
    <a href="logout.php" class="logout-link">Sign Out</a>
</div>
       
        
    </style>
</head>
<body>

<header>
    <h1><i class="fas fa-store"></i> Vendor Dashboard</h1>
    <div style="display:flex;align-items:center;gap:12px">
        <span style="opacity:0.9;font-size:0.9rem">👤 <?php echo $vendor_name; ?> (<?php echo $vendor_id; ?>)</span>
        <a href="logout.php" class="logout-btn">Sign Out</a>
    </div>
</header>

<div class="main-container">

    <div class="modern-card">
        <div class="section-title">
            <i class="fas fa-store"></i>
            <h2>Welcome, <?php echo $vendor_name; ?>!</h2>
        </div>
        <p class="welcome-text">Browse available vegetables and place orders directly with farmers.</p>
    </div>

    <div class="quick-actions">
        <a href="#available-products" class="action-card">
            <div class="action-icon"><i class="fas fa-shopping-cart"></i></div>
            <div>
                <div class="action-title">Place New Order</div>
                <div class="action-desc">Order from available stock</div>
            </div>
        </a>
        <a href="vegetables_in_stock.html" class="action-card">
            <div class="action-icon"><i class="fas fa-carrot"></i></div>
            <div>
                <div class="action-title">View All Stock</div>
                <div class="action-desc">See what's available today</div>
            </div>
        </a>
        <a href="vendor.html" class="action-card">
            <div class="action-icon"><i class="fas fa-user-edit"></i></div>
            <div>
                <div class="action-title">Update Profile</div>
                <div class="action-desc">Edit your vendor details</div>
            </div>
        </a>
    </div>

    <!-- Available Products -->
    <div class="modern-card" id="available-products">
        <div class="section-title">
            <i class="fas fa-carrot"></i>
            <h2>Vegetables Available Right Now</h2>
        </div>
        <?php if ($products && $products->num_rows > 0): ?>
        <div class="products-grid">
            <?php while ($p = $products->fetch_assoc()): ?>
            <div class="product-card">
                <div class="product-image">
                    <img src="<?php echo htmlspecialchars(getProductImage($p['pdt_name'])); ?>" alt="<?php echo htmlspecialchars($p['pdt_name']); ?>">
                </div>
                <div class="product-name"><?php echo htmlspecialchars($p['pdt_name']); ?></div>
                <div class="product-price">shs<?php echo number_format($p['price_per_unit']); ?> / <?php echo htmlspecialchars(strtolower($p['unit_of_measure'])); ?></div>
                <div class="product-description"><?php echo htmlspecialchars(getProductDescription($p['pdt_name'])); ?></div>
                <div class="product-meta">
                    📦 Stock: <?php echo $p['qty_in_stock']; ?> <?php echo htmlspecialchars($p['unit_of_measure']); ?><br>
                    👨‍🌾 Farmer: <?php echo htmlspecialchars($p['f_name'] . ' ' . $p['l_name']); ?><br>
                    🏷️ ID: <?php echo htmlspecialchars($p['pdt_id']); ?>
                </div>
                <button type="button" class="order-btn" data-product-id="<?php echo htmlspecialchars($p['pdt_id']); ?>" data-farmer-id="<?php echo htmlspecialchars($p['f_id']); ?>" data-price="<?php echo htmlspecialchars($p['price_per_unit']); ?>" data-unit="<?php echo htmlspecialchars($p['unit_of_measure']); ?>" data-name="<?php echo htmlspecialchars($p['pdt_name']); ?>">Order Now</button>
            </div>
            <?php endwhile; ?>
        </div>
        <?php else: ?>
            <p style="color:#888;text-align:center;padding:2rem">No products currently in stock.</p>
        <?php endif; ?>
    </div>

    <!-- My Orders -->
    <div class="modern-card">
        <div class="section-title">
            <i class="fas fa-list-alt"></i>
            <h2>My Order History</h2>
        </div>
        <?php if ($orders && $orders->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Order ID</th><th>Product</th><th>Qty</th>
                    <th>Amount (UGX)</th><th>Date</th><th>Delivery</th><th>Payment</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($o = $orders->fetch_assoc()): ?>
            <tr>
                <td><?php echo $o['order_id']; ?></td>
                <td><?php echo htmlspecialchars($o['pdt_name'] ?? $o['pdt_id']); ?></td>
                <td><?php echo $o['order_qty']; ?></td>
                <td><?php echo number_format($o['total_amount']); ?></td>
                <td><?php echo $o['order_date']; ?></td>
                <td>
                    <span class="badge <?php echo strtolower($o['delivery_status']); ?>">
                        <?php echo $o['delivery_status']; ?>
                    </span>
                </td>
                <td>
                    <span class="badge <?php echo strtolower($o['payment_status']); ?>">
                        <?php echo $o['payment_status']; ?>
                    </span>
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p style="color:#888;text-align:center;padding:2rem">No orders yet. <a href="#available-products" style="color:#1565c0">Place your first order →</a></p>
        <?php endif; ?>
    </div>

</div>

<div id="orderModalOverlay" class="modal-overlay" aria-hidden="true">
    <div class="order-modal" role="dialog" aria-modal="true" aria-labelledby="orderModalTitle">
        <button id="closeOrderModal" style="position:absolute;top:1rem;right:1rem;border:none;background:none;color:#2e7d32;font-size:1.4rem;cursor:pointer;">&times;</button>
        <h3 id="orderModalTitle">Place your order</h3>
        <div class="modal-row full">
            <label>Product</label>
            <input type="text" id="orderProductName" readonly>
        </div>
        <div class="modal-row">
            <div>
                <label>Unit Price</label>
                <input type="text" id="orderUnitPrice" readonly>
            </div>
            <div>
                <label>Unit</label>
                <input type="text" id="orderUnit" readonly>
            </div>
        </div>
        <div class="modal-row">
            <div>
                <label for="orderQuantity">Quantity</label>
                <input type="number" id="orderQuantity" min="1" step="1" value="1">
            </div>
            <div>
                <label for="orderTotal">Total Amount</label>
                <input type="text" id="orderTotal" readonly>
            </div>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn-secondary" id="cancelOrderButton">Cancel</button>
            <button type="button" class="btn-primary" id="submitOrderButton">Submit Order</button>
        </div>
    </div>
</div>

<div id="orderNotification" class="notification"></div>

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
        if (href === currentPage || (currentPage === '' && href === 'vendor_dashboard.php')) {
            link.classList.add('active');
        }
    });

    const notification = document.getElementById('orderNotification');

    function showNotification(message, success = true) {
        notification.textContent = message;
        notification.style.background = success ? 'rgba(46, 125, 50, 0.95)' : 'rgba(211, 47, 47, 0.95)';
        notification.classList.add('show');
        clearTimeout(window.notificationTimeout);
        window.notificationTimeout = setTimeout(() => {
            notification.classList.remove('show');
        }, 4500);
    }

    const orderButtons = document.querySelectorAll('.order-btn');
    const orderModalOverlay = document.getElementById('orderModalOverlay');
    const closeOrderModal = document.getElementById('closeOrderModal');
    const cancelOrderButton = document.getElementById('cancelOrderButton');
    const submitOrderButton = document.getElementById('submitOrderButton');
    const orderProductName = document.getElementById('orderProductName');
    const orderUnitPrice = document.getElementById('orderUnitPrice');
    const orderUnit = document.getElementById('orderUnit');
    const orderQuantity = document.getElementById('orderQuantity');
    const orderTotal = document.getElementById('orderTotal');

    let currentOrder = {
        productId: null,
        farmerId: null,
        price: 0,
        orderBtn: null
    };

    function updateOrderTotal() {
        const qty = Number(orderQuantity.value) || 0;
        const total = qty * currentOrder.price;
        orderTotal.value = total > 0 ? `UGX ${total.toLocaleString()}` : 'UGX 0';
    }

    function openOrderModal(btn) {
        currentOrder.productId = btn.dataset.productId;
        currentOrder.farmerId = btn.dataset.farmerId;
        currentOrder.price = Number(btn.dataset.price) || 0;
        currentOrder.orderBtn = btn;

        orderProductName.value = btn.dataset.name || '';
        orderUnitPrice.value = `UGX ${currentOrder.price.toLocaleString()}`;
        orderUnit.value = btn.dataset.unit || '';
        orderQuantity.value = 1;
        updateOrderTotal();

        orderModalOverlay.classList.add('show');
    }

    function closeOrderModalDialog() {
        orderModalOverlay.classList.remove('show');
        currentOrder = {
            productId: null,
            farmerId: null,
            price: 0,
            orderBtn: null
        };
    }

    orderButtons.forEach(btn => {
        btn.addEventListener('click', event => {
            event.preventDefault();
            openOrderModal(btn);
        });
    });

    closeOrderModal.addEventListener('click', closeOrderModalDialog);
    cancelOrderButton.addEventListener('click', closeOrderModalDialog);
    orderModalOverlay.addEventListener('click', event => {
        if (event.target === orderModalOverlay) {
            closeOrderModalDialog();
        }
    });
    orderQuantity.addEventListener('input', updateOrderTotal);

    submitOrderButton.addEventListener('click', async () => {
        const qty = Number(orderQuantity.value);
        if (!qty || qty <= 0) {
            showNotification('Please enter a valid quantity.', false);
            return;
        }

        if (!currentOrder.productId || !currentOrder.farmerId) {
            showNotification('Order details are missing.', false);
            return;
        }

        currentOrder.orderBtn.disabled = true;
        currentOrder.orderBtn.textContent = 'Ordering...';

        try {
            const response = await fetch('vendor_place_order.php', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ product_id: currentOrder.productId, farmer_id: currentOrder.farmerId, order_qty: qty })
            });

            const text = await response.text();
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                showNotification('Server error: ' + (text || response.statusText), false);
                return;
            }

            if (!response.ok) {
                showNotification(data.message || 'Order could not be placed.', false);
            } else if (data.success) {
                const amountText = data.total_amount ? `UGX ${Number(data.total_amount).toLocaleString()}` : '';
                showNotification(`Your order${amountText ? ' for ' + amountText : ''} has been received and will be confirmed shortly.`);
                closeOrderModalDialog();
            } else {
                showNotification(data.message || 'Order could not be placed.', false);
            }
        } catch (error) {
            console.error(error);
            showNotification('An error occurred while placing the order.', false);
        }

        currentOrder.orderBtn.disabled = false;
        currentOrder.orderBtn.textContent = 'Order Now';
    });
</script>

</body>
</html>
