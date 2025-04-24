<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($data['title'] ?? 'Artisan Marketplace'); ?></title>
    <link rel="icon" href="<?php echo URLROOT; ?>/img/logo.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="<?php echo URLROOT; ?>/css/header-footer.css">
    <link rel="stylesheet" href="<?php echo URLROOT; ?>/css/main.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Alata&display=swap">

    <?php if (isset($data['cssFile']) && !empty($data['cssFile'])): ?>
        <link rel="stylesheet" href="<?php echo URLROOT; ?>/css/pages/<?php echo htmlspecialchars($data['cssFile']); ?>">
    <?php endif; ?>
</head>

<body>
    <header id="navbar">
        <a href="<?php echo URLROOT; ?>">
            <img src="<?php echo URLROOT; ?>/img/logo.png" alt="Logo">
        </a>
        <nav>
            <a href="<?php echo URLROOT; ?>">Home</a>
            <span> | </span>
            <a href="<?php echo URLROOT; ?>/marketplace">Marketplace</a>
            <span> | </span>
            <a href="<?php echo URLROOT; ?>/contact">Contact</a>
            <span> | </span>

            <form action="<?php echo URLROOT; ?>/search" method="GET">
                <input type="searchbar" name="query" class="fontAwesome" placeholder="Search..." aria-label="Search">
            </form>
        </nav>
        <div id="user-actions">
            <?php if (isset($_SESSION['user_id'])):
            ?>
                <a href="<?php echo URLROOT; ?>/users/dashboard" class="user-icon" title="My Dashboard"><i class="fas fa-user"></i></a>
                <a href="<?php echo URLROOT; ?>/users/logout" class="user-icon" title="Logout"><i class="fas fa-sign-out-alt"></i></a>
            <?php else:
            ?>
                <a href="<?php echo URLROOT; ?>/users/loginRegister" class="user-icon" title="Login / Register"><i class="fas fa-user-plus"></i></a> <?php
                                                                                                                                                        ?>
            <?php endif; ?>
        </div>
    </header>
    <main>