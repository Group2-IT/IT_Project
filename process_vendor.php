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
    
    // Get form data (including v_id)
    $v_id = mysqli_real_escape_string($conn, $_POST['v_id']);  // ← Added semicolon here!
    $f_name = mysqli_real_escape_string($conn, $_POST['f_name']);
    $l_name = mysqli_real_escape_string($conn, $_POST['l_name']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    // Basic validation
    $errors = array();
    
    if (empty($v_id)) {
        $errors[] = "Vendor ID is required";
    } elseif (!preg_match("/^V\d{3}$/", $v_id)) {
        $errors[] = "Vendor ID must follow the format V001";
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
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address";
    }
    
    // If no errors, insert into database
    if (empty($errors)) {
        // INSERT with v_id included
        $sql = "INSERT INTO vendor (v_id, f_name, l_name, contact, address, email) 
                VALUES ('$v_id', '$f_name', '$l_name', '$contact', '$address', '$email')";
        
        if ($conn->query($sql) === TRUE) {
            // Success message
            echo "<!DOCTYPE html>";
            echo "<html>";
            echo "<head>";
            echo "<meta http-equiv='refresh' content='3;url=vendor.html'>";
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
            echo "<h2>Vendor Registered Successfully!</h2>";
            echo "<div class='details'>";
            echo "<p><strong>Vendor ID:</strong> $v_id</p>";
            echo "<p><strong>First Name:</strong> $f_name</p>";
            echo "<p><strong>Last Name:</strong> $l_name</p>";
            echo "<p><strong>Contact:</strong> $contact</p>";
            echo "<p><strong>Email:</strong> $email</p>";
            echo "</div>";
            echo "<p>Redirecting to Vendor Form in 3 seconds...</p>";
            echo "<p><a href='vendor.html' style='color: #006400;'>Click here if not redirected</a></p>";
            echo "</div>";
            echo "</body>";
            echo "</html>";
        } else {
            // Error in insertion
            echo "<!DOCTYPE html>";
            echo "<html>";
            echo "<head>";
            echo "<meta http-equiv='refresh' content='5;url=vendor.html'>";
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
            echo "</div>";
            echo "</body>";
            echo "</html>";
        }
    } else {
        // Display validation errors
        echo "<!DOCTYPE html>";
        echo "<html>";
        echo "<head>";
        echo "<meta http-equiv='refresh' content='5;url=vendor.html'>";
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
        echo "<p><a href='vendor.html'>Go back now</a></p>";
        echo "</div>";
        echo "</body>";
        echo "</html>";
    }
} else {
    // If someone tries to access this file directly without submitting the form
    header("Location: vendor.html");
    exit();
}

$conn->close();
?>