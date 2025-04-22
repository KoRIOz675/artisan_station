<div class="container admin-form-container" style="max-width: 700px; margin: 30px auto; padding: 20px;">
    <h2>Edit Category: <?php echo htmlspecialchars($data['category']->name); ?></h2>
    <?php // flash('error'); 
    ?>
    <?php if (!empty($data['general_err'])): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($data['general_err']); ?></div>
    <?php endif; ?>

    <form action="<?php echo URLROOT; ?>/admin/updateCategory/<?php echo $data['id']; ?>" method="post" enctype="multipart/form-data">

        <div class="form-group">
            <label for="name">Category Name: <sup>*</sup></label>
            <input type="text" name="name" id="name" class="form-control <?php echo (!empty($data['name_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($data['name']); ?>" required>
            <span class="invalid-feedback"><?php echo $data['name_err']; ?></span>
        </div>

        <div class="form-group">
            <label for="description">Description:</label>
            <textarea name="description" id="description" class="form-control <?php echo (!empty($data['description_err'])) ? 'is-invalid' : ''; ?>" rows="4"><?php echo htmlspecialchars($data['description']); ?></textarea>
            <span class="invalid-feedback"><?php echo $data['description_err']; ?></span>
        </div>

        <div class="form-group">
            <label>Current Image:</label>
            <div>
                <?php if (!empty($data['current_image'])): ?>
                    <img src="<?php echo CATEGORY_IMG_URL_PREFIX . htmlspecialchars($data['current_image']); ?>" alt="Current Image" style="max-width: 100px; max-height: 100px; margin-bottom: 10px;">
                <?php else: ?>
                    <p>No image uploaded.</p>
                <?php endif; ?>
            </div>
            <label for="image">Upload New Image: (Optional - replaces current)</label>
            <input type="file" name="image" id="image" class="form-control-file <?php echo (!empty($data['image_err'])) ? 'is-invalid' : ''; ?>">
            <span class="invalid-feedback"><?php echo $data['image_err'] ?? ''; ?></span>
            <small class="form-text text-muted">Upload an image (JPG, PNG, GIF, WEBP). Max 2MB.</small>
        </div>

        <button type="submit" class="btn btn-primary">Update Category</button>
        <a href="<?php echo URLROOT; ?>/admin/manageCategories" class="btn btn-secondary">Cancel</a>
    </form>
</div>