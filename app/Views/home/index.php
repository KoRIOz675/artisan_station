<?php
?>
<div class="background-color">
    <section class="content-main-image">
        <img src="<?php echo URLROOT; ?>/img/artisan-doing-woodcutting.jpg" alt="Artisan working">
        <div class="bottom">
            <div class="text-main">
                <h1>Artisan Station</h1>
                <p>For Artists, By Artists</p>
            </div>
        </div>
    </section>

    <?php if (isset($featuredArtisan) && !empty($featuredArtisan) && is_object($featuredArtisan)): ?>
        <section class="content-section">
            <hr class="featured-line">
            <h2 class="section-title">Featured Artisans</h2>
            <div class="week-featured-container">
                <div class="week-featured-item">
                    <img src="<?php echo URLROOT; ?>/img/artists/<?php echo htmlspecialchars(!empty($featuredArtisan->profile_picture_path) ? $featuredArtisan->profile_picture_path : 'default_artist.jpg'); ?>"
                        alt="<?php echo htmlspecialchars($featuredArtisan->shop_name ?? $featuredArtisan->username ?? 'Artisan'); ?>" class="week-featured-img" />
                    <div class="week-featured-text">
                        <h3><?php echo htmlspecialchars($featuredArtisan->first_name ?? 'Artisan Name'); ?> <?php echo htmlspecialchars($featuredArtisan->last_name ?? ''); ?></h3>
                        <p><?php echo htmlspecialchars(substr($featuredArtisan->bio ?? '', 0, 100)); ?></p>
                        <a href="<?php echo URLROOT . '/artisans/' . htmlspecialchars($featuredArtisan->username ?? ''); ?>" class="in-text-link">View Full Profile</a>
                    </div>
                </div>
                <div class="week-featured-art-gallery">
                    <h4 class="gallery-title">Art :</h4>
                    <?php if (isset($featuredArtisanProducts) && !empty($featuredArtisanProducts)): ?>
                        <div class="artist-art-grid">
                            <?php foreach ($featuredArtisanProducts as $product): ?>
                                <?php if (is_object($product)): ?>
                                    <a href="<?php echo URLROOT; ?>/artisans/<?php echo htmlspecialchars($featuredArtisan->username ?? 'na'); ?>/products/<?php echo htmlspecialchars($product->slug ?? $product->id); ?>" class="artist-art-item">
                                        <img src="<?php echo URLROOT; ?>/img/products/<?php echo htmlspecialchars(!empty($product->image_path) ? $product->image_path : 'default_product.jpg'); ?>"
                                            alt="<?php echo htmlspecialchars($product->name ?? 'Artwork'); ?>">
                                    </a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <a href="<?php echo URLROOT . '/artisans/' . htmlspecialchars($featuredArtisan->username ?? ''); ?>" class="see-more-art">
                                <span>▶</span>
                            </a>
                        </div>
                    <?php else: ?>
                        <p style="margin-left: 10px;">This artisan hasn't listed any art yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <?php else: ?>
        <section class="content-section">
            <hr class="featured-line">
            <h2 class="section-title">Featured Artisans</h2>
            <div class="featured-artist-week-container">
                <p>No featured artisans available at this time.</p>
            </div>
        </section>
    <?php endif; ?>

    <?php if (isset($artOfTheWeek) && is_object($artOfTheWeek)): ?>
        <section class="content-section">
            <hr class="featured-line">
            <h2 class="section-title">Featured Art of the week</h2>
            <div class="week-featured-container">
                <div class="week-featured-item">
                    <a href="<?php echo URLROOT; ?>/artisans/<?php echo htmlspecialchars($featuredArtisan->username ?? 'na'); ?>/products/<?php echo htmlspecialchars($product->slug ?? $product->id); ?>" class="featured-art-image-link">
                        <img src="<?php echo PRODUCT_IMG_URL_PREFIX . htmlspecialchars(!empty($artOfTheWeek->image_path) ? $artOfTheWeek->image_path : 'default_product.jpg'); ?>"
                            alt="<?php echo htmlspecialchars($artOfTheWeek->name ?? 'Featured Artwork'); ?>" class="week-featured-img">
                    </a>
                    <div class="week-featured-text">
                        <h3><?php echo htmlspecialchars($artOfTheWeek->name ?? 'Artwork Name'); ?></h3>
                        <p>Type: <?php echo htmlspecialchars($artOfTheWeek->category_name ?? 'General Art'); ?></p>
                        <p>By: <?php echo htmlspecialchars($artOfTheWeek->artisan_firstname ?? 'Unknown Artisan'); ?> <?php echo htmlspecialchars($artOfTheWeek->artisan_lastname ?? 'Unknown Artisan'); ?></p>
                        <a href="<?php echo URLROOT; ?>/artisans/<?php echo htmlspecialchars($featuredArtisan->username ?? 'na'); ?>/products/<?php echo htmlspecialchars($product->slug ?? $product->id); ?>" class="in-text-link">View Details</a>
                    </div>
                </div>
        </section>
    <?php endif; ?>

    <section class="content-section">
        <hr class="featured-line">
        <h2 class="section-title">Explore Categories</h2>
        <div class="categories-container">
            <?php if (isset($categories) && !empty($categories)): ?>
                <?php foreach ($categories as $category): ?>
                    <?php if (is_object($category)): ?>
                        <?php
                        // Determine background image path (use default if empty)
                        $bgImage = !empty($category->image_path)
                            ? CATEGORY_IMG_URL_PREFIX . htmlspecialchars($category->image_path) // Use constant if defined
                            : URLROOT . '/img/categories/default_category.png'; // Provide a default
                        $categoryLink = URLROOT . '/marketplace/category/' . htmlspecialchars($category->slug ?? $category->id);
                        ?>
                        <a href="<?php echo $categoryLink; ?>" class="category-item"
                            style="background-image: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('<?php echo $bgImage; ?>');">
                            <div class="category-item-text"><?php echo htmlspecialchars($category->name ?? 'Category'); ?></div>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No categories available at the moment.</p>
            <?php endif; ?>
        </div>
    </section>

    <section class="content-section">
        <hr class="featured-line">
        <h2 class="section-title">Upcoming Events</h2>
        <div class="events-container">
            <?php if (isset($data['events']) && !empty($data['events'])): ?>
                <?php foreach ($data['events'] as $event): ?>
                    <?php if (is_object($event)): ?>
                        <div class="event-card">
                            <a href="<?php echo URLROOT . '/events/show/' . htmlspecialchars($event->slug ?? $event->id); ?>" class="category-item" style="background-image: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('<?php echo EVENT_IMG_URL_PREFIX . htmlspecialchars(!empty($event->image_path) ? $event->image_path : 'default_event.jpg'); ?>');">
                                <div class="category-item-text"><?php echo htmlspecialchars($event->name ?? 'Event Name'); ?></div>
                            </a>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No upcoming events scheduled at this time.</p>
            <?php endif; ?>
        </div>
    </section>
</div>