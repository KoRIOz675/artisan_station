<div class="container events-list-container" style="padding: 20px;">

    <h1><?php echo htmlspecialchars($data['title']); ?></h1>
    <p>Discover workshops, exhibitions, fairs, and more!</p>
    <hr>

    <div class="events-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 25px;">

        <?php if (isset($data['events']) && !empty($data['events'])): ?>
            <?php foreach ($data['events'] as $event): ?>
                <?php if (is_object($event)): ?>
                    <div class="event-list-card" style="border: 1px solid #eee; border-radius: 5px; overflow: hidden; background-color: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.05); display: flex; flex-direction: column;">
                        <a href="<?php echo URLROOT; ?>/events/show/<?php echo htmlspecialchars($event->slug ?? $event->id); ?>" style="text-decoration: none; color: inherit; display: block;">
                            <img src="<?php echo EVENT_IMG_URL_PREFIX . htmlspecialchars(!empty($event->image_path) ? $event->image_path : 'default_event.jpg'); ?>"
                                alt="<?php echo htmlspecialchars($event->name ?? 'Event'); ?>"
                                style="width: 100%; height: 180px; object-fit: cover; display: block;">
                        </a>
                        <div class="event-card-body" style="padding: 15px; flex-grow: 1; display: flex; flex-direction: column;">
                            <h3 style="margin: 0 0 10px 0; font-size: 1.2em;">
                                <a href="<?php echo URLROOT; ?>/events/show/<?php echo htmlspecialchars($event->slug ?? $event->id); ?>" style="text-decoration: none; color: #333;">
                                    <?php echo htmlspecialchars($event->name ?? 'Event Name'); ?>
                                </a>
                            </h3>
                            <p style="font-size: 0.9em; color: #555; margin-bottom: 8px;">
                                <i class="fas fa-calendar-alt"></i> <?php echo htmlspecialchars(date('M j, Y \a\t g:i A', strtotime($event->start_datetime ?? ''))); ?>
                            </p>
                            <?php if (!empty($event->location)): ?>
                                <p style="font-size: 0.9em; color: #555; margin-bottom: 15px;">
                                    <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event->location); ?>
                                </p>
                            <?php endif; ?>
                            <?php if (!empty($event->artisan_shop_name) || !empty($event->artisan_username)): ?>
                                <p style="font-size: 0.85em; color: #777; margin-bottom: 15px;">
                                    Hosted by: <a href="<?php echo URLROOT . '/artisans/' . htmlspecialchars($event->artisan_username ?? ''); ?>"><?php echo htmlspecialchars($event->artisan_shop_name ?? $event->artisan_username); ?></a>
                                </p>
                            <?php endif; ?>
                            <p style="flex-grow: 1; font-size: 0.95em; color: #444; line-height: 1.5; margin-bottom: 15px;">
                                <?php echo htmlspecialchars(substr($event->description ?? '', 0, 120)); ?>...
                            </p>
                            <a href="<?php echo URLROOT; ?>/events/show/<?php echo htmlspecialchars($event->slug ?? $event->id); ?>" class="btn btn-sm btn-outline-primary" style="margin-top: auto; align-self: flex-start;">View Details</a>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="grid-column: 1 / -1;">There are no upcoming events scheduled at this time.</p>
        <?php endif; ?>

    </div>

</div>