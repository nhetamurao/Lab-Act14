<?php
// Load the Stripe PHP library using Composer's autoloader
require 'vendor/autoload.php';

// Set your Stripe secret key here
\Stripe\Stripe::setApiKey('sk_test_51QKH37K7ux8CqOH7zkGnyBJ8nMNrsPCrtJREfMzdtAFHr8PSE1335izaHyr7jk5UJegmJ0eFlH8rgWuuy7TwOI1L00ymg0ZeWX'); // Replace with your actual secret key

// Fetch the products from Stripe API
try {
    $products = \Stripe\Product::all(['limit' => 10]); // Limit the number of products returned
} catch (\Stripe\Exception\ApiErrorException $e) {
    // Handle error
    echo "Error fetching products: " . $e->getMessage();
    exit;
}

// Start the HTML output
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stripe Products List</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="styles.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <h2 class="text-center mb-4">All - Available Products</h2>
        <?php if (count($products->data) > 0): ?>
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Product</th>
                        <th>Description</th>
                        <th>Price (USD)</th>
                        <th>Image</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products->data as $product): ?>
                        <tr>
                            <td class="fw-bold"><?php echo htmlspecialchars($product->name); ?></td>
                            <td><?php echo htmlspecialchars($product->description); ?></td>
                            <td>
                                <?php
                                // Fetch the prices associated with the product
                                try {
                                    $prices = \Stripe\Price::all(['product' => $product->id, 'limit' => 1]);
                                    if (count($prices->data) > 0) {
                                        $price = $prices->data[0];
                                        echo '$' . number_format($price->unit_amount / 100, 2); // Price is in cents, so we divide by 100
                                    } else {
                                        echo 'Price not available';
                                    }
                                } catch (\Stripe\Exception\ApiErrorException $e) {
                                    echo 'Error fetching price: ' . $e->getMessage();
                                }
                                ?>
                            </td>
                            <td>
                                <?php if (!empty($product->images)): ?>
                                    <img src="<?php echo $product->images[0]; ?>" alt="Product Image" class="img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;">
                                <?php else: ?>
                                    <span class="text-muted fst-italic">No image available</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-center text-muted">No products found in your Stripe account.</p>
        <?php endif; ?>
    </div>
    <!-- Bootstrap JS (Optional) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
