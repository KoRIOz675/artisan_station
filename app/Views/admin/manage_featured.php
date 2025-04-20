<div class="container" style="padding: 20px; text-align: center;">
    <a href="<?php echo URLROOT; ?>/admin/dashboard" class="btn btn-primary">← Back to Dashboard</a>
</div>

<div class="container admin-container" style="padding: 20px;">
    <h2>Manage Featured Content</h2>
    <?php // flash('success'); flash('error'); 
    ?>

    <section style="margin-bottom: 30px;">
        <h3>Featured Artisans</h3>
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Shop Name</th>
                    <th>Status</th>
                    <th>Currently Featured?</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (isset($data['artisans']) && !empty($data['artisans'])): ?>
                    <?php foreach ($data['artisans'] as $artisan): ?>
                        <tr>
                            <td><?php echo $artisan->id; ?></td>
                            <td><?php echo htmlspecialchars($artisan->username); ?></td>
                            <td><?php echo htmlspecialchars($artisan->shop_name ?? 'N/A'); ?></td>
                            <td><?php echo $artisan->is_active ? 'Active' : 'Inactive'; ?></td>
                            <td><?php echo $artisan->is_featured_artisan ? '<span style="color: green; font-weight:bold;">Yes</span>' : 'No'; ?></td>
                            <td>
                                <a href="<?php echo URLROOT; ?>/admin/toggleArtisanFeatured/<?php echo $artisan->id; ?>/<?php echo $artisan->is_featured_artisan; ?>" class="btn btn-sm <?php echo $artisan->is_featured_artisan ? 'btn-secondary' : 'btn-primary'; ?>" style="font-size: 0.8em; padding: 2px 5px; color: white; background-color: <?php echo $artisan->is_featured_artisan ? '#6c757d' : '#007bff'; ?>;">
                                    <?php echo $artisan->is_featured_artisan ? 'Unfeature' : 'Feature'; ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">No artisans found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>

    <section>
        <h3>Featured Products</h3>
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Product Name</th>
                    <th>Artisan</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Currently Featured?</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (isset($data['products']) && !empty($data['products'])): ?>
                    <?php foreach ($data['products'] as $product): ?>
                        <tr>
                            <td><?php echo $product->id; ?></td>
                            <td><?php echo htmlspecialchars($product->name); ?></td>
                            <td><?php echo htmlspecialchars($product->artisan_username ?? 'N/A'); ?></td>
                            <td>$<?php echo number_format($product->price ?? 0, 2); ?></td>
                            <td><?php echo $product->is_active ? 'Active' : 'Inactive'; ?></td>
                            <td><?php echo $product->is_featured ? '<span style="color: green; font-weight:bold;">Yes</span>' : 'No'; ?></td>
                            <td>
                                <a href="<?php echo URLROOT; ?>/admin/toggleProductFeatured/<?php echo $product->id; ?>/<?php echo $product->is_featured; ?>" class="btn btn-sm <?php echo $product->is_featured ? 'btn-secondary' : 'btn-primary'; ?>" style="font-size: 0.8em; padding: 2px 5px; color: white; background-color: <?php echo $product->is_featured ? '#6c757d' : '#007bff'; ?>;">
                                    <?php echo $product->is_featured ? 'Unfeature' : 'Feature'; ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">No products found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>

</div>