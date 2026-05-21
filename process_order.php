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

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Get form data - only the fields from simplified form
    $order_qty = mysqli_real_escape_string($conn, $_POST['order_qty']);
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
    $v_id = mysqli_real_escape_string($conn, $_POST['v_id']);
    
    // Basic validation
    $errors = array();
    
    if (empty($order_qty)) {
        $errors[] = "Order quantity is required";
    } elseif (!is_numeric($order_qty)) {
        $errors[] = "Order quantity must be a number";
    }
    if (empty($payment_method)) {
        $errors[] = "Payment method is required";
    }
    if (empty($v_id)) {
        $errors[] = "Vendor ID is required";
    }
    
    // If no errors, insert into database as pending order
    if (empty($errors)) {
        // Generate unique order ID
        $order_id = "ORD" . date("YmdHis") . rand(100, 999);
        
        // Insert query for orders table with default values for admin fields
        $sql = "INSERT INTO orders (order_id, order_qty, order_date, delivery_status, payment_status, total_amount, payment_method, v_id, pdt_id, f_id) 
                VALUES ('$order_id', '$order_qty', CURDATE(), 'Pending', 'Pending', 0, '$payment_method', '$v_id', '', '')";
        
        if ($conn->query($sql) === TRUE) {
            // Success message - redirect to admin dashboard
            echo "<!DOCTYPE html>";
            echo "<html>";
            echo "<head>";
            echo "<meta http-equiv='refresh' content='3;url=index.html'>";
            echo "<style>";
            echo "body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: linear-gradient(135deg, #1b5e20 0%, #0a3b0e 100%); }";
            echo ".success-box { background: white; max-width: 600px; margin: 0 auto; padding: 30px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }";
            echo ".success-icon { color: #00c853; font-size: 60px; margin-bottom: 20px; }";
            echo "h2 { color: #006400; }";
            echo ".details { text-align: left; margin: 20px 0; padding: 10px; background: #f5f5f5; border-radius: 8px; }";
            echo ".details p { margin: 8px 0; }";
            echo "</style>";
            echo "</head>";
            echo "<body>";
            echo "<div class='success-box'>";
            echo "<div class='success-icon'>✓</div>";
            echo "<h2>Order Submitted Successfully!</h2>";
            echo "<div class='details'>";
            echo "<p><strong>Order ID:</strong> " . $order_id . "</p>";
            echo "<p><strong>Quantity:</strong> " . $order_qty . "</p>";
            echo "<p><strong>Payment Method:</strong> " . $payment_method . "</p>";
            echo "<p><strong>Vendor ID:</strong> " . $v_id . "</p>";
            echo "<p><strong>Status:</strong> Pending Admin Approval</p>";
            echo "</div>";
            echo "<p>Your order has been submitted and is pending admin approval.</p>";
            echo "<p>Redirecting to admin dashboard in 3 seconds...</p>";
            echo "<p><a href='index.html' style='color: #006400;'>Go to Admin Dashboard</a></p>";
            echo "</div>";
            echo "</body>";
            echo "</html>";
        } else {
            // Error in insertion
            echo "<!DOCTYPE html>";
            echo "<html>";
            echo "<head>";
            echo "<meta http-equiv='refresh' content='5;url=order.html'>";
            echo "<style>";
            echo "body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }";
            echo ".error-box { background: white; max-width: 500px; margin: 0 auto; padding: 30px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }";
            echo ".error-icon { color: #f5576c; font-size: 60px; margin-bottom: 20px; }";
            echo "</style>";
            echo "</head>";
            echo "<body>";
            echo "<div class='error-box'>";
            echo "<div class='error-icon'>✗</div>";
            echo "<h2>Database Error</h2>";
            echo "<p>Error: " . $conn->error . "</p>";
            echo "<p>Redirecting back in 5 seconds...</p>";
            echo "<p><a href='order.html' style='color: #006400;'>Go back now</a></p>";
            echo "</div>";
            echo "</body>";
            echo "</html>";
        }
    } else {
        // Display validation errors
        echo "<!DOCTYPE html>";
        echo "<html>";
        echo "<head>";
        echo "<meta http-equiv='refresh' content='5;url=order.html'>";
        echo "<style>";
        echo "body { font-family: Arial, sans-serif; padding: 50px; background: #ffebee; }";
        echo ".error-box { background: white; max-width: 500px; margin: 0 auto; padding: 30px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border-left: 5px solid #f44336; }";
        echo "h2 { color: #f44336; }";
        echo "ul { color: #333; }";
        echo "</style>";
        echo "</head>";
        echo "<body>";
        echo "<div class='error-box'>";
        echo "<h2>Validation Errors</h2>";
        echo "<ul>";
        foreach ($errors as $error) {
            echo "<li>$error</li>";
        }
        echo "</ul>";
        echo "<p>Redirecting back to form in 5 seconds...</p>";
        echo "<p><a href='order.html' style='color: #006400;'>Go back now</a></p>";
        echo "</div>";
        echo "</body>";
        echo "</html>";
    }
} else {
    // If someone tries to access this file directly without submitting the form
    header("Location: order.html");
    exit();
}

$conn->close();
?>