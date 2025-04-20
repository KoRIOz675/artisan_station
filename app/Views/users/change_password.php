<div class="container change-password-container" style="max-width: 600px; margin: 10em auto; padding: 20px; background-color:#f8f9fa; border: 1px solid #dee2e6; border-radius: 5px;">

    <h2 style="margin:1em auto; color:#735339;">Change Your Password</h2>
    <hr style="margin: 1em auto; border-color: #735339;">

    <?php // Display general errors or success messages ?>
     <?php if (!empty($data['general_err'])): ?>
        <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; padding: 10px; border-radius: 4px; margin-bottom: 15px;">
            <?php echo htmlspecialchars($data['general_err']); ?>
        </div>
     <?php endif; ?>
     <?php if (!empty($data['success_message'])): ?>
         <div class="alert alert-success" style="background-color: #d4edda; color: #155724; border-color: #c3e6cb; padding: 10px; border-radius: 4px; margin-bottom: 15px;">
             <?php echo htmlspecialchars($data['success_message']); ?>
         </div>
     <?php endif; ?>


    <form action="<?php echo URLROOT; ?>/users/changePassword" method="post" novalidate>

        <!-- Current Password -->
        <div class="form-group" style="margin-bottom: 15px;">
            <label for="current_password">Current Password: <sup>*</sup></label>
            <input type="password" name="current_password" id="current_password" class="form-control <?php echo (!empty($data['current_password_err'])) ? 'is-invalid' : ''; ?>" value="" required>
            <span class="invalid-feedback" style="color: #D8000C; font-size: 0.85em; display: block;"><?php echo $data['current_password_err']; ?></span>
        </div>

        <!-- New Password -->
        <div class="form-group" style="margin-bottom: 15px;">
            <label for="new_password">New Password: <sup>*</sup> (Min 6 chars)</label>
            <input type="password" name="new_password" id="new_password" class="form-control <?php echo (!empty($data['new_password_err'])) ? 'is-invalid' : ''; ?>" value="" required>
            <span class="invalid-feedback" style="color: #D8000C; font-size: 0.85em; display: block;"><?php echo $data['new_password_err']; ?></span>
        </div>

        <!-- Confirm New Password -->
        <div class="form-group" style="margin-bottom: 20px;">
            <label for="confirm_new_password">Confirm New Password: <sup>*</sup></label>
            <input type="password" name="confirm_new_password" id="confirm_new_password" class="form-control <?php echo (!empty($data['confirm_new_password_err'])) ? 'is-invalid' : ''; ?>" value="" required>
            <span class="invalid-feedback" style="color: #D8000C; font-size: 0.85em; display: block;"><?php echo $data['confirm_new_password_err']; ?></span>
        </div>

        <hr style="margin: 1em auto; border-color: #735339;">

        <!-- Submit Button -->
        <div class="form-group">
            <button type="submit" class="btn btn-primary" style="font: inherit; cursor: pointer;">Update Password</button>
            <a href="<?php echo URLROOT; ?>/users/dashboard" class="btn btn-primary">Cancel</a>
        </div>

    </form>

</div>
