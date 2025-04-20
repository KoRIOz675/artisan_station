<div class="container" style="padding: 20px; text-align: center;">
    <a href="<?php echo URLROOT; ?>/admin/dashboard" class="btn btn-primary">← Back to Dashboard</a>
</div>

<div class="container admin-container" style="padding: 20px;">
    <h2>Manage Events</h2>
    <?php // flash('success'); flash('error'); 
    ?>

    <table class="dashboard-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Artisan</th>
                <th>Start Date</th>
                <th>Location</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (isset($data['events']) && !empty($data['events'])): ?>
                <?php foreach ($data['events'] as $event): ?>
                    <tr>
                        <td><?php echo $event->id; ?></td>
                        <td><?php echo htmlspecialchars($event->name); ?></td>
                        <td><?php echo htmlspecialchars($event->artisan_username ?? 'Admin/Site'); ?></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($event->start_datetime)); ?></td>
                        <td><?php echo htmlspecialchars($event->location ?? 'N/A'); ?></td>
                        <td><?php echo $event->is_active ? '<span style="color: green;">Active</span>' : '<span style="color: red;">Inactive</span>'; ?></td>
                        <td>
                            <a href="<?php echo URLROOT; ?>/admin/toggleEventActive/<?php echo $event->id; ?>/<?php echo $event->is_active; ?>" class="btn btn-sm <?php echo $event->is_active ? 'btn-warning' : 'btn-success'; ?>" style="font-size: 0.8em; padding: 2px 5px; color: white; background-color: <?php echo $event->is_active ? '#ffc107' : '#28a745'; ?>;">
                                <?php echo $event->is_active ? 'Deactivate' : 'Activate'; ?>
                            </a>
                            | <a href="<?php echo URLROOT; ?>/events/view/<?php echo $event->slug; ?>" target="_blank">View</a>
                            | <a href="<?php echo URLROOT; ?>/admin/editEvent/<?php echo $event->id; ?>">Edit</a> <?php // TODO: Create editEvent method/view 
                                                                                                                    ?>
                            | <a href="<?php echo URLROOT; ?>/admin/deleteEvent/<?php echo $event->id; ?>" onclick="return confirm('Delete event <?php echo htmlspecialchars($event->name); ?>? This is permanent!')" style="color: red;">Delete</a> <?php // TODO: Create deleteEvent method 
                                                                                                                                                                                                                                                        ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">No events found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>