<?php
?>
<div class="background-color">
    <section class="content-main-image">
        <img src="<?php echo URLROOT; ?>/img/artisan-doing-woodcutting.jpg" alt="Artisan working">
        <div class="bottom">
            <div class="text-main">
                <h1><?php echo isset($title) ? htmlspecialchars($title) : 'Welcome'; ?></h1>
                <p><?php echo isset($description) ? htmlspecialchars($description) : 'Discover unique creations.'; ?></p>
            </div>
        </div>
    </section>

    <?php if (isset($featuredArtisan) && !empty($featuredArtisan) && is_object($featuredArtisan)): ?>
        <section class="content-section">
            <hr class="featured-line">
            <h2 class="section-title">Featured Artisans</h2>
            <div class="week-featured-container">
                <div class="week-featured-item">
                    <img src="<?php echo URLROOT; ?>/img/artists/<?php echo htmlspecialchars(!empty($artisan->profile_picture_path) ? $artisan->profile_picture_path : 'default_artist.jpg'); ?>"
                        alt="<?php echo htmlspecialchars($artisan->shop_name ?? $artisan->username ?? 'Artisan'); ?>" class="week-featured-img" />
                    <div class="week-featured-text">
                        <h3><?php echo htmlspecialchars($artisan->shop_name ?? $artisan->username ?? 'Artisan Name'); ?></h3>
                        <p><?php echo htmlspecialchars(substr($artisan->bio ?? '', 0, 100)); ?></p>
                        <a href="<?php echo URLROOT . '/artisans/show/' . ($artisan->id ?? ''); ?>">View Profile</a>
                    </div>
                </div>
                <div class="week-featured-art-gallery">
                    <h4 class="gallery-title">Art :</h4>
                    <?php if (isset($featuredArtisanProducts) && !empty($featuredArtisanProducts)): ?>
                        <div class="artist-art-grid">
                            <?php foreach ($featuredArtisanProducts as $product): ?>
                                <?php if (is_object($product)): ?>
                                    <a href="<?php echo URLROOT . '/products/show/' . ($product->slug ?? $product->id); ?>" class="artist-art-item">
                                        <img src="<?php echo URLROOT; ?>/img/products/<?php echo htmlspecialchars(!empty($product->image_path) ? $product->image_path : 'default_product.jpg'); ?>"
                                            alt="<?php echo htmlspecialchars($product->name ?? 'Artwork'); ?>">
                                    </a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <a href="<?php echo URLROOT . '/artisans/show/' . ($featuredArtisan->id ?? ''); ?>#artworks" class="artist-art-item see-more-art">
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

    <section class="content-section">
        <hr class="featured-line">
        <h2 class="section-title">Featured Creations</h2>
        <div class="categories-container">
            <?php // Check if $products exists and is not empty before looping 
            ?>
            <?php if (isset($products) && !empty($products)): ?>
                <?php foreach ($products as $product): ?>
                    <div class="category-item" style="background-image: url('<?php echo URLROOT; ?>/img/products/<?php echo htmlspecialchars($product['image_path'] ?? 'default_product.jpg'); ?>');">
                        <div class="category-item-text">
                            <?php // Ensure product ID and name exist 
                            ?>
                            <a href="<?php echo URLROOT . '/products/show/' . ($product['id'] ?? ''); ?>"><?php echo htmlspecialchars($product['name'] ?? 'Product'); ?></a>
                            <p>by <?php echo htmlspecialchars($product['artisan_name'] ?? 'Unknown Artisan'); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <?php // Placeholder if no product data is passed 
                ?>
                <p>Featured products section - requires data from controller/model.</p>
            <?php endif; ?>
        </div>
    </section>

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
                    <?php // Check if $event is an object 
                    ?>
                    <?php if (is_object($event)): ?>
                        <div class="event-card"> <?php // Style this card 
                                                    ?>
                            <?php // Construct link using event slug (more SEO friendly) 
                            ?>
                            <a href="<?php echo URLROOT . '/events/view/' . htmlspecialchars($event->slug ?? $event->id); ?>">
                                <?php // Construct image path - provide a default image if needed 
                                ?>
                                <a href="<?php echo URLROOT . '/events/view/' . htmlspecialchars($event->slug ?? $event->id); ?>" class="category-item" style="background-image: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('<?php echo EVENT_IMG_URL_PREFIX . htmlspecialchars(!empty($event->image_path) ? $event->image_path : 'default_event.jpg'); ?>');">
                                    <div class="category-item-text"><?php echo htmlspecialchars($event->name ?? 'Event Name'); ?></div>
                                </a>
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