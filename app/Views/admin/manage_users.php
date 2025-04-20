<div class="container" style="padding: 20px; text-align: center;">
    <a href="<?php echo URLROOT; ?>/admin/dashboard" class="btn btn-primary">← Back to Dashboard</a>
</div>

<div class="container admin-container" style="padding: 20px;">
    <h2>Manage Users</h2>
    <?php // flash('success'); flash('error'); 
    ?>

    <table class="dashboard-table" style="width: 100%; border-collapse: collapse; margin: 20px 0; border: 1px solid #dee2e6; background-color: #f0f0f0;">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Registered</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody style="text-align: center; border: 1px solid #dee2e6; background-color: #f8f9fa;">
            <?php if (isset($data['users']) && !empty($data['users'])): ?>
                <?php foreach ($data['users'] as $user): ?>
                    <tr>
                        <td><?php echo $user->id; ?></td>
                        <td><?php echo htmlspecialchars($user->username); ?></td>
                        <td><?php echo htmlspecialchars($user->email); ?></td>
                        <td>
                            <?php if ($user->id != $_SESSION['user_id']):
                            ?>
                                <select class="role-select form-control form-control-sm" data-userid="<?php echo $user->id; ?>" style="padding: 2px 5px; height: auto; display: inline-block; width: auto;">
                                    <option value="customer" <?php echo ($user->role == 'customer') ? 'selected' : ''; ?>>Customer</option>
                                    <option value="artisan" <?php echo ($user->role == 'artisan') ? 'selected' : ''; ?>>Artisan</option>
                                    <option value="admin" <?php echo ($user->role == 'admin') ? 'selected' : ''; ?>>Admin</option>
                                </select>
                            <?php else: ?>
                                <?php echo htmlspecialchars(ucfirst($user->role)); // Display current user's role as text 
                                ?>
                            <?php endif; ?>
                        </td>
                        <td style="width: 8em;"><?php echo $user->is_active ? '<span style="color: green;">Active</span>' : '<span style="color: red;">Inactive</span>'; ?></td>
                        <td><?php echo date('Y-m-d', strtotime($user->created_at)); ?></td>
                        <td>
                            <?php if ($user->id != $_SESSION['user_id']):
                            ?>
                                <a href="<?php echo URLROOT; ?>/admin/toggleUserActive/<?php echo $user->id; ?>/<?php echo $user->is_active; ?>" class="btn btn-sm <?php echo $user->is_active ? 'btn-primary' : 'btn-primary'; ?>" style="font-size: 0.8em; padding: 2px 5px; color: white; width: 7em; background-color: <?php echo $user->is_active ? '#ffc107' : '#28a745'; ?>;">
                                    <?php echo $user->is_active ? 'Deactivate' : 'Activate'; ?>
                                </a>
                                | <a href="<?php echo URLROOT; ?>/admin/deleteUser/<?php echo $user->id; ?>" onclick="return confirm('Delete user <?php echo htmlspecialchars($user->username); ?>? This is permanent!')" style="color: red;">Delete</a> <?php // TODO: Create deleteUser method 
                                                                                                                                                                                                                                                            ?>
                            <?php else: ?>
                                (Current User)
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">No users found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const roleSelects = document.querySelectorAll('.role-select');

        roleSelects.forEach(select => {
            select.addEventListener('change', function(e) {
                const selectElement = e.target;
                const userId = selectElement.dataset.userid;
                const newRole = selectElement.value;
                const currentUsername = selectElement.closest('tr').querySelector('td:nth-child(2)').textContent; // Get username for confirmation msg

                // Confirmation dialog
                const confirmation = window.confirm(`Are you sure you want to change the role for user "${currentUsername}" (ID: ${userId}) to "${newRole}"?`);

                if (confirmation) {
                    // Construct the URL and navigate
                    const targetUrl = `<?php echo URLROOT; ?>/admin/changeUserRole/${userId}/${newRole}`;
                    window.location.href = targetUrl;
                } else {
                    // If cancelled, reset the dropdown to its original value (requires storing original value, more complex)
                    // For simplicity, we won't reset it here, but the change won't be submitted.
                    // To reset: you'd need to store the original role, maybe in another data attribute,
                    // find the option with that value, and set its `selected` property to true.
                    console.log('Role change cancelled.');
                }
            });
        });
    });
</script>