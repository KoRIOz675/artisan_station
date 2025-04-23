<?php
// app/Controllers/ContactController.php

class ContactController extends Controller
{

    public function __construct()
    {
        // No models typically needed for a simple email contact form
        // Session should be started in index.php if using flash messages
    }

    /**
     * Displays the contact form (GET) or processes submission (POST).
     */
    public function index()
    {

        // Default data structure for the view
        $data = [
            'title' => 'Contact Us',
            'name' => '',
            'email' => '',
            'subject' => '',
            'message' => '',
            'name_err' => '',
            'email_err' => '',
            'subject_err' => '',
            'message_err' => '',
            'mail_success' => '',
            'mail_error' => ''
        ];

        // --- Handle POST Request (Form Submission) ---
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            // Sanitize POST data individually
            $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
            $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
            $subject = trim(filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
            $message = trim(filter_input(INPUT_POST, 'message', FILTER_SANITIZE_FULL_SPECIAL_CHARS)); // Or allow basic tags if needed

            // Put submitted data (sanitized) back into $data for repopulation
            $data['name'] = $name;
            $data['email'] = $email;
            $data['subject'] = $subject;
            $data['message'] = $message; // Repopulate message too

            // --- Validation ---
            if (empty($name)) {
                $data['name_err'] = 'Please enter your name.';
            }
            if (empty($email)) {
                $data['email_err'] = 'Please enter your email address.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $data['email_err'] = 'Please enter a valid email format.';
            }
            if (empty($subject)) {
                $data['subject_err'] = 'Please enter a subject.';
            }
            if (empty($message)) {
                $data['message_err'] = 'Please enter your message.';
            } elseif (strlen($message) < 10) {
                $data['message_err'] = 'Message must be at least 10 characters long.';
            }


            // --- If No Validation Errors, Attempt to Send Email ---
            if (empty($data['name_err']) && empty($data['email_err']) && empty($data['subject_err']) && empty($data['message_err'])) {

                // --- Email Configuration ---
                // IMPORTANT: Replace with your actual recipient email address
                $recipientEmail = CONTACT_FORM_RECIPIENT_EMAIL; // Consider putting this in config.php
                $emailSubject = "Contact Form Submission: " . $subject; // Add prefix

                // Construct email body
                $emailBody = "You have received a new message from your website contact form.\n\n";
                $emailBody .= "Here are the details:\n";
                $emailBody .= "Name: " . $name . "\n";
                $emailBody .= "Email: " . $email . "\n";
                $emailBody .= "Subject: " . $subject . "\n";
                $emailBody .= "Message:\n" . $message . "\n";

                $headers = "From: noreply@" . ($_SERVER['SERVER_NAME'] ?? 'localhost') . "\r\n";
                $headers .= "Reply-To: " . $email . "\r\n";
                $headers .= "X-Mailer: PHP/" . phpversion();

                if (mail($recipientEmail, $emailSubject, $emailBody, $headers)) {
                    flash('contact_success', 'Thank you for your message! We will get back to you soon.', 'alert alert-success');
                    $data['mail_success'] = 'Thank you for your message! We will get back to you soon.';
                    $data['name'] = '';
                    $data['email'] = '';
                    $data['subject'] = '';
                    $data['message'] = '';
                    $this->view('contact/index', $data);
                    return;
                } else {
                    error_log("PHP mail() function failed for contact form submission from: " . $email);
                    $data['mail_error'] = 'Sorry, there was an error sending your message. Please try again later or contact us directly.';
                    $this->view('contact/index', $data); // Reload view with general error
                }
            } else {
                $this->view('contact/index', $data);
            }
        } else {
            $this->view('contact/index', $data);
        }
    }
}
