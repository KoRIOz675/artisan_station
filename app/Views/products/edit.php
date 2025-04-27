<?php
$product = $data['product'] ?? null;
?>

<div class="container edit-product-container" style="max-width: 800px; margin: 30px auto; padding: 20px; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px;">

    <h2>Edit Art Piece: <?php echo htmlspecialchars($product->name ?? ''); ?></h2>
    <hr style="margin: 1.5em 0;">

    <form action="<?php echo URLROOT; ?>/products/update/<?php echo $data['id']; ?>" method="post" novalidate enctype="multipart/form-data">

        <!-- Product Name -->
        <div class="form-group" style="margin-bottom: 15px; display: flex; flex-direction: column;">
            <label style="color: #735339; margin-bottom: 5px;" for="name">Product Name: <sup>*</sup></label>
            <input style="margin: 0 1em; border: 1px solid #800000; border-radius: 5px; height: 30px; padding: 0 8px; font-size: medium; font-family: Alata, sans-serif;" type="text" name="name" id="name" class="form-control <?php echo (!empty($data['name_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($data['name']); ?>" required>
            <span class="invalid-feedback"><?php echo $data['name_err']; ?></span>
        </div>

        <!-- Description -->
        <div class="form-group" style="margin-bottom: 15px; display: flex; flex-direction: column;">
            <label style="color: #735339; margin-bottom: 5px;" for="description">Description: <sup>*</sup></label>
            <textarea style="margin: 0 1em; border: 1px solid #800000; border-radius: 5px; height: 180px; padding: 8px 8px; font-size: medium; resize: vertical; font-family: Alata, sans-serif;" style="margin: 0 1em; border: 1px solid #800000; border-radius: 5px; height: 180px; padding: 8px 8px; font-size: medium; resize: vertical; font-family: Alata, sans-serif;" name="description" id="description" class="form-control <?php echo (!empty($data['description_err'])) ? 'is-invalid' : ''; ?>" rows="6" required><?php echo htmlspecialchars($data['description']); ?></textarea>
            <span class="invalid-feedback"><?php echo $data['description_err']; ?></span>
        </div>

        <!-- Price -->
        <div class="form-group" style="margin-bottom: 15px; display: flex; flex-direction: column;">
            <label style="color: #735339; margin-bottom: 5px;" for="price">Price ($): <sup>*</sup></label>
            <input style="margin: 0 1em; border: 1px solid #800000; border-radius: 5px; height: 30px; padding: 0 8px; font-size: medium; font-family: Alata, sans-serif;" type="number" name="price" id="price" step="0.01" min="0" class="form-control <?php echo (!empty($data['price_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($data['price']); ?>" required>
            <span class="invalid-feedback"><?php echo $data['price_err']; ?></span>
        </div>

        <!-- Stock Quantity -->
        <div class="form-group" style="margin-bottom: 15px; display: flex; flex-direction: column;">
            <label style="color: #735339; margin-bottom: 5px;" for="stock_quantity">Stock Quantity: <sup>*</sup> (0 = Made-to-order)</label>
            <input style="margin: 0 1em; border: 1px solid #800000; border-radius: 5px; height: 30px; padding: 0 8px; font-size: medium; font-family: Alata, sans-serif;" type="number" name="stock_quantity" id="stock_quantity" step="1" min="0" class="form-control <?php echo (!empty($data['stock_quantity_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($data['stock_quantity']); ?>" required>
            <span class="invalid-feedback"><?php echo $data['stock_quantity_err']; ?></span>
        </div>

        <!-- Category -->
        <div class="form-group" style="margin-bottom: 15px; display: flex; flex-direction: column;">
            <label style="color: #735339; margin-bottom: 5px;" for="category_id">Category: <sup>*</sup></label>
            <select style="margin: 0 1em; border: 1px solid #800000; border-radius: 5px; height: 30px; padding: 0 8px; font-size: medium; font-family: Alata, sans-serif;" name="category_id" id="category_id" class="form-control <?php echo (!empty($data['category_id_err'])) ? 'is-invalid' : ''; ?>" required>
                <option value="">-- Select a Category --</option>
                <?php foreach ($data['categories'] as $category): if (is_object($category)): ?>
                        <option value="<?php echo $category->id; ?>" <?php echo ($data['category_id'] == $category->id) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category->name); ?>
                        </option>
                <?php endif;
                endforeach; ?>
            </select>
            <span class="invalid-feedback"><?php echo $data['category_id_err']; ?></span>
        </div>

        <!-- Active Status Checkbox -->
        <div class="form-group form-check" style="margin-bottom: 15px; display: flex; flex-direction: row;">
            <label style="color: #735339; margin-bottom: 5px;" for="is_active" class="form-check-label">Active (Visible in Marketplace)</label>
            <input style="margin: 0 1em" type="checkbox" name="is_active" id="is_active" value="1" <?php echo ($data['is_active'] ?? 1) ? 'checked' : ''; ?>>
        </div>


        <!-- Image Upload -->
        <div class="form-group" style="margin-bottom: 15px; display: flex; flex-direction: column;">
            <label style="color: #735339; margin-bottom: 5px;">Current Image:</label>
            <div>
                <?php
                $currentImage = $data['current_image'] ?? null;
                $imageSrc = !empty($currentImage)
                    ? PRODUCT_IMG_URL_PREFIX . htmlspecialchars($currentImage)
                    : PRODUCT_IMG_URL_PREFIX . 'default_product.jpg';
                ?>
                <img src="<?php echo $imageSrc; ?>" alt="Current Product Image" style="max-width: 150px; height: auto; margin-bottom: 10px; border: 1px solid #ccc;">
            </div>
            <label style="color: #735339; margin-bottom: 5px;" for="image">Upload New Image: (Optional - replaces current)</label>
            <input type="file" name="image" id="image" class="form-control-file <?php echo (!empty($data['image_err'])) ? 'is-invalid' : ''; ?>" accept="image/*">
            <span class="invalid-feedback"><?php echo $data['image_err'] ?? ''; ?></span>
            <small class="form-text text-muted">Max 5MB. JPG, PNG, GIF, WEBP.</small>
            <?php // Optional: Add checkbox to remove image 
            ?>
            <!-- <input type="checkbox" name="remove_image" value="1"> Remove Current Image -->
        </div>

        <hr style="margin: 1.5em 0;">

        <!-- Submit Button -->
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Update Art Piece</button>
            <a href="<?php echo URLROOT; ?>/users/dashboard#art-content" class="btn btn-secondary" style="margin-left: 10px;">Cancel</a>
        </div>

    </form>
</div>