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
    $pdt_id = mysqli_real_escape_string($conn, $_POST['pdt_id']);
    $pdt_name = mysqli_real_escape_string($conn, $_POST['pdt_name']);
    $pdt_type = mysqli_real_escape_string($conn, $_POST['pdt_type']);
    $price_per_unit = mysqli_real_escape_string($conn, $_POST['price_per_unit']);
    $description_age = mysqli_real_escape_string($conn, $_POST['description_age']);
    $qty_in_stock = mysqli_real_escape_string($conn, $_POST['qty_in_stock']);
    $unit_of_measure = mysqli_real_escape_string($conn, $_POST['unit_of_measure']);
    $f_id = mysqli_real_escape_string($conn, $_POST['f_id']);
    
    // Basic validation
    $errors = array();
    
    if (empty($pdt_id)) {
        $errors[] = "Product ID is required";
    }
    if (empty($pdt_name)) {
        $errors[] = "Product name is required";
    }
    if (empty($pdt_type)) {
        $errors[] = "Product type is required";
    }
    if (empty($price_per_unit)) {
        $errors[] = "Price per unit is required";
    } elseif (!is_numeric($price_per_unit)) {
        $errors[] = "Price per unit must be a number";
    }
    if (empty($qty_in_stock)) {
        $errors[] = "Quantity in stock is required";
    } elseif (!is_numeric($qty_in_stock)) {
        $errors[] = "Quantity in stock must be a number";
    }
    if (empty($unit_of_measure)) {
        $errors[] = "Unit of measure is required";
    }
    if (empty($f_id)) {
        $errors[] = "Farmer ID is required";
    }
    
    // If no errors, insert into database
    if (empty($errors)) {
        // Insert query for product table
        $sql = "INSERT INTO product (pdt_id, pdt_name, pdt_type, price_per_unit, description_age, qty_in_stock, unit_of_measure, f_id) 
                VALUES ('$pdt_id', '$pdt_name', '$pdt_type', '$price_per_unit', '$description_age', '$qty_in_stock', '$unit_of_measure', '$f_id')";
        
        if ($conn->query($sql) === TRUE) {
            // Success message
            echo "<!DOCTYPE html>";
            echo "<html>";
            echo "<head>";
            echo "<meta http-equiv='refresh' content='3;url=product.html'>";
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
            echo "<h2>Product Added Successfully!</h2>";
            echo "<div class='details'>";
            echo "<p><strong>Product ID:</strong> " . $pdt_id . "</p>";
            echo "<p><strong>Product Name:</strong> " . $pdt_name . "</p>";
            echo "<p><strong>Product Type:</strong> " . $pdt_type . "</p>";
            echo "<p><strong>Price per Unit:</strong> UGX " . number_format($price_per_unit, 2) . "</p>";
            echo "<p><strong>Description/Age:</strong> " . $description_age . "</p>";
            echo "<p><strong>Quantity in Stock:</strong> " . $qty_in_stock . " " . $unit_of_measure . "</p>";
            echo "<p><strong>Farmer ID:</strong> " . $f_id . "</p>";
            echo "</div>";
            echo "<p>Redirecting to Product Form in 3 seconds...</p>";
            echo "<p><a href='product.html' style='color: #006400;'>Click here if not redirected</a></p>";
            echo "</div>";
            echo "</body>";
            echo "</html>";
        } else {
            // Error in insertion
            echo "<!DOCTYPE html>";
            echo "<html>";
            echo "<head>";
            echo "<meta http-equiv='refresh' content='5;url=product.html'>";
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
            echo "<p><a href='product.html' style='color: #006400;'>Go back now</a></p>";
            echo "</div>";
            echo "</body>";
            echo "</html>";
        }
    } else {
        // Display validation errors
        echo "<!DOCTYPE html>";
        echo "<html>";
        echo "<head>";
        echo "<meta http-equiv='refresh' content='5;url=product.html'>";
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
        echo "<p><a href='product.html' style='color: #006400;'>Go back now</a></p>";
        echo "</div>";
        echo "</body>";
        echo "</html>";
    }
} else {
    // If someone tries to access this file directly without submitting the form
    header("Location: product.html");
    exit();
}

$conn->close();
?>