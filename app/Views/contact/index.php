<div class="container contact-container" style="max-width: 800px; margin: 30px auto; padding: 20px; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px;">

    <h1><?php echo htmlspecialchars($data['title']); ?></h1>
    <p>Have questions or feedback? Send us a message using the form below.</p>
    <hr style="margin: 1.5em 0;">

    <?php if (!empty($data['mail_success'])): ?>
        <div class="alert alert-success" style="background-color: #d4edda; color: #155724; border-color: #c3e6cb; padding: 10px; border-radius: 4px; margin-bottom: 15px;">
            <?php echo htmlspecialchars($data['mail_success']); ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($data['mail_error'])): ?>
        <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; padding: 10px; border-radius: 4px; margin-bottom: 15px;">
            <?php echo htmlspecialchars($data['mail_error']); ?>
        </div>
    <?php endif; ?>

    <?php if (empty($data['mail_success'])): ?>
        <form action="<?php echo URLROOT; ?>/contact" method="post" novalidate>

            <!-- Name -->
            <div class="form-group" style="margin-bottom: 15px; display: flex; flex-direction: column;">
                <label for="name" style="color: #735339; margin-bottom: 5px;">Your Name: <sup>*</sup></label>
                <input type="text" style="margin: 0 1em; border: 1px solid #800000; border-radius: 5px; height: 30px; padding: 0 8px; font-size: medium; font-family: Alata, sans-serif;" name="name" id="name" class="form-control <?php echo (!empty($data['name_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($data['name']); ?>" required>
                <span class="invalid-feedback" style="color: #D8000C; font-size: 0.85em; display: block;"><?php echo $data['name_err']; ?></span>
            </div>

            <!-- Email -->
            <div class="form-group" style="margin-bottom: 15px; display: flex; flex-direction: column;">
                <label for="email" style="color: #735339; margin-bottom: 5px;">Your Email: <sup>*</sup></label>
                <input type="email" style="margin: 0 1em; border: 1px solid #800000; border-radius: 5px; height: 30px; padding: 0 8px; font-size: medium; font-family: Alata, sans-serif;" name="email" id="email" class="form-control <?php echo (!empty($data['email_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($data['email']); ?>" required>
                <span class="invalid-feedback" style="color: #D8000C; font-size: 0.85em; display: block;"><?php echo $data['email_err']; ?></span>
            </div>

            <!-- Subject -->
            <div class="form-group" style="margin-bottom: 15px; display: flex; flex-direction: column;">
                <label for="subject" style="color: #735339; margin-bottom: 5px;">Subject: <sup>*</sup></label>
                <input type="text" style="margin: 0 1em; border: 1px solid #800000; border-radius: 5px; height: 30px; padding: 0 8px; font-size: medium; font-family: Alata, sans-serif;" name="subject" id="subject" class="form-control <?php echo (!empty($data['subject_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($data['subject']); ?>" required>
                <span class="invalid-feedback" style="color: #D8000C; font-size: 0.85em; display: block;"><?php echo $data['subject_err']; ?></span>
            </div>

            <!-- Message -->
            <div class="form-group" style="margin-bottom: 15px; display: flex; flex-direction: column;">
                <label for="message" style="color: #735339; margin-bottom: 5px;">Message: <sup>*</sup></label>
                <textarea name="message" style="margin: 0 1em; border: 1px solid #800000; border-radius: 5px; height: 180px; padding: 8px 8px; font-size: medium; resize: vertical; font-family: Alata, sans-serif;" id="message" class="form-control <?php echo (!empty($data['message_err'])) ? 'is-invalid' : ''; ?>" rows="6" required><?php echo htmlspecialchars($data['message']); ?></textarea>
                <span class="invalid-feedback" style="color: #D8000C; font-size: 0.85em; display: block;"><?php echo $data['message_err']; ?></span>
            </div>

            <!-- Submit Button -->
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Send Message</button>
            </div>

        </form>
    <?php endif; ?>

</div>