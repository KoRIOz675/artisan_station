<div class="container marketplace-container" style="padding: 20px;">

    <h1><?php echo htmlspecialchars($data['title']); ?></h1>

    <!-- Filter and Search Bar -->
    <div class="filter-search-bar" style="background-color: #f8f9fa; padding: 15px; border-radius: 15px; margin-bottom: 30px; border: 1px solid #eee;">
        <form action="<?php echo URLROOT; ?>/marketplace" method="GET" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: left; flex-direction: column; width: 100%;">

            <div class="filter-group" style="display: flex; flex-direction: column; justify-content: left; width: 100%; margin-bottom: 15px;">
                <label for="search-input" style="font-size: 30px; margin: 0.2em 1.3em">Search:</label>
                <input type="search" style="width: 100%;  padding: 1em 1em; border-radius: 1em; border: none; background-color: #f0f0f0; outline: #d9d9d9 solid 3px; outline-offset: -3px;" name="search" id="search-input" class="form-control form-control-sm" placeholder="Product, artist, keyword..." value="<?php echo htmlspecialchars($data['active_search_term'] ?? ''); ?>">
            </div>

            <div style="display: flex; gap: 15px; flex-wrap: wrap; justify-content: space-between ;">
                <div style="display: flex; gap: 15px; flex-wrap: wrap; justify-content: space-between ;">
                    <div class="filter-group" style="display: flex; align-items: center; justify-content: center;">
                        <label for="category-filter" style="font-size: 20px;">Category:</label>
                        <select name="category" id="category-filter" class="form-control form-control-sm"
                            style="margin: 0 1em; border: 1px solid #800000; border-radius: 5px; height: 30px; padding: 0 8px; font-size: medium; font-family: Alata, sans-serif;">
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

                    <div class="filter-group" style="display: flex; align-items: center; justify-content: center;">
                        <label for="sort_by" style="font-size: 20px;">Sort:</label>
                        <select name="sort_by" id="sort_by" class="form-control form-control-sm filter-input"
                            style="margin: 0 1em; border: 1px solid #800000; border-radius: 5px; height: 30px; padding: 0 8px; font-size: medium; font-family: Alata, sans-serif;">
                            <option value="" <?php echo ($data['active_sort_by'] == '') ? 'selected' : ''; ?>>Newest</option>
                            <option value="price_asc" <?php echo ($data['active_sort_by'] == 'price_asc') ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price_desc" <?php echo ($data['active_sort_by'] == 'price_desc') ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="name_asc" <?php echo ($data['active_sort_by'] == 'name_asc') ? 'selected' : ''; ?>>Name: A to Z</option>
                        </select>
                    </div>

                    <div class="filter-group" style="display: flex; align-items: center; justify-content: center;">
                        <label for="min_price" style="font-size: 20px;">Price:</label>
                        <input type="number" name="min_price" id="min_price" placeholder="Min $" step="0.01" min="0"
                            value="<?php echo htmlspecialchars($data['active_min_price'] ?? ''); ?>"
                            class="form-control form-control-sm filter-input" style="margin: 0 1em; border: 1px solid #800000; border-radius: 5px; height: 30px; padding: 0 8px; font-size: medium; font-family: Alata, sans-serif;">
                        <span style="margin: 0 5px;">-</span>
                        <input type="number" name="max_price" id="max_price" placeholder="Max $" step="0.01" min="0"
                            value="<?php echo htmlspecialchars($data['active_max_price'] ?? ''); ?>"
                            class="form-control form-control-sm filter-input" style="margin: 0 1em; border: 1px solid #800000; border-radius: 5px; height: 30px; padding: 0 8px; font-size: medium; font-family: Alata, sans-serif;">
                    </div>
                </div>

                <div class="filter-group" style="display: flex; align-items: center; justify-content: center; margin: 0 2em; gap: 15px;">
                    <button type="submit" class="btn btn-primary btn-sm">Search</button>
                    <a href="<?php echo URLROOT; ?>/marketplace" class="btn btn-secondary">Clear</a>
                </div>
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
                                <form action="<?php echo URLROOT; ?>/cart/add" method="POST" style="margin-top: 10px;">
                                    <input type="hidden" name="product_id" value="<?php echo $product->id; ?>">
                                    <input type="hidden" name="quantity" value="1"> <?php // Default quantity 1 
                                                                                    ?>
                                    <?php // Basic stock check before showing button 
                                    ?>
                                    <?php if (isset($product->stock_quantity) && $product->stock_quantity !== 0): ?>
                                        <button type="submit" class="btn btn-sm btn-primary">Add to Cart</button>
                                    <?php else: ?>
                                        <span style="font-size: 0.8em; color: #6c757d;">Out of Stock</span>
                                    <?php endif; ?>
                                </form>
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