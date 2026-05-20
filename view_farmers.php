<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php"); exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "vegetable_database");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// CORRECTED QUERY - using farmer_id instead of f_id
$sql = "SELECT * FROM farmer_view ORDER BY f_id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmers List - Vegetable Sales System</title>
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
            background: linear-gradient(135deg, #1b5e20 0%, #0a3b0e 100%);
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
            border-left-color: #66bb6a;
            padding-left: 1.8rem;
        }
        .sidebar-menu li a.active {
            background: rgba(102, 187, 106, 0.2);
            border-left-color: #66bb6a;
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
            background: #2e7d32;
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
            background: #1b5e20;
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
            nav ul li a {
                padding: 0.8rem 1rem;
                font-size: 0.85rem;
            }
            header h1 {
                font-size: 1.4rem;
            }
        }
        @media (max-width: 480px) {
            .section-title {
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>

<header>
    <h1>ONLINE VEGETABLE SALES AND MARKETING INFORMATION SYSTEM</h1>
</header>

<nav>
    <ul>
        <li><a href="index.html"><i class="fas fa-home"></i> Home</a></li>
        <li><a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="admin_orders.php"><i class="fas fa-tasks"></i> Orders</a></li>
        <li><a href="view_farmers.php"><i class="fas fa-users"></i> Farmers</a></li>
        <li><a href="view_vendors.php"><i class="fas fa-store-alt"></i> Vendors</a></li>
        <li><a href="view_products.php"><i class="fas fa-boxes"></i> Products</a></li>
        <li><a href="report.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
</nav>

<div class="user-info">
    <div class="user-details">
        <i class="fas fa-crown"></i> Admin Dashboard - System Administrator
    </div>
    <a href="logout.php" class="logout-link">Sign Out</a>
</div>

        
    </style>
</head>
<body>


<div class="main-container">

    <div class="modern-card">
        <div class="section-title">
            <i class="fas fa-tractor"></i>
            <h2>Farmers List</h2>
        </div>
        <p class="welcome-text">View and manage all registered farmers in the system.</p>

        <div style="margin-bottom: 1.5rem;">
            <?php if ($result && $result->num_rows > 0): ?>
                <p style="font-size: 1rem; color: #2c3e2b;">Total Farmers: <strong><?php echo $result->num_rows; ?></strong></p>
            <?php endif; ?>

            <div style="margin-top: 1rem; display: flex; gap: 1rem; flex-wrap: wrap;">
                <button onclick="window.print()" style="background: #2e7d32; color: white; border: none; padding: 0.5rem 1rem; border-radius: 8px; cursor: pointer; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fas fa-print"></i> Print List
                </button>
                <a href="farmer.html" style="background: #4caf50; color: white; text-decoration: none; padding: 0.5rem 1rem; border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fas fa-plus"></i> Add New Farmer
                </a>
            </div>
        </div>

        <?php if ($result && $result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Farmer ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Contact</th>
                        <th>Address</th>
                        <th>Payment Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['f_id']; ?></td>
                            <td><?php echo htmlspecialchars($row['f_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['l_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['contact']); ?></td>
                            <td><?php echo htmlspecialchars($row['address']); ?></td>
                            <td><?php echo htmlspecialchars($row['payment_details']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="text-align: center; padding: 3rem; color: #888;">
                <p style="font-size: 1.2rem;">No farmers found in the database.</p>
                <p style="margin-top: 1rem;">
                    <a href="farmer.html" style="background: #4caf50; color: white; text-decoration: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600;">Add Your First Farmer</a>
                </p>
            </div>
        <?php endif; ?>
    </div>

</div>

<footer>
    <div style="max-width: 1200px; margin: 0 auto;">
        <p>&copy; 2026 Online Vegetable Sales And Marketing Information System</p>
        <p style="margin-top: 0.5rem; font-size: 0.8rem; opacity: 0.8;">
            Connecting farmers, vendors, and customers for fresh, quality vegetables across Uganda
        </p>
    </div>
</footer>

</body>
</html>

<?php
// Close connection
$conn->close();
?>