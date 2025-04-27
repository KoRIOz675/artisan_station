<div class="dashboard-container">

    <!-- Sidebar Navigation Tabs -->
    <aside class="dashboard-nav">
        <h3>Dashboard</h3>
        <ul>
            <li><a href="#profile-content" class="dashboard-tab active">Profile</a></li>
            <li><a href="#order-history-content" class="dashboard-tab">Order History</a></li>
            <li><a href="#events-content" class="dashboard-tab">Attended Events</a></li>
            <?php if (isset($data['role']) && $data['role'] === 'artisan'): ?>
                <li><a href="#art-content" class="dashboard-tab">My Art</a></li>
                <li><a href="#event-created" class="dashboard-tab">My Events</a></li>
            <?php endif; ?>
            <?php if (isset($data['role']) && $data['role'] === 'admin'): ?>
                <li><a href="<?php echo URLROOT; ?>/admin/dashboard">Admin Section</a></li>
            <?php endif; ?>
            <li><a href="<?php echo URLROOT; ?>/users/logout" id="logout-link">Logout</a></li>
        </ul>
    </aside>

    <!-- Main Content Area -->
    <main class="dashboard-content">

        <!-- Profile Tab Content -->
        <div id="profile-content" class="tab-pane active">
            <h2>Profile Information</h2>
            <ul class="profile-info">
                <li><strong>Username:</strong> <?php echo htmlspecialchars($data['username'] ?? 'N/A'); ?></li>
                <li><strong>First Name:</strong> <?php echo htmlspecialchars(ucfirst($data['first_name'] ?? 'N/A')) ?></li>
                <li><strong>Last Name:</strong> <?php echo htmlspecialchars(ucfirst($data['last_name'] ?? 'N/A')) ?></li>
                <li><strong>Email:</strong> <?php echo htmlspecialchars($data['email'] ?? 'N/A'); ?></li>
                <li><strong>Password:</strong> ********</li>
                <li><strong>Role:</strong> <?php echo htmlspecialchars(ucfirst($data['role'] ?? 'N/A')); ?></li>
                <?php if (isset($data['role']) && $data['role'] === 'artisan'): ?>
                    <li><strong>Shop Name:</strong> <?php echo htmlspecialchars($data['shop_name'] ?? 'N/A'); ?></li>
                    <li><strong>Bio:</strong> <?php echo htmlspecialchars($data['bio'] ?? 'N/A'); ?></li>
                <?php endif; ?>
            </ul>
            <a href="<?php echo URLROOT; ?>/users/editProfile" class="btn btn-primary">Edit Profile</a>
            <a href="<?php echo URLROOT; ?>/users/changePassword" class="btn btn-primary">Change Password</a>

            <hr style="margin: 30px 0;"> <?php // Add a separator 
                                            ?>

            <!-- Delete Account Section -->
            <div class="delete-account-section">
                <h3>Delete Account</h3>
                <p style="color: #dc3545; margin-bottom: 15px;">
                    <strong>Warning:</strong> Deleting your account is permanent and cannot be undone.
                    All your associated data (profile, created events, products, etc., depending on database setup) may be lost.
                </p>
                <?php // Use a form to submit POST request for deletion 
                ?>
                <form action="<?php echo URLROOT; ?>/users/deleteAccount" method="post" style="display: inline;">
                    <button type="submit" class="btn btn-primary"
                        onclick="return confirm('ARE YOU ABSOLUTELY SURE?\n\nDeleting your account is permanent and cannot be undone.');">
                        Delete My Account Permanently
                    </button>
                </form>
            </div>
        </div>

        <!-- Order History Tab Content -->
        <div id="order-history-content" class="tab-pane">
            <h2>Order History</h2>
            <?php if (!empty($data['orders'])): ?>
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['orders'] as $order): ?>
                            <tr>
                                <td>#<?php echo htmlspecialchars($order->id); ?></td>
                                <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($order->order_datetime))); ?></td>
                                <td>$<?php echo htmlspecialchars(number_format($order->total_amount, 2)); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst($order->status)); ?></td>
                                <td><a href="<?php echo URLROOT; ?>/orders/details/<?php echo $order->id; ?>">View Details</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>You haven't placed any orders yet.</p>
                <?php // Placeholder Table for structure demonstration 
                ?>
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Attended Events Tab Content -->
        <div id="events-content" class="tab-pane">
            <h2>Attended Events</h2>
            <?php if (!empty($data['attendedEventsList'])): ?>
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>Event Name</th>
                            <th>Start Date</th>
                            <th>Location</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['attendedEventsList'] as $event): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($event->name); ?></td>
                                <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($event->start_datetime))); ?></td>
                                <td><?php echo htmlspecialchars($event->location ?? 'N/A'); ?></td>
                                <td>
                                    <a href="<?php echo URLROOT; ?>/events/show/<?php echo $event->slug; ?>" target="_blank">View Event</a>
                                    |
                                    <form action="<?php echo URLROOT; ?>/events/unattend/<?php echo $event->id; ?>" method="POST" style="display:inline;" onsubmit="return confirm('Cancel attendance for this event?');">
                                        <button type="submit" class="btn-link-style" style="color: #dc3545;">Cancel Attendance</button> <?php // Link-styled button 
                                                                                                                                        ?>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>You haven't attended any events yet.</p>
                <p><a href="<?php echo URLROOT; ?>/events" class="in-text-link">Browse upcoming events</a>.</p>
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>Event Name</th>
                            <th>Date</th>
                            <th>Location</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            <?php endif; ?>
        </div>

        <?php // --- Artisan Only Tab Content --- 
        ?>
        <!-- My Art -->
        <?php if (isset($data['role']) && $data['role'] === 'artisan'  || $data['role'] === 'admin'): ?>
            <div id="art-content" class="tab-pane">
                <h2>My Art Management</h2>

                <a href="<?php echo URLROOT; ?>/products/create" class="btn btn-primary">Upload New Art</a>

                <h3>My Listed Arts</h3>
                <?php if (!empty($data['arts'])): ?>
                    <div class="arts-grid">
                        <?php foreach ($data['arts'] as $art): if (is_object($art)): ?>
                                <div class="art-card">
                                    <img src="<?php echo PRODUCT_IMG_URL_PREFIX . ($art->image_path ?? 'default.jpg'); ?>" alt="<?php echo htmlspecialchars($art->name); ?>">
                                    <h4><?php echo htmlspecialchars($art->name); ?></h4>
                                    <p>$<?php echo htmlspecialchars(number_format($art->price, 2)); ?></p>
                                    <a style="border: 0; background-color: inherit; font-family: Alata, sans-serif; font-size: medium;" href="<?php echo URLROOT; ?>/products/edit/<?php echo $art->id; ?>">Edit</a> |
                                    <a style="border: 0; background-color: inherit; font-family: Alata, sans-serif; font-size: medium;" href="<?php echo URLROOT; ?>/artisans/<?php echo htmlspecialchars($_SESSION['user_username'] ?? 'na'); ?>/products/<?php echo htmlspecialchars($art->slug ?? $art->id); ?>" target="_blank">View</a> |
                                    <form action="<?php echo URLROOT; ?>/products/destroy/<?php echo $art->id; ?>" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                        <button type="submit" style="border: 0; background-color: inherit; font-family: Alata, sans-serif; font-size: medium;">Delete</button>
                                    </form>
                                </div>
                        <?php endif;
                        endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>You haven't uploaded any art pieces yet.</p>
                <?php endif; ?>
            </div>

            <!-- My Events -->
            <div id="event-created" class="tab-pane">
                <h2>My Created Events</h2>

                <a href="<?php echo URLROOT; ?>/events/create" class="btn mb-20" style="background-color: #735339; color: white">Create New Event</a>

                <?php if (!empty($data['my_events'])): ?>
                    <table class="dashboard-table">
                        <thead>
                            <tr>
                                <th>Event Name</th>
                                <th>Start Date</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['my_events'] as $event): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($event->name); ?></td>
                                    <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($event->start_datetime))); ?></td>
                                    <td><?php echo htmlspecialchars($event->location ?? 'N/A'); ?></td>
                                    <td><?php echo ($event->is_active ?? 1) ? 'Active' : 'Inactive'; ?></td>
                                    <td>
                                        <?php // Placeholder links - implement these routes/methods later 
                                        ?>
                                        <a href="<?php echo URLROOT; ?>/events/view/<?php echo $event->slug; ?>" target="_blank">View</a> |
                                        <a href="<?php echo URLROOT; ?>/events/edit/<?php echo $event->id; ?>">Edit</a> |
                                        <a href="<?php echo URLROOT; ?>/events/delete/<?php echo $event->id; ?>" onclick="return confirm('Are you sure you want to delete this event?');" style="color: red;">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>You haven't created any events yet.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <?php // --- End Artisan Only Tab Content --- 
        ?>

    </main>

