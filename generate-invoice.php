<?php

require "init.php";

// Fetch all customers from Stripe
$customers = $stripe->customers->all();
$products = $stripe->products->all();

// Initialize message
$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get selected customer and products
    $customer_id = $_POST['customer'] ?? '';
    $selected_products = $_POST['products'] ?? [];

    // Create an invoice for the selected customer
    try {
        // Create the invoice
        $invoice = $stripe->invoices->create([
            'customer' => $customer_id
        ]);

        // Add selected products to the invoice as line items
        foreach ($selected_products as $product_id) {
            $product = $stripe->products->retrieve($product_id);
            $prices = $stripe->prices->all(['product' => $product->id]);

            // Check for one-time price
            foreach ($prices->data as $price) {
                if ($price->type === 'one_time') {
                    $stripe->invoiceItems->create([
                        'customer' => $customer_id,
                        'price' => $price->id,
                        'invoice' => $invoice->id
                    ]);
                }
            }
        }

        // Finalize the invoice
        $stripe->invoices->finalizeInvoice($invoice->id);
        $invoice = $stripe->invoices->retrieve($invoice->id);

        $invoice_url = $invoice->hosted_invoice_url;
        $invoice_pdf = $invoice->invoice_pdf;

        $message = "Invoice generated successfully! <br> 
                    <a href='$invoice_url' target='_blank'>View Invoice</a><br> 
                    <a href='$invoice_pdf' target='_blank'>Download PDF</a>";
    } catch (\Stripe\Exception\ApiErrorException $e) {
        $message = "Error creating invoice: " . $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Invoice</title>
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
            width: 500px;
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
        select, input[type="checkbox"] {
            margin-bottom: 15px;
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
        <h2>Create an Invoice</h2>
        <form method="POST" action="">
            <!-- Dropdown for Customers -->
            <label for="customer">Select Customer</label>
            <select name="customer" id="customer" required>
                <option value="">Select a customer</option>
                <?php foreach ($customers->data as $customer): ?>
                    <option value="<?php echo htmlspecialchars($customer->id); ?>"><?php echo htmlspecialchars($customer->name); ?></option>
                <?php endforeach; ?>
            </select>

            <!-- Product Selection -->
            <label for="products">Select Products</label>
            <div>
                <?php foreach ($products->data as $product): ?>
                    <label>
                        <input type="checkbox" name="products[]" value="<?php echo htmlspecialchars($product->id); ?>">
                        <?php echo htmlspecialchars($product->name); ?>
                    </label><br>
                <?php endforeach; ?>
            </div>

            <button type="submit">Create Invoice</button>
        </form>

        <?php if (!empty($message)): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
    </div>
</body>
</html>
