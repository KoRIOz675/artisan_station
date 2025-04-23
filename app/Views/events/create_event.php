<div class="container create-event-container" style="max-width: 800px; margin: 30px auto; padding: 20px; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px;">

    <h2>Create New Event</h2>
    <hr style="margin: 1.5em 0;">

    <?php if (!empty($data['general_err'])): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($data['general_err']); ?></div>
    <?php endif; ?>

    <form action="<?php echo URLROOT; ?>/events/store" method="post" novalidate enctype="multipart/form-data">

        <!-- Event Name -->
        <div class="form-group" style="margin-bottom: 15px; display: flex; flex-direction: column;">
            <label for="name" style="color: #735339; margin-bottom: 5px;">Event Name: <sup>*</sup></label>
            <input type="text" name="name" style="margin: 0 1em; border: 1px solid #800000; border-radius: 5px; height: 30px; padding: 0 8px; font-size: medium; font-family: Alata, sans-serif;" id="name" class="form-control <?php echo (!empty($data['name_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($data['name']); ?>" required>
            <span class="invalid-feedback" style="color: #D8000C; font-size: 0.85em; display: block;"><?php echo $data['name_err']; ?></span>
        </div>

        <!-- Description -->
        <div class="form-group" style="margin-bottom: 15px; display: flex; flex-direction: column;">
            <label for="description" style="color: #735339; margin-bottom: 5px;">Description: <sup>*</sup></label>
            <textarea name="description" style="margin: 0 1em; border: 1px solid #800000; border-radius: 5px; height: 130px; padding: 8px 8px; font-size: medium; resize: vertical;" id="description" class="form-control <?php echo (!empty($data['description_err'])) ? 'is-invalid' : ''; ?>" rows="5" required><?php echo htmlspecialchars($data['description']); ?></textarea>
            <span class="invalid-feedback" style="color: #D8000C; font-size: 0.85em; display: block;"><?php echo $data['description_err']; ?></span>
        </div>

        <!-- Start Date/Time -->
        <div style="display: flex; gap: 15px; margin-bottom: 15px;">
            <div class="form-group" style="flex: 1; ">
                <label for="start_date" style="color: #735339; margin-bottom: 5px;">Start Date: <sup>*</sup></label>
                <input type="date" style="margin: 0 1em; border: 1px solid #800000; border-radius: 5px; height: 30px; padding: 0 8px; font-size: medium; font-family: Alata, sans-serif;" name="start_date" id="start_date" class="form-control <?php echo (!empty($data['start_datetime_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($data['start_date']); ?>" required>
            </div>
            <div class="form-group" style="flex: 1;">
                <label for="start_time" style="color: #735339; margin-bottom: 5px;">Start Time: <sup>*</sup></label>
                <input type="time" style="margin: 0 1em; border: 1px solid #800000; border-radius: 5px; height: 30px; padding: 0 8px; font-size: medium; font-family: Alata, sans-serif;" name="start_time" id="start_time" class="form-control <?php echo (!empty($data['start_datetime_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($data['start_time']); ?>" required>
            </div>
        </div>
        <span class="invalid-feedback" style="color: #D8000C; font-size: 0.85em; display: block; margin-top: -10px; margin-bottom: 15px;"><?php echo $data['start_datetime_err']; ?></span>


        <!-- End Date/Time (Optional) -->
        <div style="display: flex; gap: 15px; margin-bottom: 15px;">
            <div class="form-group" style="flex: 1;">
                <label for="end_date" style="color: #735339; margin-bottom: 5px;">End Date: (Optional)</label>
                <input type="date" style="margin: 0 1em; border: 1px solid #800000; border-radius: 5px; height: 30px; padding: 0 8px; font-size: medium; font-family: Alata, sans-serif;" name="end_date" id="end_date" class="form-control <?php echo (!empty($data['end_datetime_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($data['end_date']); ?>">
            </div>
            <div class="form-group" style="flex: 1;">
                <label for="end_time" style="color: #735339; margin-bottom: 5px;">End Time: (Optional)</label>
                <input type="time" style="margin: 0 1em; border: 1px solid #800000; border-radius: 5px; height: 30px; padding: 0 8px; font-size: medium; font-family: Alata, sans-serif;" name="end_time" id="end_time" class="form-control <?php echo (!empty($data['end_datetime_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($data['end_time']); ?>">
            </div>
        </div>
        <span class="invalid-feedback" style="color: #D8000C; font-size: 0.85em; display: block; margin-top: -10px; margin-bottom: 15px;"><?php echo $data['end_datetime_err']; ?></span>


        <!-- Location -->
        <div class="form-group" style="margin-bottom: 15px; display: flex; flex-direction: column;">
            <label for="location" style="color: #735339; margin-bottom: 5px;">Location: (Optional)</label>
            <input type="text" style="margin: 0 1em; border: 1px solid #800000; border-radius: 5px; height: 30px; padding: 0 8px; font-size: medium; font-family: Alata, sans-serif;" name="location" id="location" class="form-control" value="<?php echo htmlspecialchars($data['location']); ?>">
            <?php // Add error span if location validation is added 
            ?>
        </div>

        <!-- Image Upload (Basic - requires backend processing) -->
        <div class="form-group">
            <label for="image" style="color: #735339; margin-bottom: 5px;">Event Image: (Optional)</label>
            <input type="file" name="image" id="image" class="form-control-file <?php echo (!empty($data['image_err'])) ? 'is-invalid' : ''; ?>">
            <span class="invalid-feedback" style="color: #D8000C; font-size: 0.85em; display: block;"><?php echo $data['image_err'] ?? ''; ?></span>
            <small class="form-text text-muted">Upload an image (JPG, PNG, GIF). Max size: 2MB.</small>
        </div>


        <hr style="margin: 1.5em 0;">

        <!-- Submit Button -->
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Create Event</button>
            <a href="<?php echo URLROOT; ?>/users/dashboard" class="btn btn-secondary" style="background-color: #6c757d; color: white; margin-left: 10px;">Cancel</a>
        </div>

    </form>

</div>