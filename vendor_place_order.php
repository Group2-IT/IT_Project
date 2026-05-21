<?php
session_start();
header('Content-Type: application/json');

// basic session/role checks
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'vendor' || empty($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized request. Please log in as a vendor.']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$product_id = isset($input['product_id']) ? trim($input['product_id']) : '';
$farmer_id_input = isset($input['farmer_id']) ? trim($input['farmer_id']) : '';
$order_qty = isset($input['order_qty']) ? trim($input['order_qty']) : '';
$vendor_id = $_SESSION['user_id'];

if ($product_id === '' || $order_qty === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Product and quantity are required.']);
    exit();
}

if (!is_numeric($order_qty) || intval($order_qty) <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Please enter a valid order quantity.']);
    exit();
}

$order_qty = intval($order_qty);

$mysqli = new mysqli('localhost', 'root', '', 'vegetable_database');
if ($mysqli->connect_error) {
    http_response_code(500);
    error_log('DB connect error in vendor_place_order: ' . $mysqli->connect_error);
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit();
}

// fetch product details
$stmt = $mysqli->prepare('SELECT qty_in_stock, price_per_unit, f_id FROM product WHERE pdt_id = ? LIMIT 1');
if (!$stmt) {
    http_response_code(500);
    error_log('Prepare failed: ' . $mysqli->error);
    echo json_encode(['success' => false, 'message' => 'Server error.']);
    $mysqli->close();
    exit();
}

$stmt->bind_param('s', $product_id);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Product not found.']);
    $stmt->close();
    $mysqli->close();
    exit();
}

$prod = $res->fetch_assoc();

// check stock
if ($order_qty > intval($prod['qty_in_stock'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Requested quantity exceeds available stock.']);
    $stmt->close();
    $mysqli->close();
    exit();
}

// determine farmer id
$farmer_id_final = $farmer_id_input !== '' ? $farmer_id_input : $prod['f_id'];

$price_per_unit = floatval($prod['price_per_unit']);
$total_amount = $price_per_unit * $order_qty;
$order_date = date('Y-m-d');

// generate custom sequential order ID
$result = $mysqli->query("SELECT order_id FROM orders ORDER BY order_id DESC LIMIT 1");
if ($result && $result->num_rows > 0) {
    $lastOrder = $result->fetch_assoc();
    $lastNum = intval(substr($lastOrder['order_id'], 3)); // strip 'ORD'
    $nextNum = $lastNum + 1;
} else {
    $nextNum = 1; // first order
}
$order_id = 'ORD' . $nextNum;

$ins = $mysqli->prepare('INSERT INTO orders (order_id, pdt_id, order_qty, order_date, delivery_status, payment_status, total_amount, payment_method, v_id, f_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
if (!$ins) {
    http_response_code(500);
    error_log('Prepare insert failed: ' . $mysqli->error);
    echo json_encode(['success' => false, 'message' => 'Server error.']);
    $stmt->close();
    $mysqli->close();
    exit();
}

$delivery_status = 'Pending';
$payment_status = 'Pending';
$payment_method = 'Pending';

$ins->bind_param('ssisssdsss', $order_id, $product_id, $order_qty, $order_date, $delivery_status, $payment_status, $total_amount, $payment_method, $vendor_id, $farmer_id_final);

if ($ins->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Order submitted successfully.',
        'order_id' => $order_id,
        'product_id' => $product_id,
        'farmer_id' => $farmer_id_final,
        'total_amount' => $total_amount,
    ]);
} else {
    http_response_code(500);
    error_log('Insert failed: ' . $ins->error);
    echo json_encode(['success' => false, 'message' => 'Unable to submit order.']);
}

$ins->close();
$stmt->close();
$mysqli->close();
?>
