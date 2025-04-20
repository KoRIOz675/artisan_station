<div class="login-register-container">

    <!-- Login Form (Left - Swapped order for visual match) -->
    <div class="form-container login-form">
        <h2>LOGIN</h2> <?php // Changed Heading ?>

        <?php /* Flash messages or general errors can be displayed here if needed */ ?>
        <?php if (!empty($data['login_general_err'])): ?>
            <div class="form-group general-error">
                <?php $isSuccess = (isset($_GET['registered']) && $_GET['registered'] === 'success'); ?>
                <span class="error-message" style="text-align:center; font-weight:bold; display: block; margin-bottom: 10px; color: <?php echo $isSuccess ? 'green' : '#D8000C'; ?>;">
                    <?php echo htmlspecialchars($data['login_general_err']); ?>
                </span>
            </div>
        <?php endif; ?>
        <?php flash('login_error'); // Display flash messages if using session_helper ?>


        <form action="<?php echo URLROOT; ?>/users/loginRegister" method="post" novalidate>
            <!-- Email Input -->
            <div class="form-group">
                <label for="login_identifier">Email</label> <?php // Removed * ?>
                <input type="email" name="login_identifier" id="login_identifier" class="<?php echo (!empty($data['login_identifier_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($data['login_identifier']); ?>" required> <?php // Changed type to email ?>
                <span class="error-message"><?php echo $data['login_identifier_err']; ?></span>
            </div>
            <!-- Password -->
            <div class="form-group">
                <label for="login_password">Password</label> <?php // Removed * ?>
                <input type="password" name="login_password" id="login_password" class="<?php echo (!empty($data['login_password_err']) || !empty($data['login_general_err']) && !$isSuccess) ? 'is-invalid' : ''; ?>" value="" required>
                <span class="error-message"><?php echo $data['login_password_err']; ?></span>
            </div>

            <!-- Submit Button -->
            <div class="form-group form-button-group"> <?php // Added wrapper for centering ?>
                <button type="submit" name="login_submit" class="btn">Login</button> <?php // Changed Text ?>
            </div>
        </form>
    </div>

    <!-- Separator (Handled by CSS) -->

    <!-- Registration Form (Right - Swapped order for visual match) -->
    <div class="form-container register-form">
        <h2>SIGN UP</h2> <?php // Changed Heading ?>

        <?php /* Flash messages or general errors can be displayed here if needed */ ?>
         <?php if (!empty($data['register_general_err'])): ?>
            <div class="form-group general-error">
                 <span class="error-message" style="text-align:center; font-weight:bold; display: block; margin-bottom: 10px; color: #D8000C;">
                    <?php echo htmlspecialchars($data['register_general_err']); ?>
                 </span>
            </div>
        <?php endif; ?>
         <?php flash('register_error'); // Display flash messages if using session_helper ?>


        <form action="<?php echo URLROOT; ?>/users/loginRegister" method="post" novalidate>
            <!-- Name (was Username) -->
            <div class="form-group">
                <label for="register_username">Username</label> <?php // Changed Label, removed * ?>
                <input type="text" name="register_username" id="register_username" class="<?php echo (!empty($data['register_username_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($data['register_username']); ?>" required>
                <span class="error-message"><?php echo $data['register_username_err']; ?></span>
            </div>
            <!-- Email -->
            <div class="form-group">
                <label for="register_email">Email</label> <?php // Removed * ?>
                <input type="email" name="register_email" id="register_email" class="<?php echo (!empty($data['register_email_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($data['register_email']); ?>" required>
                <span class="error-message"><?php echo $data['register_email_err']; ?></span>
            </div>
            <!-- Password -->
            <div class="form-group">
                <label for="register_password">Password</label> <?php // Removed * ?>
                <input type="password" name="register_password" id="register_password" class="<?php echo (!empty($data['register_password_err'])) ? 'is-invalid' : ''; ?>" value="" required> <?php // Clear password field value ?>
                <span class="error-message"><?php echo $data['register_password_err']; ?></span>
            </div>
            <!-- Confirm Password -->
            <div class="form-group">
                <label for="register_confirm_password">Confirm password</label> <?php // Changed Label, removed * ?>
                <input type="password" name="register_confirm_password" id="register_confirm_password" class="<?php echo (!empty($data['register_confirm_password_err'])) ? 'is-invalid' : ''; ?>" value="" required> <?php // Clear password field value ?>
                <span class="error-message"><?php echo $data['register_confirm_password_err']; ?></span>
            </div>

            <!-- Submit Button -->
             <div class="form-group form-button-group"> <?php // Added wrapper for centering ?>
                <button type="submit" name="register_submit" class="btn">Sign Up</button> <?php // Changed Text ?>
            </div>
        </form>
    </div>

</div>