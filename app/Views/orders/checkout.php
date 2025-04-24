<div class="container checkout-container" style="padding: 20px; max-width: 800px; margin: 20px auto;">
    <h2><?php echo $data['title']; ?></h2>
    <hr>

    <?php // flash('checkout_error'); 
    ?>
    <?php if (!empty($data['general_err'])): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($data['general_err']); ?></div>
    <?php endif; ?>


    <div style="display: flex; flex-wrap: wrap; gap: 30px;">

        <!-- Order Summary -->
        <div style="flex: 1; min-width: 250px; background-color: #f8f9fa; padding: 15px; border-radius: 5px; border: 1px solid #eee;">
            <h4>Order Summary</h4>
            <table style="width: 100%; font-size: 0.9em;">
                <?php foreach ($data['cartItems'] as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item->name); ?> x <?php echo $item->cart_quantity; ?></td>
                        <td style="text-align: right;">$<?php echo number_format($item->subtotal, 2); ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr style="border-top: 1px solid #ccc; font-weight: bold;">
                    <td>Total</td>
                    <td style="text-align: right;">$<?php echo number_format($data['cartTotal'], 2); ?></td>
                </tr>
            </table>
        </div>

        <!-- Checkout Form -->
        <div style="flex: 2; min-width: 300px;">
            <h4>Shipping Information (Basic)</h4>
            <form action="<?php echo URLROOT; ?>/orders/process" method="post">
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="shipping_address">Shipping Address:</label>
                    <textarea name="shipping_address" id="shipping_address" rows="4" class="form-control" placeholder="Enter your full shipping address"><?php echo htmlspecialchars($data['shipping_address'] ?? ''); ?></textarea>
                    <?php // Add error span if needed 
                    ?>
                </div>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="billing_address">Billing Address: (Optional - Same as shipping if blank)</label>
                    <textarea name="billing_address" id="billing_address" rows="4" class="form-control" placeholder="Enter billing address if different"><?php echo htmlspecialchars($data['billing_address'] ?? ''); ?></textarea>
                </div>

                <p style="font-weight: bold; margin-top: 20px;">Payment Method:</p>
                <p><i>(Payment gateway integration needed for real payments. This will just create the order.)</i></p>

                <hr>
                <button type="submit" class="btn btn-success btn-lg">Place Order</button>
                <a href="<?php echo URLROOT; ?>/cart" class="btn btn-secondary" style="margin-left: 10px;">Back to Cart</a>
            </form>
        </div>

    </div>
</div>