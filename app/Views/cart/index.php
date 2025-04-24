<div class="container cart-container" style="padding: 20px; max-width: 900px; margin: 20px auto;">
    <h2><?php echo $data['title']; ?></h2>
    <hr>

    <?php // flash('cart_success'); flash('cart_error'); flash('cart_update'); flash('cart_remove'); 
    ?>

    <?php if (empty($data['cartItems'])): ?>
        <p>Your shopping cart is empty.</p>
        <a href="<?php echo URLROOT; ?>/marketplace" class="btn btn-primary">Continue Shopping</a>
    <?php else: ?>
        <table class="dashboard-table" style="width: 100%;">
            <thead>
                <tr>
                    <th colspan="2">Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['cartItems'] as $item): ?>
                    <tr>
                        <td style="width: 80px;">
                            <img src="<?php echo PRODUCT_IMG_URL_PREFIX . htmlspecialchars(!empty($item->image_path) ? $item->image_path : 'default_product.jpg'); ?>"
                                alt="<?php echo htmlspecialchars($item->name); ?>" style="width: 60px; height: 60px; object-fit: cover;">
                        </td>
                        <td>
                            <a href="<?php echo URLROOT; ?>/products/show/<?php echo htmlspecialchars($item->slug ?? $item->id); ?>">
                                <?php echo htmlspecialchars($item->name); ?>
                            </a>
                            <?php if ($item->stock_issue): ?>
                                <br><small style="color: red;">Requested quantity exceeds stock (<?php echo $item->stock_quantity; ?> available)</small>
                            <?php endif; ?>
                        </td>
                        <td>$<?php echo number_format($item->price, 2); ?></td>
                        <td>
                            <?php // Update Form 
                            ?>
                            <form action="<?php echo URLROOT; ?>/cart/update" method="post" style="display: flex; align-items: center;">
                                <input type="hidden" name="product_id" value="<?php echo $item->id; ?>">
                                <input type="number" name="quantity" value="<?php echo $item->cart_quantity; ?>" min="1" <?php echo ($item->stock_quantity !== null) ? 'max="' . $item->stock_quantity . '"' : ''; ?> style="width: 60px; padding: 5px; margin-right: 5px;">
                                <button type="submit" class="btn btn-secondary btn-sm" style="padding: 2px 6px;">Update</button>
                            </form>
                        </td>
                        <td>$<?php echo number_format($item->subtotal, 2); ?></td>
                        <td>
                            <a href="<?php echo URLROOT; ?>/cart/remove/<?php echo $item->id; ?>" class="btn btn-danger btn-sm" style="padding: 2px 6px;" onclick="return confirm('Remove item?');">Remove</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" style="text-align: right; font-weight: bold;">Total:</td>
                    <td style="font-weight: bold;">$<?php echo number_format($data['cartTotal'], 2); ?></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

        <div style="text-align: right; margin-top: 20px;">
            <a href="<?php echo URLROOT; ?>/marketplace" class="btn btn-secondary">Continue Shopping</a>
            <a href="<?php echo URLROOT; ?>/orders/checkout" class="btn btn-success" style="margin-left: 10px;">Proceed to Checkout</a>
        </div>
    <?php endif; ?>
</div>