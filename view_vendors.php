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

$sql = "SELECT * FROM vendor ORDER BY v_id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendors List - Vegetable Sales System</title>
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
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #e0e8dc; }
        ::-webkit-scrollbar-thumb { background: #2e7d32; border-radius: 10px; }

        /* Header */
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

        /* User info bar */
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

        /* Main container */
        .main-container {
            max-width: 1280px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }

        /* Card */
        .modern-card {
            background: white;
            border-radius: 28px;
            box-shadow: 0 10px 30px -12px rgba(0, 0, 0, 0.08);
            padding: 2rem 2.2rem;
            margin-bottom: 2.5rem;
            border: 1px solid rgba(0, 0, 0, 0.03);
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

        table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
        th, td { padding: 10px 12px; text-align: left; border-bottom: 1px solid #f0f0f0; }
        th { background: #f5faf3; color: #2e7d32; font-weight: 600; font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.5px; }
        tr:hover td { background: #fafff9; }

        /* Footer */
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
            .modern-card { padding: 1.5rem; }
            .section-title h2 { font-size: 1.4rem; }
            header h1 { font-size: 1.4rem; }
            table { font-size: 0.8rem; }
            th, td { padding: 8px 10px; }
        }
        @media (max-width: 480px) {
            .section-title { flex-wrap: wrap; }
        }
    </style>
</head>
<body>

<header>
    <h1>ONLINE VEGETABLE SALES AND MARKETING INFORMATION SYSTEM</h1>
</header>

<div class="user-info">
    <div class="user-details">
        <i class="fas fa-crown"></i> Admin Dashboard - System Administrator
    </div>
    <a href="logout.php" class="logout-link">Sign Out</a>
</div>

<div class="main-container">

    <div class="modern-card">
        <div class="section-title">
            <i class="fas fa-store"></i>
            <h2>Vendors List</h2>
        </div>
        <p class="welcome-text">View and manage all registered vendors in the system.</p>

        <div style="margin-bottom: 1.5rem;">
            <?php if ($result->num_rows > 0): ?>
                <p style="font-size: 1rem; color: #2c3e2b;">Total Vendors: <strong><?php echo $result->num_rows; ?></strong></p>
            <?php endif; ?>

            <div style="margin-top: 1rem; display: flex; gap: 1rem; flex-wrap: wrap;">
                <button onclick="window.print()" style="background: #2e7d32; color: white; border: none; padding: 0.5rem 1rem; border-radius: 8px; cursor: pointer; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fas fa-print"></i> Print List
                </button>
                <a href="vendor.html" style="background: #4caf50; color: white; text-decoration: none; padding: 0.5rem 1rem; border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fas fa-plus"></i> Add New Vendor
                </a>
            </div>
        </div>

        <?php if ($result->num_rows > 0): ?>

            <table>
                <thead>
                    <tr>
                        <th>Vendor ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Contact</th>
                        <th>Address</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['v_id']; ?></td>
                        <td><?php echo htmlspecialchars($row['f_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['l_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['contact']); ?></td>
                        <td><?php echo htmlspecialchars($row['address']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="text-align: center; padding: 3rem; color: #888;">
                <p style="font-size: 1.2rem;">No vendors found in the database.</p>
                <p style="margin-top: 1rem;">
                    <a href="vendor.html" style="background: #4caf50; color: white; text-decoration: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600;">Add Your First Vendor</a>
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

<?php $conn->close(); ?>