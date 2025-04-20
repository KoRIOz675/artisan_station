<div class="container" style="padding: 20px; text-align: center;">
    <a href="<?php echo URLROOT; ?>/admin/dashboard" class="btn btn-primary">← Back to Dashboard</a>
</div>

<div class="container admin-container" style="padding: 20px;">
    <h2>Site Settings</h2>
    <p>These settings are currently defined in configuration files. A future version might allow editing some via a database.</p>

    <table class="dashboard-table" style="max-width: 600px;">
        <thead>
            <tr>
                <th>Setting Name (Constant)</th>
                <th>Value</th>
            </tr>
        </thead>
        <tbody>
            <?php if (isset($data['settings']) && is_array($data['settings'])): ?>
                <?php foreach ($data['settings'] as $key => $value): ?>
                    <?php // Display only relevant/safe constants 
                    ?>
                    <?php if (in_array($key, ['URLROOT', 'APPROOT', 'SITENAME', 'DB_HOST', 'DB_NAME', 'DB_CHARSET', 'EVENT_IMG_UPLOAD_DIR', 'EVENT_IMG_URL_PREFIX'])): // Add others if needed 
                    ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($key); ?></strong></td>
                            <td><?php echo htmlspecialchars(print_r($value, true)); // Use print_r for potential arrays/objects 
                                ?></td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="2">Could not load settings.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <p><i>Database credentials (User, Password) are intentionally hidden.</i></p>

</div>