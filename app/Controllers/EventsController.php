<?php

class EventsController extends Controller
{

    private $eventModel;

    public function __construct()
    {

        $this->eventModel = $this->model('Event');
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
            // Event not found or not active
            error_log("Active event not found for slug: {$cleanSlug}");
            $this->redirect('events');
            return;
        }

        // Prepare data for the view
        $data = [
            'title' => htmlspecialchars($event->name), // Use event name as page title
            'cssFile' => 'event-show.css', // Optional specific CSS
            'event' => $event // Pass the full event object
        ];

        // Load the single event view
        $this->view('events/show', $data);
    }
}
