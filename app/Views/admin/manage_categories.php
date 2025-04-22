<div class="container" style="padding: 20px; text-align: center;">
    <a href="<?php echo URLROOT; ?>/admin/dashboard" class="btn btn-primary">← Back to Dashboard</a>
</div>

<div class="container admin-container" style="padding: 20px;">
    <h2>Manage Categories</h2>
    <hr style="margin: 20px 0; background-color: #800000;">
    <a href="<?php echo URLROOT; ?>/admin/createCategory" class="btn btn-primary">Add New Category</a>

    <table class="dashboard-table" style="width: 100%; border-collapse: collapse; margin: 20px 0; border: 1px solid #dee2e6; background-color: #f0f0f0;">
        <thead>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Name</th>
                <th>Slug</th>
                <th>Description</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody style="text-align: center; border: 1px solid #dee2e6; background-color: #f8f9fa;">
            <?php if (isset($data['categories']) && !empty($data['categories'])): ?>
                <?php foreach ($data['categories'] as $category): ?>
                    <tr>
                        <td><?php echo $category->id; ?></td>
                        <td>
                            <img src="<?php echo CATEGORY_IMG_URL_PREFIX . htmlspecialchars(!empty($category->image_path) ? $category->image_path : 'default_category.png'); ?>"
                                alt="<?php echo htmlspecialchars($category->name); ?>" style="width: 50px; height: 50px; object-fit: cover;">
                            <?php // Add default_category.png to public/img/categories/ 
                            ?>
                        </td>
                        <td><?php echo htmlspecialchars($category->name); ?></td>
                        <td><?php echo htmlspecialchars($category->slug); ?></td>
                        <td><?php echo htmlspecialchars(substr($category->description ?? '', 0, 50)); ?>...</td>
                        <td><?php echo date('Y-m-d', strtotime($category->created_at)); ?></td>
                        <td>
                            <a href="<?php echo URLROOT; ?>/admin/editCategory/<?php echo $category->id; ?>" class="btn btn-sm btn-warning" style="font-size: 0.8em; padding: 2px 5px; color: black; background-color: #ffc107;">Edit</a>
                            <?php // Use a form for DELETE requests 
                            ?>
                            <form action="<?php echo URLROOT; ?>/admin/deleteCategory/<?php echo $category->id; ?>" method="post" style="display: inline;" onsubmit="return confirm('Delete category \'<?php echo htmlspecialchars(addslashes($category->name)); ?>\'? This might affect products!');">
                                <button type="submit" class="btn btn-sm btn-danger" style="font-size: 0.8em; padding: 2px 5px; color: white; background-color: #dc3545;">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">No categories found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>