<?php
$event = $data['event'] ?? null; // Extract event object
?>

<div class="container event-show-container" style="padding: 20px;">

    <?php if ($event && is_object($event)): ?>

        <div class="event-show-header" style="margin-bottom: 30px;">
            <h1><?php echo htmlspecialchars($event->name); ?></h1>
            <hr>
        </div>

        <div class="event-show-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">

            <!-- Left Column: Image -->
            <div class="event-show-image">
                <img src="<?php echo EVENT_IMG_URL_PREFIX . htmlspecialchars(!empty($event->image_path) ? $event->image_path : 'default_event.jpg'); ?>"
                    alt="<?php echo htmlspecialchars($event->name); ?>"
                    style="max-width: 100%; height: auto; border-radius: 5px; border: 1px solid #eee;">
            </div>

            <!-- Right Column: Details -->
            <div class="event-show-details">
                <h3>Event Details</h3>
                <ul style="list-style: none; padding: 0;">
                    <li style="margin-bottom: 10px;">
                        <strong><i class="fas fa-calendar-alt"></i> Start:</strong>
                        <?php echo htmlspecialchars(date('l, F j, Y \a\t g:i A', strtotime($event->start_datetime ?? ''))); ?>
                    </li>
                    <?php if (!empty($event->end_datetime)): ?>
                        <li style="margin-bottom: 10px;">
                            <strong><i class="fas fa-calendar-alt"></i> End:</strong>
                            <?php echo htmlspecialchars(date('l, F j, Y \a\t g:i A', strtotime($event->end_datetime))); ?>
                        </li>
                    <?php endif; ?>
                    <?php if (!empty($event->location)): ?>
                        <li style="margin-bottom: 10px;">
                            <strong><i class="fas fa-map-marker-alt"></i> Location:</strong>
                            <?php echo htmlspecialchars($event->location); ?>
                            <?php // Optional: Add link to Google Maps 
                            ?>
                            <?php /* <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($event->location); ?>" target="_blank">(View Map)</a> */ ?>
                        </li>
                    <?php endif; ?>
                    <?php if (!empty($event->artisan_username)): // Check if linked to an artisan 
                    ?>
                        <li style="margin-bottom: 10px;">
                            <strong><i class="fas fa-user"></i> Hosted by:</strong>
                            <a href="<?php echo URLROOT . '/artisans/' . htmlspecialchars($event->artisan_username); ?>">
                                <?php echo htmlspecialchars($event->artisan_shop_name ?? $event->artisan_username); ?>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>

                <hr style="margin: 20px 0;">

                <h3>Description</h3>
                <div class="event-description" style="line-height: 1.7;">
                    <?php echo nl2br(htmlspecialchars($event->description ?? 'No description provided.')); ?>
                </div>
                <div class="event-attendance-action" style="margin-top: 25px; padding-top: 20px; border-top: 1px solid #eee;">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if (!empty($data['is_attending'])): ?>
                            <form action="<?php echo URLROOT; ?>/events/unattend/<?php echo $event->id; ?>" method="POST" style="display: inline;">
                                <button type="submit" class="btn btn-warning">Cancel Attendance</button>
                            </form>
                        <?php else: ?>
                            <form action="<?php echo URLROOT; ?>/events/attend/<?php echo $event->id; ?>" method="POST" style="display: inline;">
                                <button type="submit" class="btn btn-success">Attend Event</button>
                            </form>
                        <?php endif; ?>
                    <?php else: ?>
                        <p>Log in to mark your attendance.</p>
                    <?php endif; ?>
                </div>
            </div>

        </div>

    <?php else: ?>
        <h2>Event Not Found</h2>
        <p>The event you are looking for could not be found or is no longer available.</p>
        <a href="<?php echo URLROOT; ?>/events" class="btn btn-primary">Back to Events List</a>
    <?php endif; ?>

</div>