<div class="container mt-5">
    <h2>Edit Event</h2>
    <p>Update the details for your event below.</p>

    <?php flash('event_message'); ?>

    <form action="<?php echo URLROOT; ?>/events/update/<?php echo $data['event']->id; ?>" method="post" enctype="multipart/form-data">
        <!-- <input type="hidden" name="event_id" value="<?php echo $data['event']->id; ?>"> -->

        <div class="form-group mb-3">
            <label for="name">Event Name: <sup>*</sup></label>
            <input type="text" name="name" class="form-control <?php echo (!empty($data['name_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($data['event']->name); ?>">
            <span class="invalid-feedback"><?php echo $data['name_err']; ?></span>
        </div>

        <div class="form-group mb-3">
            <label for="description">Description: <sup>*</sup></label>
            <textarea name="description" class="form-control <?php echo (!empty($data['description_err'])) ? 'is-invalid' : ''; ?>" rows="5"><?php echo htmlspecialchars($data['event']->description); ?></textarea>
            <span class="invalid-feedback"><?php echo $data['description_err']; ?></span>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="start_datetime">Start Date & Time: <sup>*</sup></label>
                    <input type="datetime-local" name="start_datetime" class="form-control <?php echo (!empty($data['start_datetime_err'])) ? 'is-invalid' : ''; ?>"
                        value="<?php echo htmlspecialchars($data['start_datetime_form_value'] ?? ''); ?>">
                    <span class="invalid-feedback"><?php echo $data['start_datetime_err']; ?></span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="end_datetime">End Date & Time: (Optional)</label>
                    <input type="datetime-local" name="end_datetime" class="form-control <?php echo (!empty($data['end_datetime_err'])) ? 'is-invalid' : ''; ?>"
                        value="<?php echo htmlspecialchars($data['end_datetime_form_value'] ?? ''); ?>">
                    <span class="invalid-feedback"><?php echo $data['end_datetime_err']; ?></span>
                </div>
            </div>
        </div>

        <div class="form-group mb-3">
            <label for="location">Location: (Optional)</label>
            <input type="text" name="location" class="form-control <?php echo (!empty($data['location_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($data['event']->location ?? ''); ?>">
            <span class="invalid-feedback"><?php echo $data['location_err']; ?></span>
        </div>

        <div class="form-group mb-3">
            <label for="image">Event Image: (Leave blank to keep current image)</label>
            <?php if (!empty($data['event']->image_path)) : ?>
                <p><img src="<?php echo URLROOT . '/' . $data['event']->image_path; ?>" alt="<?php echo htmlspecialchars($data['event']->name); ?>" style="max-width: 200px; display:block; margin-bottom:10px;"></p>
            <?php endif; ?>
            <input type="file" name="image" class="form-control <?php echo (!empty($data['image_err'])) ? 'is-invalid' : ''; ?>">
            <span class="invalid-feedback"><?php echo $data['image_err']; ?></span>
        </div>

        <div class="form-group form-check mb-3">
            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" <?php echo ($data['event']->is_active ?? 1) ? 'checked' : ''; ?>>
            <label class="form-check-label" for="is_active">Active (Visible to public)</label>
        </div>

        <div class="form-group mb-3">
            <button type="submit" value="Update Event" class="btn btn-primary">Update Event</button>
            <a href="<?php echo URLROOT; ?>/events/show/<?php echo $data['event']->slug; ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>