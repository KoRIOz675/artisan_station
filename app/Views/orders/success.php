<div class="container order-success-container" style="padding: 40px 20px; text-align: center; max-width: 700px; margin: 20px auto;">
    <h1 style="color: green;">Order Placed Successfully!</h1>
    <p>Thank you for your purchase.</p>
    <p>Your Order ID is: <strong>#<?php echo htmlspecialchars($data['orderId'] ?? 'N/A'); ?></strong></p>
    <p>You can view your order details in your dashboard.</p>
    <hr style="margin: 30px 0;">
    <a href="<?php echo URLROOT; ?>/marketplace" class="btn btn-primary" style="margin-right: 10px;">Continue Shopping</a>
    <a href="<?php echo URLROOT; ?>/users/dashboard#order-history-content" class="btn btn-secondary">View Order History</a>
</div>