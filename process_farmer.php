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
    
    // Get form data
    $f_id = mysqli_real_escape_string($conn, $_POST['f_id']);
    $f_name = mysqli_real_escape_string($conn, $_POST['f_name']);
    $l_name = mysqli_real_escape_string($conn, $_POST['l_name']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $payment_details = mysqli_real_escape_string($conn, $_POST['payment_details']);
    
    // Basic validation
    $errors = array();
    
    if (empty($f_id)) {
        $errors[] = "Farmer ID is required";
        }
    if (empty($f_id)) {
    $errors[] = "Farmer ID is required";
    } elseif (!preg_match("/^F\d{3}$/", $f_id)) {
    $errors[] = "Farmer ID must follow the format F001";
    }

    if (empty($f_name)) {
        $errors[] = "First name is required";
    }
    if (empty($l_name)) {
        $errors[] = "Last name is required";
    }
    if (empty($contact)) {
        $errors[] = "Contact number is required";
    }
    if (empty($address)) {
        $errors[] = "Address is required";
    }
    if (empty($payment_details)) {
        $errors[] = "Payment details are required";
    }
    
    // If no errors, insert into database
    if (empty($errors)) {
        // Insert query for farmer table
        $sql = "INSERT INTO farmer (f_id, f_name, l_name, contact, address, payment_details) 
                VALUES ('$f_id', '$f_name', '$l_name', '$contact', '$address', '$payment_details')";
        
        if ($conn->query($sql) === TRUE) {
            // Success message
            echo "<!DOCTYPE html>";
            echo "<html>";
            echo "<head>";
            echo "<meta http-equiv='refresh' content='3;url=farmer.html'>";
            echo "<style>";
            echo "body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: linear-gradient(135deg, #1b5e20 0%, #0a3b0e 100%); }";
            echo ".success-box { background: white; max-width: 500px; margin: 0 auto; padding: 30px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }";
            echo ".success-icon { color: #00c853; font-size: 60px; margin-bottom: 20px; }";
            echo "h2 { color: #006400; }";
            echo ".details { text-align: left; margin: 20px 0; padding: 10px; background: #f5f5f5; border-radius: 8px; }";
            echo "</style>";
            echo "</head>";
            echo "<body>";
            echo "<div class='success-box'>";
            echo "<div class='success-icon'>✓</div>";
            echo "<h2>Farmer Registered Successfully!</h2>";
            echo "<div class='details'>";
            echo "<p><strong>Farmer ID:</strong> " . $f_id . "</p>";
            echo "<p><strong>Name:</strong> " . $f_name . " " . $l_name . "</p>";
            echo "<p><strong>Contact:</strong> " . $contact . "</p>";
            echo "<p><strong>Address:</strong> " . $address . "</p>";
            echo "<p><strong>Payment Details:</strong> " . $payment_details . "</p>";
            echo "</div>";
            echo "<p>Redirecting to Farmer Form in 3 seconds...</p>";
            echo "<p><a href='farmer.html' style='color: #006400;'>Click here if not redirected</a></p>";
            echo "</div>";
            echo "</body>";
            echo "</html>";
        } else {
            // Error in insertion
            echo "<!DOCTYPE html>";
            echo "<html>";
            echo "<head>";
            echo "<meta http-equiv='refresh' content='5;url=farmer.html'>";
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
            echo "<p><a href='farmer.html' style='color: #006400;'>Go back now</a></p>";
            echo "</div>";
            echo "</body>";
            echo "</html>";
        }
    } else {
        // Display validation errors
        echo "<!DOCTYPE html>";
        echo "<html>";
        echo "<head>";
        echo "<meta http-equiv='refresh' content='5;url=farmer.html'>";
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
        echo "<p><a href='farmer.html' style='color: #006400;'>Go back now</a></p>";
        echo "</div>";
        echo "</body>";
        echo "</html>";
    }
} else {
    // If someone tries to access this file directly without submitting the form
    header("Location: farmer.html");
    exit();
}

$conn->close();
?>