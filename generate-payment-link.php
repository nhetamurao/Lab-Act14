<?php

require "init.php";

// Initialize message
$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get selected products
    $selected_products = $_POST['products'] ?? [];

    // Prepare line items for the payment link
    $line_items = [];

    foreach ($selected_products as $product_id) {
        try {
            // Retrieve the product's price
            $prices = $stripe->prices->all(['product' => $product_id]);
            
            foreach ($prices->data as $price) {
                if ($price->type === 'one_time') {
                    // Add product as a line item (assuming quantity 1)
                    $line_items[] = [
                        'price' => $price->id,
                        'quantity' => 1,
                    ];
                }
            }
        } catch (\Stripe\Exception\ApiErrorException $e) {
            $message = "Error fetching product details: " . $e->getMessage();
        }
    }

    // Create a payment link if there are line items
    if (count($line_items) > 0) {
        try {
            $payment_link = $stripe->paymentLinks->create([
                'line_items' => $line_items
            ]);
            
            // Redirect to the payment link URL
            header('Location: ' . $payment_link->url);
            exit;
        } catch (\Stripe\Exception\ApiErrorException $e) {
            $message = "Error generating payment link: " . $e->getMessage();
        }
    } else {
        $message = "Please select at least one product.";
    }
}

// Fetch all products for the form
$products = $stripe->products->all();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Payment Link</title>
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
            width: 600px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        .product-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-bottom: 15px;
        }
        .product-item {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .product-item img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }
        .product-item label {
            display: flex;
            flex-direction: column;
        }
        .product-item .price {
            color: #4CAF50;
            font-weight: bold;
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
        <h2>Select Products and Generate Payment Link</h2>
        <form method="POST" action="">
            <!-- Product Selection -->
            <div class="product-list">
                <?php foreach ($products->data as $product): ?>
                    <?php 
                    // Fetch price for the product
                    $price_display = 'Price unavailable';
                    try {
                        $prices = $stripe->prices->all(['product' => $product->id]);
                        foreach ($prices->data as $price) {
                            if ($price->type === 'one_time') {
                                $price_display = '$' . number_format($price->unit_amount / 100, 2);
                                break;
                            }
                        }
                    } catch (\Stripe\Exception\ApiErrorException $e) {
                        $price_display = 'Price unavailable';
                    }
                    ?>
                    <div class="product-item">
                        <img src="<?php echo !empty($product->images) ? htmlspecialchars($product->images[0]) : 'https://via.placeholder.com/80'; ?>" alt="Product Image">
                        <label>
                            <input type="checkbox" name="products[]" value="<?php echo htmlspecialchars($product->id); ?>">
                            <?php echo htmlspecialchars($product->name); ?> 
                            <span class="price"><?php echo htmlspecialchars($price_display); ?></span>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="submit">Generate Payment Link</button>
        </form>

        <?php if (!empty($message)): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
    </div>
</body>
</html>
