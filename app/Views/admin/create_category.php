<div class="container create-product-container" style="max-width: 800px; margin: 30px auto; padding: 20px; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px;">
    <h2>Add New Category</h2>
    <hr style="margin: 1.5em 0;">
    <?php if (!empty($data['general_err'])): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($data['general_err']); ?></div>
    <?php endif; ?>

    <form action="<?php echo URLROOT; ?>/admin/storeCategory" method="post" enctype="multipart/form-data">

        <div class="form-group" style="margin-bottom: 15px; display: flex; flex-direction: column;">
            <label for="name" style="color: #735339; margin-bottom: 5px;">Category Name: <sup>*</sup></label>
            <input type="text" style="margin: 0 1em; border: 1px solid #800000; border-radius: 5px; height: 30px; padding: 0 8px; font-size: medium;" name="name" id="name" class="form-control <?php echo (!empty($data['name_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($data['name']); ?>" required>
            <span class="invalid-feedback" style="color: #D8000C; font-size: 0.85em; display: block;"><?php echo $data['name_err']; ?></span>
        </div>

        <div class="form-group" style="margin-bottom: 15px; display: flex; flex-direction: column;">
            <label for="description" style="color: #735339; margin-bottom: 5px;">Description:</label>
            <textarea name="description" style="margin: 0 1em; border: 1px solid #800000; border-radius: 5px; height: 120px; padding: 8px 8px; font-size: medium; resize: vertical;" id="description" class="form-control <?php echo (!empty($data['description_err'])) ? 'is-invalid' : ''; ?>" rows="4"><?php echo htmlspecialchars($data['description']); ?></textarea>
            <span class="invalid-feedback"><?php echo $data['description_err']; ?></span>
        </div>

        <div class="form-group" style="margin-bottom: 15px; display: flex; flex-direction: column;">
            <label for="image" style="color: #735339; margin-bottom: 5px;">Category Image: (Optional)</label>
            <input type="file" name="image" id="image" class="form-control-file <?php echo (!empty($data['image_err'])) ? 'is-invalid' : ''; ?>">
            <span class="invalid-feedback"><?php echo $data['image_err'] ?? ''; ?></span>
            <small class="form-text text-muted">Upload an image (JPG, PNG, GIF, WEBP). Max 2MB.</small>
        </div>

        <button type="submit" class="btn btn-primary">Create Category</button>
        <a href="<?php echo URLROOT; ?>/admin/manageCategories" class="btn btn-secondary">Cancel</a>
    </form>
</div>