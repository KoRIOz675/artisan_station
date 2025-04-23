<div class="container marketplace-container" style="padding: 20px;">

    <h1><?php echo htmlspecialchars($data['title']); ?></h1>

    <!-- Filter and Search Bar -->
    <div class="filter-search-bar" style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 30px; border: 1px solid #eee;">
        <form action="<?php echo URLROOT; ?>/marketplace" method="GET" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: center;">

            <div class="filter-group">
                <label for="category-filter" style="margin-right: 5px; font-weight: bold;">Category:</label>
                <select name="category" id="category-filter" class="form-control form-control-sm" style="display: inline-block; width: auto;">
                    <option value="">All Categories</option>
                    <?php if (isset($data['categories']) && !empty($data['categories'])): ?>
                        <?php foreach ($data['categories'] as $category): ?>
                            <?php if (is_object($category)): ?>
                                <option value="<?php echo $category->id; ?>" <?php echo (isset($data['active_category_id']) && $data['active_category_id'] == $category->id) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category->name); ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="filter-group" style="flex-grow: 1;">
                <label for="search-input" style="margin-right: 5px; font-weight: bold;">Search:</label>
                <input type="search" name="search" id="search-input" class="form-control form-control-sm" placeholder="Product, artist, keyword..." value="<?php echo htmlspecialchars($data['active_search_term'] ?? ''); ?>" style="display: inline-block; width: auto; min-width: 250px;">
            </div>

            <div class="filter-group">
                <button type="submit" class="btn btn-primary btn-sm">Filter / Search</button>
                <a href="<?php echo URLROOT; ?>/marketplace" class="btn btn-secondary btn-sm" style="margin-left: 5px;">Clear</a>
            </div>

        </form>
    </div>
    <!-- End Filter Bar -->


    <!-- Product Grid -->
    <div class="product-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px;">

        <?php if (isset($data['products']) && !empty($data['products'])): ?>
            <?php foreach ($data['products'] as $product): ?>
                <?php if (is_object($product)): ?>
                    <div class="product-card" style="border: 1px solid #eee; border-radius: 5px; overflow: hidden; background-color: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                        <a href="<?php echo URLROOT; ?>/artisans/<?php echo htmlspecialchars($product->artisan_username ?? 'na'); ?>/products/<?php echo htmlspecialchars($product->slug ?? $product->id); ?>" style="text-decoration: none; color: inherit;">
                            <img src="<?php echo PRODUCT_IMG_URL_PREFIX . htmlspecialchars(!empty($product->image_path) ? $product->image_path : 'default_product.jpg'); ?>"
                                alt="<?php echo htmlspecialchars($product->name ?? 'Product'); ?>"
                                style="width: 100%; height: 200px; object-fit: cover; display: block;">
                            <div class="product-card-body" style="padding: 15px;">
                                <h4 style="margin: 0 0 5px 0; font-size: 1.1em;"><?php echo htmlspecialchars($product->name ?? 'Product Name'); ?></h4>
                                <p style="font-size: 0.9em; color: #666; margin: 0 0 10px 0;">By:
                                    <a href="<?php echo URLROOT . '/artisans/' . htmlspecialchars($product->artisan_username ?? ''); ?>">
                                        <?php echo htmlspecialchars($product->shop_name ?? $product->artisan_username ?? 'Artisan'); ?>
                                    </a>
                                </p>
                                <p style="font-weight: bold; color: #333; margin: 0;">$<?php echo number_format($product->price ?? 0, 2); ?></p>
                                <?php if (!empty($product->category_name) && !empty($product->category_slug)): ?>
                                    <a href="<?php echo URLROOT; ?>/marketplace/category/<?php echo htmlspecialchars($product->category_slug); ?>" style="font-size: 0.8em; color: #007bff; display:block; margin-top: 5px;"><?php echo htmlspecialchars($product->category_name); ?></a>
                                <?php endif; ?>
                            </div>
                        </a>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="grid-column: 1 / -1; text-align: center;">No products found matching your criteria.</p> <?php // Spans across grid columns 
                                                                                                                ?>
        <?php endif; ?>

    </div>
    <!-- End Product Grid -->

</div>