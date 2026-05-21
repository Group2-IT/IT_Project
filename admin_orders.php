<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php"); exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Pending Orders</title>
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

        .main-container {
            max-width: 1280px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }

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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%);
            color: white;
            font-weight: 600;
            font-size: 0.95rem;
        }

        tr:nth-child(even) {
            background: #f8faf6;
        }

        tr:hover {
            background: #eef5e9;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .btn-complete {
            background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.25s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-complete:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(46, 125, 50, 0.3);
        }

        .form-container {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .form-container h3 {
            color: #1b5e20;
            margin-bottom: 1.5rem;
            font-size: 1.4rem;
        }

        form label {
            display: block;
            margin-top: 15px;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }

        form input, form select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 15px;
            transition: 0.3s;
            background: #f9f9f9;
        }

        form input:focus, form select:focus {
            border: 2px solid #32CD32;
            outline: none;
            background: white;
            box-shadow: 0px 0px 8px rgba(50,205,50,0.5);
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            margin-top: 25px;
            border: none;
            border-radius: 10px;
            background: linear-gradient(to right, #00c853, #009624);
            color: white;
            font-size: 17px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.4s;
        }

        .btn-submit:hover {
            background: linear-gradient(to right, #009624, #006400);
            transform: scale(1.02);
        }

        footer {
            background: #0f2a0c;
            color: #d0e6c8;
            text-align: center;
            padding: 2rem 1rem;
            margin-top: 2rem;
            font-size: 0.85rem;
            border-top: 1px solid #2a5a24;
        }

        @media (max-width: 768px) {
            .modern-card {
                padding: 1.5rem;
            }
            table {
                font-size: 0.9rem;
            }
            th, td {
                padding: 8px 10px;
            }
        }
    </style>
</head>
<body>

<button class="sidebar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h3><i class="fas fa-crown"></i> Admin</h3>
        <p><?php echo htmlspecialchars($_SESSION['username']); ?></p>
    </div>
    <ul class="sidebar-menu">
        <li><a href="index.html"><i class="fas fa-home"></i> Home</a></li>
        <li><a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="admin_orders.php"><i class="fas fa-tasks"></i> Orders</a></li>
        <li><a href="view_farmers.php"><i class="fas fa-users"></i> Farmers</a></li>
        <li><a href="view_vendors.php"><i class="fas fa-store-alt"></i> Vendors</a></li>
        <li><a href="view_products.php"><i class="fas fa-boxes"></i> Products</a></li>
        <li><a href="report.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
    </ul>
    <div class="sidebar-footer">
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</aside>

<header>
    <h1>ONLINE VEGETABLE SALES AND MARKETING INFORMATION SYSTEM</h1>
</header>

<div class="main-container">

    <div class="modern-card">
        <div class="section-title">
            <i class="fas fa-clock"></i>
            <h2>Pending Orders</h2>
        </div>
        <p class="welcome-text">
            Review and complete pending orders submitted by vendors. Fill in the missing details to finalize each order.
        </p>

        <?php
        // Database configuration
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "vegetable_database";

        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Get pending orders
        $sql = "SELECT o.*, p.pdt_name FROM orders o LEFT JOIN product p ON o.pdt_id = p.pdt_id WHERE o.delivery_status = 'Pending' AND o.payment_status = 'Pending' ORDER BY o.order_date DESC";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            echo "<table>";
            echo "<thead>";
            echo "<tr>";
            echo "<th>Order ID</th>";
            echo "<th>Product</th>";
            echo "<th>Quantity</th>";
            echo "<th>Payment Method</th>";
            echo "<th>Vendor ID</th>";
            echo "<th>Order Date</th>";
            echo "<th>Status</th>";
            echo "<th>Action</th>";
            echo "</tr>";
            echo "</thead>";
            echo "<tbody>";

            while($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row["order_id"] . "</td>";                echo "<td>" . htmlspecialchars($row["pdt_name"] ?: $row["pdt_id"]) . "</td>";                echo "<td>" . $row["order_qty"] . "</td>";
                echo "<td>" . $row["payment_method"] . "</td>";
                echo "<td>" . $row["v_id"] . "</td>";
                echo "<td>" . $row["order_date"] . "</td>";
                echo "<td><span class='status-pending'>Pending</span></td>";
                echo "<td><button class='btn-complete' onclick='completeOrder(\"" . $row["order_id"] . "\")'>Complete Order</button></td>";
                echo "</tr>";
            }

            echo "</tbody>";
            echo "</table>";
        } else {
            echo "<p>No pending orders found.</p>";
        }

        $conn->close();
        ?>
    </div>

    <!-- Order Completion Form (Hidden by default) -->
    <div id="completionForm" class="form-container" style="display: none;">
        <h3>Complete Order Details</h3>
        <form action="complete_order.php" method="POST" id="orderForm">
            <input type="hidden" name="order_id" id="complete_order_id">

            <label>Product ID</label>
            <input type="text" name="pdt_id" id="pdt_id" placeholder="Product ID" required>

            <label>Farmer ID</label>
            <input type="text" name="f_id" id="f_id" placeholder="Farmer ID" required>

            <label>Total Amount (UGX)</label>
            <input type="number" name="total_amount" id="total_amount" placeholder="Total Amount" step="0.01" required>

            <label>Delivery Status</label>
            <select name="delivery_status" id="delivery_status" required>
                <option value="">Select Delivery Status</option>
                <option value="Processing">Processing</option>
                <option value="Delivered">Delivered</option>
                <option value="Cancelled">Cancelled</option>
            </select>

            <label>Payment Status</label>
            <select name="payment_status" id="payment_status" required>
                <option value="">Select Payment Status</option>
                <option value="Paid">Paid</option>
                <option value="Failed">Failed</option>
            </select>

            <button type="submit" class="btn-submit">Complete Order</button>
        </form>
    </div>

</div>

<script>
function completeOrder(orderId) {
    document.getElementById('complete_order_id').value = orderId;
    document.getElementById('completionForm').style.display = 'block';
    document.getElementById('completionForm').scrollIntoView({ behavior: 'smooth' });
}
</script>

<footer>
    <p>&copy; 2026 Online Vegetable Sales And Marketing Information System</p>
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
        if (href === currentPage || (currentPage === '' && href === 'admin_orders.php')) {
            link.classList.add('active');
        }
    });
</script>

</body>
</html>