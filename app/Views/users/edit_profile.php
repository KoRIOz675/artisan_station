<div class="container edit-profile-container" style="max-width: 800px; margin: 30px auto; padding: 20px; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px;">

    <h2>Edit Your Profile</h2>
    <hr style="margin: 1.5em 0;">

    <?php if (!empty($data['general_err'])): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($data['general_err']); ?></div>
    <?php endif; ?>


    <form action="<?php echo URLROOT; ?>/users/editProfile" method="post" novalidate enctype="multipart/form-data">

        <!-- Profile Picture -->
        <div class="form-group" style="margin-bottom: 15px; display: flex; flex-direction: column;">
            <div style="display: flex; justify-content: left; align-items: top;">
                <label style="color: #735339; margin-bottom: 5px;">Edit Profile Picture:</label>
                <div style="margin-left: 25em;">
                    <?php
                    $currentImage = $data['current_image_path'] ?? ($data['user']->profile_picture_path ?? null);
                    $imageSrc = !empty($currentImage)
                        ? PROFILE_IMG_URL_PREFIX . htmlspecialchars($currentImage)
                        : PROFILE_IMG_URL_PREFIX . 'default_artist.jpg';
                    ?>
                    <img src="<?php echo $imageSrc; ?>" alt="Current Profile Picture"
                        id="profilePicturePreview"
                        style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; margin-bottom: 10px; border: 1px solid #ccc;">
                </div>
            </div>
            <label for="profile_picture" style="color: #735339; margin-bottom: 5px;">Upload New Picture: (Optional)</label>
            <input type="file" name="profile_picture" id="profile_picture"
                accept="image/png, image/jpeg, image/gif, image/webp"
                class="form-control-file <?php echo (!empty($data['image_err'])) ? 'is-invalid' : ''; ?>">
            <span class="invalid-feedback" style="color: #D8000C; font-size: 0.85em; display: block;"><?php echo $data['image_err'] ?? ''; ?></span>
            <small class="form-text text-muted">Upload JPG, PNG, GIF, WEBP. Max 3MB.</small>
        </div>
        <hr style="margin: 1.5em 0;">


        <!-- Username -->
        <div class="form-group" style="margin-bottom: 15px; display: flex; flex-direction: column;">
            <label for="username" style="color: #735339; margin-bottom: 5px;">Username: <sup>*</sup></label>
            <input type="text" style="margin: 0 1em; border: 1px solid #800000; border-radius: 5px; height: 30px; padding: 0 8px; font-size: medium; font-family: Alata, sans-serif;" name="username" id="username" class="form-control <?php echo (!empty($data['username_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($data['username']); ?>" required>
            <span class="invalid-feedback" style="color: #D8000C; font-size: 0.85em; display: block;"><?php echo $data['username_err']; ?></span>
        </div>

        <!-- Email -->
        <div class="form-group" style="margin-bottom: 15px; display: flex; flex-direction: column;">
            <label for="email" style="color: #735339; margin-bottom: 5px;">Email: <sup>*</sup></label>
            <input type="email" style="margin: 0 1em; border: 1px solid #800000; border-radius: 5px; height: 30px; padding: 0 8px; font-size: medium; font-family: Alata, sans-serif;" name="email" id="email" class="form-control <?php echo (!empty($data['email_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($data['email']); ?>" required>
            <span class="invalid-feedback" style="color: #D8000C; font-size: 0.85em; display: block;"><?php echo $data['email_err']; ?></span>
        </div>

        <!-- First Name (Optional Field Example) -->
        <div class="form-group" style="margin-bottom: 15px; display: flex; flex-direction: column;">
            <label for="first_name" style="color: #735339; margin-bottom: 5px;">First Name:</label>
            <input type="text" style="margin: 0 1em; border: 1px solid #800000; border-radius: 5px; height: 30px; padding: 0 8px; font-size: medium; font-family: Alata, sans-serif;" name="first_name" id="first_name" class="form-control <?php echo (!empty($data['first_name_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($data['first_name']); ?>">
            <span class="invalid-feedback" style="color: #D8000C; font-size: 0.85em; display: block;"><?php echo $data['first_name_err']; ?></span>
        </div>

        <!-- Last Name (Optional Field Example) -->
        <div class="form-group" style="margin-bottom: 15px; display: flex; flex-direction: column;">
            <label for="last_name" style="color: #735339; margin-bottom: 5px;">Last Name:</label>
            <input type="text" style="margin: 0 1em; border: 1px solid #800000; border-radius: 5px; height: 30px; padding: 0 8px; font-size: medium; font-family: Alata, sans-serif;" name="last_name" id="last_name" class="form-control <?php echo (!empty($data['last_name_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($data['last_name']); ?>">
            <span class="invalid-feedback" style="color: #D8000C; font-size: 0.85em; display: block;"><?php echo $data['last_name_err']; ?></span>
        </div>

        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'artisan'): ?>
            <hr style="margin: 1.5em 0;">
            <h4>Artisan Information</h4>

            <!-- Shop Name -->
            <div class="form-group" style="margin-bottom: 15px; display: flex; flex-direction: column;">
                <label for="shop_name" style="color: #735339; margin-bottom: 5px;">Shop Name:</label>
                <input type="text" style="margin: 0 1em; border: 1px solid #800000; border-radius: 5px; height: 30px; padding: 0 8px; font-size: medium; font-family: Alata, sans-serif;" name="shop_name" id="shop_name" class="form-control <?php echo (!empty($data['shop_name_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($data['shop_name']); ?>">
                <span class="invalid-feedback" style="color: #D8000C; font-size: 0.85em; display: block;"><?php echo $data['shop_name_err']; ?></span>
            </div>

            <!-- Bio -->
            <div class="form-group" style="margin-bottom: 15px; display: flex; flex-direction: column;">
                <label for="bio" style="color: #735339; margin-bottom: 5px;">Bio:</label>
                <textarea name="bio" style="margin: 0 1em; border: 1px solid #800000; border-radius: 5px; height: 180px; padding: 8px 8px; font-size: medium; resize: vertical; font-family: Alata, sans-serif;" id="bio" class="form-control" rows="5"><?php echo htmlspecialchars($data['bio']); ?></textarea>
                <?php // No error span shown for bio, but could be added 
                ?>
            </div>
        <?php endif; ?>


        <hr style="margin: 1.5em 0;">

        <!-- Submit Button -->
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="<?php echo URLROOT; ?>/users/dashboard" class="btn btn-secondary" style="background-color: #6c757d; color: white; margin-left: 10px;">Cancel</a>
        </div>

    </form>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('profile_picture');
        const imagePreview = document.getElementById('profilePicturePreview');

        if (fileInput && imagePreview) {
            fileInput.addEventListener('change', function(event) {
                const file = event.target.files[0]; // Get the selected file

                if (file) {
                    // Check if the file is an image (basic check)
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();

                        // Define what happens when the reader loads the file
                        reader.onload = function(e) {
                            // Set the src attribute of the img tag to the file data URL
                            imagePreview.src = e.target.result;
                        }

                        // Read the file as a Data URL (base64 encoded string)
                        reader.readAsDataURL(file);
                    } else {
                        // Optional: Alert user or clear preview if file is not an image
                        alert('Please select a valid image file (JPG, PNG, GIF, WEBP).');
                        // Reset file input if needed (can be tricky across browsers)
                        // fileInput.value = '';
                        // imagePreview.src = '<?php echo $imageSrc; ?>'; // Reset to original image
                    }
                } else {
                    // Optional: No file selected, reset to original image if needed
                    // imagePreview.src = '<?php echo $imageSrc; ?>';
                }
            });
        } else {
            console.error("Could not find file input or image preview element for profile picture.");
        }
    });
</script>