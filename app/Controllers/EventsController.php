<?php

class EventsController extends Controller
{

    private $eventModel;
    private $userModel;

    public function __construct()
    {

        $this->eventModel = $this->model('Event');
        $this->userModel = $this->model('Users');
        if (!$this->eventModel) {
            die("Error loading event resources.");
        }
    }

    public function index()
    {
        $events = $this->eventModel->getActiveEvents(true);

        $data = [
            'title' => 'Upcoming Events',
            'events' => $events
        ];

        $this->view('events/index', $data);
    }

    // Display Event Creation Form
    public function create()
    {
        // Ensure user is an artisan
        if ($_SESSION['user_role'] !== 'artisan') {
            // flash('auth_error', 'Only artisans can create events.', 'alert alert-danger');
            $this->redirect('users/dashboard');
            return;
        }

        // Default data for the form view
        $data = [
            'title' => 'Create New Event',
            'cssFile' => 'create-event.css', // Optional specific CSS
            'name' => '',
            'description' => '',
            'start_date' => '', // Separate date/time for easier input
            'start_time' => '',
            'end_date' => '',
            'end_time' => '',
            'location' => '',
            // Add image handling later
            'name_err' => '',
            'description_err' => '',
            'start_datetime_err' => '',
            'end_datetime_err' => '',
            'general_err' => ''
        ];

        // Load the view with the form
        $this->view('events/create_event', $data);
    }


    // Process Event Creation Form Submission
    public function store()
    {
        // Ensure user is an artisan and request is POST
        if ($_SESSION['user_role'] !== 'artisan' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('users/dashboard'); // Or show an error
            return;
        }

        // Sanitize POST data
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS); // Basic sanitize

        // Combine date/time inputs
        $startDateTimeStr = trim($_POST['start_date'] . ' ' . $_POST['start_time']);
        $endDateTimeStr = trim($_POST['end_date'] . ' ' . $_POST['end_time']);

        // Validate date/time formats (basic example)
        $startDateTime = $startDateTimeStr ? date_create_from_format('Y-m-d H:i', $startDateTimeStr) : false;
        $endDateTime = $endDateTimeStr ? date_create_from_format('Y-m-d H:i', $endDateTimeStr) : null; // End date is optional

        // Prepare data for validation and view reload
        $data = [
            'title' => 'Create New Event',
            'cssFile' => 'create-event.css',
            'name' => trim($_POST['name']),
            'description' => trim($_POST['description']),
            'start_date' => trim($_POST['start_date']),
            'start_time' => trim($_POST['start_time']),
            'end_date' => trim($_POST['end_date']),
            'end_time' => trim($_POST['end_time']),
            'location' => trim($_POST['location']),
            // Image handling needed here
            'name_err' => '',
            'description_err' => '',
            'start_datetime_err' => '',
            'end_datetime_err' => '', // Optional: Check end >= start
            'general_err' => ''
        ];

        // --- Validation ---
        if (empty($data['name'])) {
            $data['name_err'] = 'Please enter the event name.';
        }
        if (empty($data['description'])) {
            $data['description_err'] = 'Please enter a description.';
        }
        if (!$startDateTime) {
            $data['start_datetime_err'] = 'Invalid start date or time format (Use YYYY-MM-DD and HH:MM).';
        } elseif ($endDateTime === false && !empty($endDateTimeStr)) { // Check format only if end date was provided but invalid
            $data['end_datetime_err'] = 'Invalid end date or time format (Use YYYY-MM-DD and HH:MM).';
        } elseif ($endDateTime !== null && $startDateTime && $endDateTime < $startDateTime) {
            $data['end_datetime_err'] = 'End date/time cannot be before the start date/time.';
        }

