<?php
session_start();

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin_dashboard.php');
        exit();
    } elseif ($_SESSION['role'] === 'farmer') {
        header('Location: farmer_dashboard.php');
        exit();
    } elseif ($_SESSION['role'] === 'vendor') {
        header('Location: vendor_dashboard.php');
        exit();
    }
}

// Hardcoded admin credentials (change these!)
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'admin123');

// DB connection
$conn = new mysqli("localhost", "root", "", "vegetable_database");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role     = $_POST['role'];
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password']; // used only for admin

    if ($role === 'admin') {
        // Admin: check hardcoded credentials
        if ($username === 'Vian' && $password === 'admin') {
            $_SESSION['role']     = 'admin';
            $_SESSION['username'] = $username;
            header("Location: admin_dashboard.php");
            exit();
        } else {
            $error = "Invalid admin credentials.";
        }

    } elseif ($role === 'farmer') {
        // Farmer: check if f_id exists
        $sql = "SELECT f_id, f_name, l_name FROM farmer WHERE f_id = '$username'";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $_SESSION['role']     = 'farmer';
            $_SESSION['user_id']  = $row['f_id'];
            $_SESSION['username'] = $row['f_name'] . ' ' . $row['l_name'];
            header("Location: farmer_dashboard.php");
            exit();
        } else {
            $error = "Farmer ID not found. Please register first.";
        }

    } elseif ($role === 'vendor') {
        // Vendor: check if v_id exists
        $sql = "SELECT v_id, f_name, l_name FROM vendor WHERE v_id = '$username'";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $_SESSION['role']     = 'vendor';
            $_SESSION['user_id']  = $row['v_id'];
            $_SESSION['username'] = $row['f_name'] . ' ' . $row['l_name'];
            header("Location: vendor_dashboard.php");
            exit();
        } else {
            $error = "Vendor ID not found. Please register first.";
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Online Vegetable Sales System</title>
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

        /* Navigation - modern glass-morphism style */
        nav {
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(4px);
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid rgba(46, 125, 50, 0.2);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }
        nav ul {
            list-style: none;
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            max-width: 1200px;
            margin: 0 auto;
        }
        nav ul li a {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 1rem 1.6rem;
            color: #1f3b1a;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.25s ease;
            border-bottom: 3px solid transparent;
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

        /* Login specific styles */
        .login-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 24px 48px rgba(0,0,0,0.25);
            margin: 0 auto;
        }

        .login-card h2 {
            font-size: 1.4rem;
            font-weight: 700;
            color: #1a3b1a;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        /* Role tabs */
        .role-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 1.5rem;
        }
        .role-tab {
            flex: 1;
            padding: 10px 6px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            background: #f9f9f9;
            cursor: pointer;
            text-align: center;
            font-size: 0.82rem;
            font-weight: 600;
            color: #666;
            transition: all 0.2s;
        }
        .role-tab i { display: block; font-size: 1.3rem; margin-bottom: 4px; }
        .role-tab:hover { border-color: #4caf50; color: #2e7d32; }
        .role-tab.active.farmer  { border-color: #4caf50; background: #e8f5e9; color: #2e7d32; }
        .role-tab.active.vendor  { border-color: #1976d2; background: #e3f2fd; color: #1565c0; }
        .role-tab.active.admin   { border-color: #f57c00; background: #fff3e0; color: #e65100; }

        .form-group { margin-bottom: 1.2rem; }
        .form-group label {
            display: block;
            font-size: 0.88rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 6px;
        }
        .form-group input {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid #ddd;
            border-radius: 10px;
            font-size: 15px;
            transition: 0.25s;
            background: #f9f9f9;
            font-family: inherit;
        }
        .form-group input:focus {
            border-color: #4caf50;
            outline: none;
            background: white;
            box-shadow: 0 0 0 3px rgba(76,175,80,0.15);
        }

        #password-group { display: none; }

        .hint {
            font-size: 0.8rem;
            color: #888;
            margin-top: 4px;
        }

        .error-msg {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ef9a9a;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 0.88rem;
            margin-bottom: 1rem;
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 10px;
            background: linear-gradient(135deg, #2e7d32, #1b5e20);
            color: white;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: 0.25s;
            font-family: inherit;
        }
        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(46,125,50,0.35);
        }

        .divider {
            text-align: center;
            color: #aaa;
            font-size: 0.82rem;
            margin: 1.2rem 0;
            position: relative;
        }
        .divider::before, .divider::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 38%;
            height: 1px;
            background: #eee;
        }
        .divider::before { left: 0; }
        .divider::after  { right: 0; }

        .register-links {
            display: flex;
            gap: 10px;
        }
        .register-links a {
            flex: 1;
            text-align: center;
            padding: 10px;
            border-radius: 8px;
            font-size: 0.82rem;
            font-weight: 600;
            text-decoration: none;
            border: 1.5px solid;
            transition: 0.2s;
        }
        .register-links a.farmer-link {
            color: #2e7d32;
            border-color: #a5d6a7;
        }
        .register-links a.farmer-link:hover { background: #e8f5e9; }
        .register-links a.vendor-link {
            color: #1565c0;
            border-color: #90caf9;
        }
        .register-links a.vendor-link:hover { background: #e3f2fd; }

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
            .login-card {
                padding: 1.5rem;
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
        <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
    </ul>
</nav>

<div class="main-container">
    <div class="modern-card">
        <div class="section-title">
            <i class="fas fa-sign-in-alt"></i>
            <h2>Login</h2>
        </div>

        <?php if ($error): ?>
            <div class="error-msg"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Role selector -->
        <div class="role-tabs">
            <div class="role-tab farmer active" onclick="selectRole('farmer')">
                <i class="fas fa-tractor"></i> Farmer
            </div>
            <div class="role-tab vendor" onclick="selectRole('vendor')">
                <i class="fas fa-store"></i> Vendor
            </div>
            <div class="role-tab admin" onclick="selectRole('admin')">
                <i class="fas fa-user-shield"></i> Admin
            </div>
        </div>

        <form action="login.php" method="POST">
            <input type="hidden" name="role" id="role-input" value="farmer">

            <div class="form-group">
                <label id="username-label">Farmer ID</label>
                <input type="text" name="username" id="username" placeholder="e.g. F001" required autocomplete="off">
                <div class="hint" id="username-hint">Enter the Farmer ID you registered with</div>
            </div>

            <div class="form-group" id="password-group">
                <label>Password</label>
                <input type="password" name="password" id="password" placeholder="Admin password">
            </div>

            <button type="submit" class="btn-login">Login &rarr;</button>
        </form>

        <div class="divider">New here?</div>

        <div class="register-links">
            <a href="farmer.html" class="farmer-link"><i class="fas fa-tractor"></i> Register as Farmer</a>
            <a href="vendor.html" class="vendor-link"><i class="fas fa-store"></i> Register as Vendor</a>
        </div>
    </div>
</div>

<footer>
    <p>&copy; 2026 Online Vegetable Sales Information System</p>
</footer>

<script>
function selectRole(role) {
    document.querySelectorAll('.role-tab').forEach(t => t.classList.remove('active'));
    document.querySelector('.role-tab.' + role).classList.add('active');
    document.getElementById('role-input').value = role;

    const label = document.getElementById('username-label');
    const hint  = document.getElementById('username-hint');
    const input = document.getElementById('username');
    const pwdGrp = document.getElementById('password-group');
    const pwdInput = document.getElementById('password');

    if (role === 'farmer') {
        label.textContent = 'Farmer ID';
        input.placeholder = 'e.g. F***';
        hint.textContent  = 'Enter the Farmer ID you registered with';
        pwdGrp.style.display = 'none';
        pwdInput.required = false;
    } else if (role === 'vendor') {
        label.textContent = 'Vendor ID';
        input.placeholder = 'e.g. V***';
        hint.textContent  = 'Enter the Vendor ID you registered with';
        pwdGrp.style.display = 'none';
        pwdInput.required = false;
    } else {
        label.textContent = 'Username';
        input.placeholder = 'Admin username';
        hint.textContent  = 'Enter your admin credentials';
        pwdGrp.style.display = 'block';
        pwdInput.required = true;
    }
}
</script>
</body>
</html>