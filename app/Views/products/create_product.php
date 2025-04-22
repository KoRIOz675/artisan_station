<div class="container create-product-container" style="max-width: 800px; margin: 30px auto; padding: 20px; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px;">

    <h2>Add New Artwork / Product</h2>
    <hr style="margin: 1.5em 0;">
    <?php if (!empty($data['general_err'])): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($data['general_err']); ?></div>
    <?php endif; ?>

    <form action="<?php echo URLROOT; ?>/products/store" method="post" novalidate enctype="multipart/form-data">

        <!-- Product Name -->
        <div class="form-group" style="margin-bottom: 15px; display: flex; flex-direction: column;">
            <label for="name" style="color: #735339; margin-bottom: 5px;">Product Name: <sup>*</sup></label>
            <input type="text" name="name" style="margin: 0 1em; border: 1px solid #800000; border-radius: 5px; height: 30px; padding: 0 8px; font-size: medium;" id="name" class="form-control <?php echo (!empty($data['name_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($data['name']); ?>" required>
            <span class="invalid-feedback" style="color: #D8000C; font-size: 0.85em; display: block;"><?php echo $data['name_err']; ?></span>
        </div>

        <!-- Description -->
        <div class="form-group" style="margin-bottom: 15px; display: flex; flex-direction: column;">
            <label for="description" style="color: #735339; margin-bottom: 5px;">Description: <sup>*</sup></label>
            <textarea name="description" id="description" style="margin: 0 1em; border: 1px solid #800000; border-radius: 5px; height: 180px; padding: 8px 8px; font-size: medium; resize: vertical;" class="form-control <?php echo (!empty($data['description_err'])) ? 'is-invalid' : ''; ?>" rows="6" required><?php echo htmlspecialchars($data['description']); ?></textarea>
            <span class="invalid-feedback" style="color: #D8000C; font-size: 0.85em; display: block;"><?php echo $data['description_err']; ?></span>
        </div>

        <!-- Price -->
        <div class="form-group" style="margin-bottom: 15px; display: flex; flex-direction: column;">
            <label for="price" style="color: #735339; margin-bottom: 5px;">Price ($): <sup>*</sup></label>
            <input type="number" name="price" id="price" style="margin: 0 1em; border: 1px solid #800000; border-radius: 5px; height: 30px; padding: 0 8px; font-size: medium;" step="0.01" min="0" class="form-control <?php echo (!empty($data['price_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($data['price']); ?>" required>
            <span class="invalid-feedback" style="color: #D8000C; font-size: 0.85em; display: block;"><?php echo $data['price_err']; ?></span>
        </div>

        <!-- Stock Quantity -->
        <div class="form-group" style="margin-bottom: 15px; display: flex; flex-direction: column;">
            <label for="stock_quantity" style="color: #735339; margin-bottom: 5px;">Stock Quantity: <sup>*</sup> (Enter 0 if made-to-order/unique)</label>
            <input type="number" name="stock_quantity" style="margin: 0 1em; border: 1px solid #800000; border-radius: 5px; height: 30px; padding: 0 8px; font-size: medium;" id="stock_quantity" step="1" min="0" class="form-control <?php echo (!empty($data['stock_quantity_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($data['stock_quantity'] ?? '1'); ?>" required> <?php // Default value 1 in HTML 
                                                                                                                                                                                                                                                                                                                                                                                                    ?>
            <span class="invalid-feedback" style="color: #D8000C; font-size: 0.85em; display: block;"><?php echo $data['stock_quantity_err']; ?></span>
        </div>

        <!-- Category -->
        <div class="form-group" style="margin-bottom: 15px; display: flex; flex-direction: column;">
            <label for="category_id" style="color: #735339; margin-bottom: 5px;">Category: <sup>*</sup></label>
            <select name="category_id" id="category_id" style="margin: 0 1em; border: 1px solid #800000; border-radius: 5px; height: 30px; padding: 0 8px; font-size: medium;" class="form-control <?php echo (!empty($data['category_id_err'])) ? 'is-invalid' : ''; ?>" required>
                <option value="">-- Select a Category --</option>
                <?php if (isset($data['categories']) && !empty($data['categories'])): ?>
                    <?php foreach ($data['categories'] as $category): ?>
                        <?php if (is_object($category)): ?>
                            <option value="<?php echo $category->id; ?>" <?php echo (isset($data['category_id']) && $data['category_id'] == $category->id) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category->name); ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <option value="" disabled>No categories available</option>
                <?php endif; ?>
            </select>
            <span class="invalid-feedback" style="color: #D8000C; font-size: 0.85em; display: block;"><?php echo $data['category_id_err']; ?></span>
        </div>


        <!-- Image Upload -->
        <div class="form-group" style="margin-bottom: 15px; display: flex; flex-direction: column;">
            <label for="image" style="color: #735339; margin-bottom: 5px;">Product Image:</label> <?php // Add required if mandatory 
                                                                                                    ?>
            <input type="file" name="image" id="image" class="form-control-file <?php echo (!empty($data['image_err'])) ? 'is-invalid' : ''; ?>">
            <span class="invalid-feedback" style="color: #D8000C; font-size: 0.85em; display: block;"><?php echo $data['image_err'] ?? ''; ?></span>
            <small class="form-text text-muted">Upload main image (JPG, PNG, GIF, WEBP). Max 5MB.</small>
        </div>

        <hr style="margin: 1.5em 0;">

        <!-- Submit Button -->
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Add Product</button>
            <a href="<?php echo URLROOT; ?>/users/dashboard#art-content" class="btn btn-secondary" style="margin-left: 10px;">Cancel</a>
        </div>

    </form>
</div>