</div>

<!-- Simple JavaScript for Tab Switching -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tabs = document.querySelectorAll('.dashboard-tab');
        const panes = document.querySelectorAll('.tab-pane');
        const logoutLink = document.getElementById('logout-link');

        tabs.forEach(tab => {
            tab.addEventListener('click', function(e) {
                // Don't jump to hash link if it's a real tab link
                if (this.getAttribute('href').startsWith('#')) {
                    e.preventDefault();

                    const targetId = this.getAttribute('href'); // e.g., "#profile-content"
                    const targetPane = document.querySelector(targetId);

                    // Remove active class from all tabs and panes
                    tabs.forEach(t => t.classList.remove('active'));
                    panes.forEach(p => p.classList.remove('active'));

                    // Add active class to the clicked tab and target pane
                    this.classList.add('active');
                    if (targetPane) {
                        targetPane.classList.add('active');
                    }
                }
                // Allow normal navigation for links like Logout or Admin Section
            });
        });

        if (logoutLink) {
            // Add a click event listener
            logoutLink.addEventListener('click', function(event) {
                // Prevent the link from navigating immediately
                event.preventDefault();

                // Show the confirmation dialog
                const userConfirmed = window.confirm('Are you sure you want to logout?');

                // If the user clicked "OK" (true), then navigate to the logout URL
                if (userConfirmed) {
                    window.location.href = this.href; // 'this.href' refers to the link's original URL
                }
                // If the user clicked "Cancel" (false), do nothing (navigation was already prevented)
            });
        }
    });
</script>