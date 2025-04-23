<?php
// Extract product data for easier access (check if it exists first)
$product = $data['product'] ?? null;
?>

<div class="container product-page-container" style="padding: 20px;">

    <?php if ($product && is_object($product)): ?>
        <?php // Basic Two Column Layout 
        ?>
        <div class="product-details-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">

            <!-- Left Column: Image(s) -->
            <div class="product-images">
                <img src="<?php echo PRODUCT_IMG_URL_PREFIX . htmlspecialchars(!empty($product->image_path) ? $product->image_path : 'default_product.jpg'); ?>"
                    alt="<?php echo htmlspecialchars($product->name); ?>"
                    style="max-width: 100%; height: auto; border-radius: 5px; border: 1px solid #eee;">
                <?php // Add logic for multiple images/gallery later if needed 
                ?>
            </div>

            <!-- Right Column: Details & Actions -->
            <div class="product-info">
                <h1><?php echo htmlspecialchars($product->name); ?></h1>

                <div class="product-meta" style="margin-bottom: 15px; font-size: 0.9em; color: #666;">
                    <span>Category:
                        <a href="<?php echo URLROOT . '/marketplace/category/' . htmlspecialchars($product->category_slug ?? ''); ?>">
                            <?php echo htmlspecialchars($product->category_name ?? 'N/A'); ?>
                        </a>
                    </span> |
                    <span>Artisan:
                        <a href="<?php echo URLROOT . '/artisans/show/' . htmlspecialchars($product->artisan_username ?? ''); ?>">
                            <?php echo htmlspecialchars($product->shop_name ?? $product->artisan_username ?? 'N/A'); ?>
                        </a>
                    </span>
                </div>

                <div class="product-price" style="font-size: 1.8em; font-weight: bold; color: #B85C38; /* Example price color */ margin-bottom: 20px;">
                    $<?php echo number_format($product->price ?? 0, 2); ?>
                </div>

                <div class="product-description" style="line-height: 1.6; margin-bottom: 20px;">
                    <h3>Description</h3>
                    <p><?php echo nl2br(htmlspecialchars($product->description ?? 'No description available.')); ?></p> <?php // nl2br to respect line breaks 
                                                                                                                        ?>
                </div>

                <div class="product-stock" style="margin-bottom: 25px;">
                    <?php if (isset($product->stock_quantity) && $product->stock_quantity > 0): ?>
                        <span style="color: green;">In Stock (<?php echo $product->stock_quantity; ?> available)</span>
                    <?php elseif (isset($product->stock_quantity) && $product->stock_quantity === 0): ?>
                        <span style="color: orange;">Made to Order / Out of Stock</span>
                    <?php else: ?>
                        <span style="color: grey;">Stock information unavailable</span>
                    <?php endif; ?>
                </div>

                <!-- Add to Cart Form (Placeholder) -->
                <form action="<?php echo URLROOT; ?>/cart/add/<?php echo $product->id; ?>" method="POST">
                    <?php // Add quantity input if stock > 0 or if made-to-order allows 
                    ?>
                    <?php if (isset($product->stock_quantity) && $product->stock_quantity !== 0): // Allow adding if stock is > 0 OR null/undefined (assumed available) 
                    ?>
                        <label for="quantity" style="margin-right: 10px;">Quantity:</label>
                        <input type="number" id="quantity" name="quantity" value="1" min="1" <?php echo ($product->stock_quantity > 0) ? 'max="' . $product->stock_quantity . '"' : ''; ?> style="width: 60px; padding: 5px;">
                        <button type="submit" class="btn btn-primary" style="margin-left: 15px;">Add to Cart</button>
                    <?php else: ?>
                        <button type="button" class="btn btn-secondary" disabled>Out of Stock</button>
                    <?php endif; ?>
                </form>
                <!-- End Add to Cart Form -->

            </div>
        </div>

        <hr style="margin: 40px 0;">

        <!-- Related Artisan Info (Optional) -->
        <div class="related-artisan-info">
            <h3>About the Artisan</h3>
            <div style="display:flex; align-items:center; gap: 15px;">
                <img src="<?php echo URLROOT . '/img/artists/' . htmlspecialchars(!empty($product->artisan_image) ? $product->artisan_image : 'default_artist.jpg'); ?>" alt="<?php echo htmlspecialchars($product->shop_name ?? $product->artisan_username); ?>" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover;">
                <div>
                    <h4><a href="<?php echo URLROOT . '/artisans/show/' . htmlspecialchars($product->artisan_username ?? ''); ?>"><?php echo htmlspecialchars($product->shop_name ?? $product->artisan_username); ?></a></h4>
                    <p style="font-size: 0.9em; color: #555;"><?php echo htmlspecialchars(substr($product->artisan_bio ?? '', 0, 150)); ?>...</p>
                </div>
            </div>
        </div>

    <?php else: ?>
        <p>Product details could not be loaded.</p>
    <?php endif; ?>

</div>