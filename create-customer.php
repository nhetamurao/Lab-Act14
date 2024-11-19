<?php
// Load the Stripe PHP library using Composer's autoloader
require 'vendor/autoload.php';

// Set your Stripe secret key here
\Stripe\Stripe::setApiKey('sk_test_51QKH37K7ux8CqOH7zkGnyBJ8nMNrsPCrtJREfMzdtAFHr8PSE1335izaHyr7jk5UJegmJ0eFlH8rgWuuy7TwOI1L00ymg0ZeWX'); // Replace with your actual secret key

// Initialize a message variable
$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = [
        'line1' => $_POST['address'] ?? '',
    ];

    // Create a customer using Stripe API
    try {
        $customer = \Stripe\Customer::create([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
        ]);

        $message = "Customer created successfully! Customer ID: " . $customer->id;
    } catch (\Stripe\Exception\ApiErrorException $e) {
        $message = "Error creating customer: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Customer</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 400px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            font-weight: bold;
            margin-bottom: 5px;
        }
        input {
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button {
            padding: 10px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background: #45a049;
        }
        .message {
            margin-top: 15px;
            text-align: center;
            font-weight: bold;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Create a Customer</h2>
        <form method="POST" action="">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" placeholder="Enter full name" required>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="Enter email address" required>

            <label for="phone">Phone</label>
            <input type="tel" id="phone" name="phone" placeholder="Enter phone number" required>

            <label for="address">Address</label>
            <input type="text" id="address" name="address" placeholder="Enter address" required>

            <button type="submit">Create Customer</button>
        </form>
        <?php if (!empty($message)): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
    </div>
</body>
</html>
