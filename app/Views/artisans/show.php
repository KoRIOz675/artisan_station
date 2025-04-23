<?php
// Extract data for easier access
$artisan = $data['artisan'] ?? null;
$products = $data['products'] ?? [];
?>

<div class="container artisan-profile-container" style="padding: 20px;">

    <?php if ($artisan && is_object($artisan)): ?>

        <!-- Artisan Header Section -->
        <div class="artisan-header" style="display: flex; align-items: center; gap: 30px; margin-bottom: 30px; background-color: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #eee;">
            <img src="<?php echo URLROOT . '/img/artists/' . htmlspecialchars(!empty($artisan->profile_picture_path) ? $artisan->profile_picture_path : 'default_artist.jpg'); ?>"
                alt="<?php echo htmlspecialchars($artisan->shop_name ?? $artisan->username); ?>"
                style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 3px solid #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <div>
                <h1 style="margin: 0 0 5px 0;"><?php echo htmlspecialchars($artisan->shop_name ?? $artisan->username); ?></h1>
                <?php if (!empty($artisan->shop_name) && $artisan->shop_name != $artisan->username): ?>
                    <p style="margin: 0 0 10px 0; color: #555;"><em>(@<?php echo htmlspecialchars($artisan->username); ?>)</em></p>
                <?php endif; ?>
                <p style="margin: 0; line-height: 1.6; color: #333;">
                    <?php echo nl2br(htmlspecialchars($artisan->bio ?? 'No biography provided.')); ?>
                </p>
                <p style="font-size: 0.85em; color: #777; margin-top: 10px;">Member since: <?php echo date('F Y', strtotime($artisan->created_at ?? '')); ?></p>
            </div>
        </div>
        <!-- End Artisan Header -->

        <hr style="margin: 30px 0;">

        <!-- Artisan's Products Section -->
        <section class="artisan-products-section">
            <h2 style="margin-bottom: 20px;" id="artworks">Artwork by <?php echo htmlspecialchars($artisan->shop_name ?? $artisan->username); ?></h2> <?php // Added id="artworks" for potential linking 
                                                                                                                                                        ?>

            <?php if (!empty($products)): ?>
                <div class="product-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px;">
                    <?php foreach ($products as $product): ?>
                        <?php if (is_object($product)): ?>
                            <div class="product-card" style="border: 1px solid #eee; border-radius: 5px; overflow: hidden; background-color: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                                <?php // Link to the specific product page using THIS artisan's username 
                                ?>
                                <a href="<?php echo URLROOT; ?>/artisans/<?php echo htmlspecialchars($artisan->username); ?>/products/<?php echo htmlspecialchars($product->slug ?? $product->id); ?>" style="text-decoration: none; color: inherit;">
                                    <img src="<?php echo PRODUCT_IMG_URL_PREFIX . htmlspecialchars(!empty($product->image_path) ? $product->image_path : 'default_product.jpg'); ?>"
                                        alt="<?php echo htmlspecialchars($product->name ?? 'Product'); ?>"
                                        style="width: 100%; height: 200px; object-fit: cover; display: block;">
                                    <div class="product-card-body" style="padding: 15px;">
                                        <h4 style="margin: 0 0 5px 0; font-size: 1.1em;"><?php echo htmlspecialchars($product->name ?? 'Product Name'); ?></h4>
                                        <p style="font-weight: bold; color: #333; margin: 0;">$<?php echo number_format($product->price ?? 0, 2); ?></p>
                                    </div>
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p><?php echo htmlspecialchars($artisan->shop_name ?? $artisan->username); ?> hasn't listed any products yet.</p>
            <?php endif; ?>
        </section>
        <!-- End Products Section -->

    <?php else: ?>
        <p>Artisan profile not found.</p> <?php // This case handled by controller redirect ideally 
                                            ?>
    <?php endif; ?>

</div>