        $uploadedImageFilename = null; // Default to no image uploaded
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['image']['tmp_name'];
            $fileName = $_FILES['image']['name'];
            $fileSize = $_FILES['image']['size'];
            $fileType = $_FILES['image']['type'];
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));

            $allowedfileExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            $maxFileSize = 2 * 1024 * 1024; // 2 MB

            // Validate file type
            if (!in_array($fileExtension, $allowedfileExtensions)) {
                $data['image_err'] = 'Invalid file type. Allowed types: JPG, JPEG, PNG, GIF.';
            }
            // Validate file size
            elseif ($fileSize > $maxFileSize) {
                $data['image_err'] = 'File size exceeds the limit of 2MB.';
            } else {
                // Generate unique filename
                $newFileName = uniqid('event_', true) . '.' . $fileExtension;
                $dest_path = EVENT_IMG_UPLOAD_DIR . $newFileName; // Use constant

                // Attempt to move the file
                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    $uploadedImageFilename = $newFileName; // Store only the filename
                    error_log("Event image uploaded successfully: " . $newFileName);
                } else {
                    $data['image_err'] = 'Error uploading image. Please check server permissions.';
                    error_log("Error moving uploaded file to: " . $dest_path);
                }
            }
        } elseif (isset($_FILES['image']) && $_FILES['image']['error'] != UPLOAD_ERR_NO_FILE && $_FILES['image']['error'] != UPLOAD_ERR_OK) {
            // Handle other potential upload errors (optional but good)
            $data['image_err'] = 'An error occurred during file upload. Error code: ' . $_FILES['image']['error'];
            error_log("PHP Upload Error Code: " . $_FILES['image']['error']);
        }


        // --- If No Errors, Proceed to Create ---
        if (empty($data['name_err']) && empty($data['description_err']) && empty($data['start_datetime_err']) && empty($data['end_datetime_err'])) {

            // Prepare data for the model
            $eventData = [
                'artisan_id' => $_SESSION['user_id'], // Get ID from session
                'name' => $data['name'],
                'description' => $data['description'],
                'start_datetime' => $startDateTime->format('Y-m-d H:i:s'), // Format for DB
                'end_datetime' => $endDateTime ? $endDateTime->format('Y-m-d H:i:s') : null, // Format for DB or null
                'location' => $data['location'],
                'image_path' => $uploadedImageFilename
            ];

            // Attempt to create event via model
            if ($this->eventModel->createEvent($eventData)) {
                // flash('event_create_success', 'Event created successfully!', 'alert alert-success');
                $this->redirect('users/dashboard'); // Redirect back to dashboard
                return;
            } else {
                // flash('event_create_error', 'Failed to create event. Please try again.', 'alert alert-danger');
                $data['general_err'] = 'Failed to save event. Please try again.';
                // Reload the view with the error
                $this->view('events/create_event', $data);
            }
        } else {
            // Validation errors occurred, reload the form with errors
            $this->view('events/create_event', $data);
        }
    }

    public function show($slug = '')
    {
        $cleanSlug = filter_var(trim($slug), FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if (empty($cleanSlug)) {
            $this->redirect('events'); // Redirect to list if no slug
            return;
        }

        // Fetch event details including artisan info using the new model method
        $event = $this->eventModel->getActiveEventBySlugWithArtisan($cleanSlug);

        if (!$event || !is_object($event)) {
            $this->redirect('events');
            return;
        }

        $isAttending = false; // Default
        if (isset($_SESSION['user_id'])) { // Only check if logged in
            $isAttending = $this->eventModel->isUserAttending($_SESSION['user_id'], $event->id);
        }

        // Prepare data for the view
        $data = [
            'title' => htmlspecialchars($event->name), // Use event name as page title
            'event' => $event,
            'is_attending' => $isAttending
        ];

        error_log("Final check - isAttending type: " . gettype($isAttending));
        error_log("Final check - Data array for view: " . print_r($data, true));

        // Load the single event view
        $this->view('events/show', $data);
    }

    public function attend($eventId = 0)
    {
        // 1. Check Login
        if (!isset($_SESSION['user_id'])) {
            // flash('login_required', 'Please log in to attend events.', 'alert alert-warning');
            $this->redirect('users/loginRegister');
            return;
        }
        // 2. Validate Event ID
        $eventId = filter_var($eventId, FILTER_VALIDATE_INT);
        if (!$eventId || $eventId <= 0) {
            // flash('error', 'Invalid event specified.', 'alert alert-danger');
            $this->redirect('events');
            return; // Redirect to list
        }

        // 3. Call Model to Attend
        if ($this->eventModel->attendEvent($_SESSION['user_id'], $eventId)) {
            flash('success', 'You are now marked as attending!', 'alert alert-success');
        } else {
            flash('error', 'Could not mark attendance. You might already be attending or an error occurred.', 'alert alert-warning');
        }

        // 4. Redirect back to the event page (or wherever appropriate)
        // We need the slug to redirect back, fetch it first (or pass it)
        $event = $this->eventModel->getEventById($eventId);
        if ($event && $event->slug) {
            $this->redirect('events/show/' . $event->slug);
        } else {
            $this->redirect('events'); // Fallback
        }
    }

    public function unattend($eventId = 0)
    {
        // 1. Check Login
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('users/loginRegister');
            return;
        }
        // 2. Validate Event ID
        $eventId = filter_var($eventId, FILTER_VALIDATE_INT);
        if (!$eventId || $eventId <= 0) {
            $this->redirect('events');
            return;
        }

        // 3. Call Model to Unattend
        if ($this->eventModel->unattendEvent($_SESSION['user_id'], $eventId)) {
            // flash('success', 'You are no longer marked as attending.', 'alert alert-info');
        } else {
            // flash('error', 'Could not remove attendance.', 'alert alert-danger');
        }

        // 4. Redirect back to the event page
        $event = $this->eventModel->getEventById($eventId); // Need getEventById in model
        if ($event && $event->slug) {
            $this->redirect('events/show/' . $event->slug);
        } else {
            $this->redirect('events');
        }
    }

    public function edit($id = 0)
    {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'artisan') {
            flash('auth_error', 'Only artisans can edit events.', 'alert alert-danger');
            $this->redirect('users/loginRegister');
            return;
        }

        $id = filter_var($id, FILTER_VALIDATE_INT);
        if (!$id || $id <= 0) {
            flash('error', 'Invalid event ID.', 'alert alert-danger');
            $this->redirect('users/dashboard#event-created');
            return;
        }

        $event = $this->eventModel->getEventByIdForEdit($id);

        if (!$event || $event->artisan_id != $_SESSION['user_id']) {
            flash('error', 'Event not found or you do not have permission to edit it.', 'alert alert-danger');
            $this->redirect('users/dashboard#event-created');
            return;
        }

        // Prepare data for the form view
        $data = [
            'title' => 'Edit Event: ' . htmlspecialchars($event->name),
            'event' => $event, // Pass the whole event object
            'id' => $event->id,
            'name' => $event->name,
            'description' => $event->description,
            // Convert datetime from DB to separate date and time for form fields
            'start_datetime_form_value' => date('Y-m-d\TH:i', strtotime($event->start_datetime)),
            'end_datetime_form_value' => !empty($event->end_datetime) ? date('Y-m-d\TH:i', strtotime($event->end_datetime)) : '',
            'location' => $event->location,
            'current_image_path' => $event->image_path,
            'is_active' => $event->is_active,
            'name_err' => '',
            'description_err' => '',
            'start_datetime_err' => '',
            'end_datetime_err' => '',
            'image_err' => '',
            'general_err' => ''
        ];

        $this->view('events/edit', $data);
    }

    public function update($id = 0)
    {
        // Ensure user is an artisan and request is POST
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'artisan') {
            flash('auth_error', 'Unauthorized access.', 'alert alert-danger');
            $this->redirect('users/loginRegister');
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('users/dashboard#event-created');
            return;
        }

        $id = filter_var($id, FILTER_VALIDATE_INT);
        if (!$id || $id <= 0) {
            flash('error', 'Invalid event ID.', 'alert alert-danger');
            $this->redirect('users/dashboard#event-created');
            return;
        }

        // Fetch original event to check ownership and get current image path
        $originalEvent = $this->eventModel->getEventByIdForEdit($id);
        if (!$originalEvent || $originalEvent->artisan_id != $_SESSION['user_id']) {
            flash('error', 'Event not found or permission denied for update.', 'alert alert-danger');
            $this->redirect('users/dashboard#event-created');
            return;
        }

        // Sanitize POST data - CORRECTED LINES
        $input_name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '');
        $input_description = trim(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '');
        $raw_start_datetime = filter_input(INPUT_POST, 'start_datetime', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $raw_end_datetime = filter_input(INPUT_POST, 'end_datetime', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $input_location = trim(filter_input(INPUT_POST, 'location', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '');
        $input_is_active = isset($_POST['is_active']) ? 1 : 0;

        $startDateTime = $raw_start_datetime ? DateTime::createFromFormat('Y-m-d\TH:i', $raw_start_datetime) : false;

        $endDateTime = null; // Default for optional end date
        if (!empty($raw_end_datetime)) {
            $endDateTime = DateTime::createFromFormat('Y-m-d\TH:i', $raw_end_datetime);
            // $endDateTime will be false if parsing failed
        }

        // Prepare data for validation and view reload
        $data = [
            'title' => 'Edit Event: ' . htmlspecialchars($input_name),
            'event' => $originalEvent, // Pass original event for view if errors
            'id' => $id,
            'name' => $input_name,
            'description' => $input_description,
            'start_datetime_form_value' => $raw_start_datetime,
            'end_datetime_form_value' => $raw_end_datetime,
            'location' => $input_location,
            'current_image_path' => $originalEvent->image_path,
            'is_active' => $input_is_active,
            'name_err' => '',
            'description_err' => '',
            ' start_datetime_err ' => ' ',
            ' end_datetime_err ' => ' ',
            'image_err' => '',
            'general_err' => ''
        ];

        // --- Validation ---
        if (empty($data['name'])) $data['name_err'] = 'Please enter the event name.';
        if (empty($data['description'])) $data['description_err'] = 'Please enter a description.';

        if (!$startDateTime) {
            $data['start_datetime_err'] = 'Invalid start date or time format. Please use the date picker.';
        }

        if (!empty($raw_end_datetime) && $endDateTime === false) {
            // Only set error if a value was provided for end_datetime but it was invalid
            $data['end_datetime_err'] = 'Invalid end date or time format. Please use the date picker.';
        } elseif ($endDateTime && $startDateTime && $endDateTime < $startDateTime) {
            $data['end_datetime_err'] = 'End date/time cannot be before start date/time.';
        }

        // --- Image Upload Handling (if a new image is provided) ---
        // ... (image handling logic remains the same) ...
        $newImageFilename = null;
        $deleteOldImage = false;
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['image']['tmp_name'];
            $fileName = basename($_FILES['image']['name']);
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedfileExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $maxFileSize = 2 * 1024 * 1024; // 2MB

            if (!in_array($fileExtension, $allowedfileExtensions)) {
                $data['image_err'] = 'Invalid file type.';
            } elseif ($_FILES['image']['size'] > $maxFileSize) {
                $data['image_err'] = 'File size exceeds 2MB limit.';
            } else {
                $newFileName = uniqid('event_', true) . '.' . $fileExtension;
                $dest_path = EVENT_IMG_UPLOAD_DIR . $newFileName;
                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    $newImageFilename = $newFileName;
                    $deleteOldImage = true;
                } else {
                    $data['image_err'] = 'Error uploading new image.';
                    error_log("Error moving updated event image to: " . $dest_path);
                }
            }
        } elseif (isset($_FILES['image']) && $_FILES['image']['error'] != UPLOAD_ERR_NO_FILE  && $_FILES['image']['error'] != UPLOAD_ERR_OK) {
            $data['image_err'] = 'File upload error code: ' . $_FILES['image']['error'];
        }


        // --- If No Errors, Proceed to Update ---
        if (empty($data['name_err']) && empty($data['description_err']) && empty($data['start_datetime_err']) && empty($data['end_datetime_err']) && empty($data['image_err'])) {
            $eventDataForModel = [
                'id' => $id,
                'artisan_id' => $_SESSION['user_id'],
                'name' => $data['name'],
                'description' => $data['description'],
                'start_datetime' => $startDateTime->format('Y-m-d H:i:s'), // Format for DB
                'end_datetime' => $endDateTime ? $endDateTime->format('Y-m-d H:i:s') : null, // Format for DB or null
                'location' => $data['location'],
                'is_active' => $data['is_active']
            ];
            if ($newImageFilename !== null) {
                $eventDataForModel['image_path'] = $newImageFilename;
            }

            if ($this->eventModel->updateEvent($eventDataForModel)) {
                if ($deleteOldImage && !empty($originalEvent->image_path) && $originalEvent->image_path != $newImageFilename) {
                    $oldImagePathFull = EVENT_IMG_UPLOAD_DIR . $originalEvent->image_path;
                    if (file_exists($oldImagePathFull)) {
                        @unlink($oldImagePathFull);
                    }
                }
                flash('event_update_success', 'Event updated successfully!', 'alert alert-success');
                $this->redirect('users/dashboard#event-created');
                return;
            } else {
                $data['general_err'] = 'Failed to update event. Please try again.';
                if ($newImageFilename && file_exists(EVENT_IMG_UPLOAD_DIR . $newImageFilename)) {
                    @unlink(EVENT_IMG_UPLOAD_DIR . $newImageFilename);
                }
            }
        }
        // If validation errors or DB update failed, reload the edit form with errors
        $this->view('events/edit', $data);
    }

    public function delete($id = 0)
    {
        // Ensure user is an artisan and request is POST
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'artisan') {
            flash('auth_error', 'Unauthorized access.', 'alert alert-danger');
            $this->redirect('users/loginRegister');
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            flash('error', 'Invalid request method for delete.', 'alert alert-danger');
            $this->redirect('users/dashboard#event-created');
            return;
        }

        $id = filter_var($id, FILTER_VALIDATE_INT);
        if (!$id || $id <= 0) {
            flash('error', 'Invalid event ID for deletion.', 'alert alert-danger');
            $this->redirect('users/dashboard#event-created');
            return;
        }

        // Fetch event to get image path and verify ownership BEFORE deleting
        $eventToDelete = $this->eventModel->getEventByIdForEdit($id);
        if (!$eventToDelete || $eventToDelete->artisan_id != $_SESSION['user_id']) {
            flash('error', 'Event not found or permission denied for deletion.', 'alert alert-danger');
            $this->redirect('users/dashboard#event-created');
            return;
        }

        // Attempt to delete event via model
        if ($this->eventModel->deleteEvent($id, $_SESSION['user_id'])) { // We'll create this model method
            // Delete image file AFTER successful DB deletion
            if (!empty($eventToDelete->image_path)) {
                $imagePathFull = EVENT_IMG_UPLOAD_DIR . $eventToDelete->image_path;
                if (file_exists($imagePathFull)) {
                    if (!@unlink($imagePathFull)) {
                        error_log("Failed to delete event image (permissions?): " . $imagePathFull);
                    }
                }
            }
            flash('event_delete_success', 'Event deleted successfully.', 'alert alert-success');
        } else {
            flash('event_delete_error', 'Failed to delete event.', 'alert alert-danger');
        }
        $this->redirect('users/dashboard#event-created');
    }
}
