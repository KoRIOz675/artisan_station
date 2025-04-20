<?php
// Assumes Base Controller 'Controller' is autoloaded
class UsersController extends Controller
{

    private $usersModel;
    private $eventModel;

    public function __construct()
    {
        $this->usersModel = $this->model('Users');
        $this->eventModel = $this->model('Event');
        if (!$this->usersModel) {
            die("Error: Could not load required resources.");
        }
    }

    // Default action for /users or /users/index
    public function index()
    {
        if (isset($_SESSION['user_id'])) {
            // Logged in users go to dashboard
            $this->redirect('/users/dashboard');
        } else {
            // Not logged in, show login/register page
            $this->redirect('/users/loginRegister');
        }
    }

    // Handles BOTH Login and Registration form submissions and GET requests
    public function loginRegister()
    {
        echo ("<script>console.log('UsersController: loginRegister() called');</script>");
        // Redirect logged-in users away from this page
        if (isset($_SESSION['user_id'])) {
            $this->redirect('users/dashboard');
            return;
        }


        // Default data structure for the view
        $data = [
            'title' => 'Login / Register',
            'cssFile' => 'login-register.css', // Specific CSS for this view

            // Login Form Data & Errors
            'login_identifier' => '',
            'login_password' => '',
            'login_identifier_err' => '',
            'login_password_err' => '',
            'login_general_err' => '',

            // Register Form Data & Errors
            'register_username' => '',
            'register_email' => '',
            'register_password' => '',
            'register_confirm_password' => '',
            'register_username_err' => '',
            'register_email_err' => '',
            'register_password_err' => '',
            'register_confirm_password_err' => '',
            'register_general_err' => ''
        ];

        // Check if request is POST
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            // Sanitize POST data (basic example)
            // $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            // --- Process Registration ---
            if (isset($_POST['register_submit'])) {
                $data['register_username'] = trim(filter_input(INPUT_POST, 'register_username', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
                $data['register_email'] = trim(filter_input(INPUT_POST, 'register_email', FILTER_SANITIZE_EMAIL));
                $data['register_password'] = trim($_POST['register_password'] ?? '');
                $data['register_confirm_password'] = trim($_POST['register_confirm_password'] ?? '');

                // --- Validation ---
                // Username
                if (empty($data['register_username'])) {
                    $data['register_username_err'] = 'Please enter username.';
                } elseif (strlen($data['register_username']) < 3) { // Example length check
                    $data['register_username_err'] = 'Username must be at least 3 characters.';
                } elseif ($this->usersModel->findUserByUsername($data['register_username'])) {
                    $data['register_username_err'] = 'Username is already taken.';
                }

                // Email
                if (empty($data['register_email'])) {
                    $data['register_email_err'] = 'Please enter email.';
                } elseif (!filter_var($data['register_email'], FILTER_VALIDATE_EMAIL)) {
                    $data['register_email_err'] = 'Please enter a valid email format.';
                } elseif ($this->usersModel->findUserByEmail($data['register_email'])) {
                    $data['register_email_err'] = 'Email is already registered.';
                }

                // Password
                if (empty($data['register_password'])) {
                    $data['register_password_err'] = 'Please enter password.';
                } elseif (strlen($data['register_password']) < 6) { // Consistent length check
                    $data['register_password_err'] = 'Password must be at least 6 characters.';
                }

                // Confirm Password
                if (empty($data['register_confirm_password'])) {
                    $data['register_confirm_password_err'] = 'Please confirm password.';
                } elseif ($data['register_password'] != $data['register_confirm_password']) {
                    $data['register_confirm_password_err'] = 'Passwords do not match.';
                }

                // --- Process if No Errors ---
                if (empty($data['register_username_err']) && empty($data['register_email_err']) && empty($data['register_password_err']) && empty($data['register_confirm_password_err'])) {

                    // Prepare data for model
                    $usersData = [
                        'username' => $data['register_username'],
                        'email' => $data['register_email'],
                        'password' => $data['register_password'],
                        'role' => 'customer', // Default role

                    ];

                    // Attempt to register user via model
                    if ($this->usersModel->register($usersData)) {
                        flash('register_success', 'Registration successful! You can now log in.', 'alert alert-success');
                        $this->redirect('users/loginRegister');
                        return; // Stop execution after redirect
                    } else {
                        flash('register_error', 'Registration failed due to a server error. Please try again.', 'alert alert-danger');
                        $data['register_general_err'] = 'Registration failed due to a server error. Please try again.';
                    }
                }
                // If validation fails, fall through to load view with errors
            }

            // --- Process Login ---
            elseif (isset($_POST['login_submit'])) {

                // Get raw inputs directly from $_POST
                $raw_identifier = $_POST['login_identifier'] ?? '';
                $raw_password = $_POST['login_password'] ?? '';

                $data['login_identifier'] = trim(filter_var($raw_identifier, FILTER_SANITIZE_EMAIL)); // email
                $data['login_password'] = trim($raw_password);

                // --- Validation ---
                if (empty($data['login_identifier'])) {
                    $data['login_identifier_err'] = 'Please enter your email address.';
                } elseif (!filter_var($data['login_identifier'], FILTER_VALIDATE_EMAIL)) {
                    $data['login_identifier_err'] = 'Please enter a valid email format.';
                }
                if (empty($data['login_password'])) {
                    $data['login_password_err'] = 'Please enter password.';
                }

                // --- Process if No Errors ---
                if (empty($data['login_identifier_err']) && empty($data['login_password_err'])) {
                    // Attempt to log in via model
                    $loggedInUser = $this->usersModel->login($data['login_identifier'], $data['login_password']);

                    if ($loggedInUser === 'inactive') {
                        // User found, password correct, but account inactive
                        $data['login_general_err'] = 'Your account is inactive. Please contact support.';
                        $data['login_password'] = ''; // Clear password field
                    } elseif ($loggedInUser) {
                        // Login successful - User object returned
                        $this->createUserSession($loggedInUser);
                        $this->redirect('users/dashboard'); // Redirect to user dashboard
                        return; // Stop execution
                    } else {
                        // Login failed (user not found or password incorrect)
                        $data['login_general_err'] = 'Invalid credentials. Please check your email/username and password.';
                        $data['login_password'] = ''; // Clear password field for security
                    }
                }
                // If validation fails, fall through to load view with errors
            }

            // Fall through: If POST request had errors or failed login/registration, load view with data/errors
            $this->view('/users/login-register', $data);
        } else {
            // --- Process GET Request ---
            // Display the form page for a GET request

            // Check for simple success message from registration redirect
            if (isset($_GET['registered']) && $_GET['registered'] === 'success') {
                // Use the general login error field to display success message
                $data['login_general_err'] = 'Registration successful! You can now log in.';
                echo "<script>console.log('GET request: Registration success message set');</script>";
            }
            // Load the view with default/empty data (or success message)
            $this->view('/users/login-register', $data);
        }
    }

    // Helper function to create user session after successful login
    private function createUserSession($user)
    {
        // Regenerate session ID for security upon login
        session_regenerate_id(true);

        // Set session variables using object properties
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_email'] = $user->email;
        $_SESSION['user_username'] = $user->username;
        $_SESSION['user_role'] = $user->role;
        $_SESSION['user_first_name'] = $user->first_name ?? null;
        $_SESSION['user_last_name'] = $user->last_name ?? null;
        $_SESSION['user_shop_name'] = $user->shop_name ?? null;
        $_SESSION['user_bio'] = $user->bio ?? '';
        $_SESSION['user_is_active'] = $user->is_active ?? 1;
    }

    // Logout user
    public function logout()
    {
        // Unset all session variables for this user
        $_SESSION = array();

        // Destroy the session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        // Destroy the session data on the server
        session_destroy();

        // Redirect to login page
        $this->redirect('/users/loginRegister');
    }

    public function dashboard()
    {
        if (!isset($_SESSION['user_id'])) {
            flash('login_required', 'You must be logged in to view that page.', 'alert alert-warning'); // Optional flash message
            $this->redirect('/users/loginRegister');
            return;
        }

        // If the code reaches here, the user IS logged in. Proceed with loading dashboard data.
        $data = [
            'title' => 'My Dashboard',
            'cssFile' => 'dashboard.css', // Add this line
            'username' => $_SESSION['user_username'] ?? 'User',
            'email' => $_SESSION['user_email'] ?? '',
            'role' => $_SESSION['user_role'] ?? '',
            'first_name' => $_SESSION['user_first_name'] ?? '',
            'last_name' => $_SESSION['user_last_name'] ?? '',
            'shop_name' => $_SESSION['user_shop_name'] ?? null,
            'bio' => $_SESSION['user_bio'] ?? null,
            'is_active' => $_SESSION['user_is_active'] ?? 1,
            'orders' => [],
            'attended_events' => [],
            'my_events' => [],
            'arts' => [],
        ];

        // Pass data for orders, events, arts (initially empty or null)
        $data['orders'] = [];
        $data['events'] = [];
        $data['arts'] = [];

        // TODO: Fetch actual data from models based on user ID/role
        // if ($data['role'] === 'customer' || $data['role'] === 'admin') {
           // $orderModel = $this->model('Order');
           // $data['orders'] = $orderModel->getOrdersByCustomerId($_SESSION['user_id']);
           // $eventModel = $this->model('Event');
           // $data['events'] = $eventModel->getEventsAttendedByUserId($_SESSION['user_id']); // Needs model method
        // }
        if ($data['role'] === 'artisan') {
            $data['my_events'] = $this->eventModel->getEventsByArtisanId($_SESSION['user_id']);
        //    $productModel = $this->model('Product');
        //    $data['arts'] = $productModel->getProductsByArtisanId($_SESSION['user_id']);
        }
        $this->view('/users/dashboard', $data); // Ensure you have this view file
    }

    public function profile()
    {
        // --- PROTECTION CHECK ---
        if (!isset($_SESSION['user_id'])) {
            // If 'user_id' is NOT set in the session, redirect to login
            flash('login_required', 'You must be logged in to view that page.', 'alert alert-warning'); // Optional flash message
            $this->redirect('/users/loginRegister');
            return; // Stop execution of the profile method
        }
        // --- END PROTECTION CHECK ---

        // If the code reaches here, the user IS logged in. Proceed with loading profile data.
        $data = [
            'title' => 'My Profile',
            'username' => $_SESSION['user_username'] ?? 'User',
            // ... other data ...
        ];
        $this->view('/users/profile', $data); // Ensure you have this view file
    }

    public function manageUsers($data)
    {
        // Check if logged in AND if role is admin
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/users/loginRegister');
            return;
        }
        if ($_SESSION['user_role'] !== 'admin') {
            flash('auth_error', 'You do not have permission to access that area.', 'alert alert-danger');
            $this->redirect('/users/dashboard'); // Redirect non-admins away
            return;
        }

        // User is logged in AND is an admin - proceed
        // ... load users data ...
        $this->view('/admin/manage_users', data: $data);
    }

    public function editProfile() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('users/loginRegister');
            return;
        }

        $userId = $_SESSION['user_id']; 

         if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Sanitize inputs individually
            $newUsername = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
            $newEmail = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
            $newFirstName = trim(filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
            $newLastName = trim(filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
            // Include artisan-specific fields if applicable
            $newBio = null;
            $newShopName = null;
            if ($_SESSION['user_role'] === 'artisan') {
                $newBio = trim(filter_input(INPUT_POST, 'bio', FILTER_SANITIZE_FULL_SPECIAL_CHARS)); // Adjust filter if needed
                 $newShopName = trim(filter_input(INPUT_POST, 'shop_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
            }


            // Fetch current user data for comparison (needed for unique checks)
            $currentUser = $this->usersModel->getUserById($userId);
            if (!$currentUser) {
                 // Should not happen if logged in, but handle defensively
                 // flash('error', 'Could not retrieve your user data.', 'alert alert-danger');
                 $this->redirect('users/dashboard');
                 return;
            }

            // Prepare data array for validation and potential view reload
             $data = [
                'title' => 'Edit Profile',
                'cssFile' => 'edit-profile.css', // Optional specific CSS
                'user' => $currentUser, // Pass current data back to view immediately
                 // Submitted values (used for repopulating form on error)
                'username' => $newUsername,
                'email' => $newEmail,
                'first_name' => $newFirstName,
                'last_name' => $newLastName,
                'bio' => $newBio,
                'shop_name' => $newShopName,
                 // Error messages
                 'username_err' => '',
                 'email_err' => '',
                 'first_name_err' => '', // Add if making required
                 'last_name_err' => '',  // Add if making required
                 'shop_name_err' => '', // Add if making required for artisans
                 'general_err' => ''
            ];


            // --- Validation ---
            // Username
            if (empty($newUsername)) {
                $data['username_err'] = 'Please enter username.';
            } elseif ($newUsername !== $currentUser->username && $this->usersModel->findUserByUsername($newUsername)) {
                 // Check if username changed AND if the new one is taken by SOMEONE ELSE
                $data['username_err'] = 'Username is already taken.';
            }

            // Email
            if (empty($newEmail)) {
                $data['email_err'] = 'Please enter email.';
            } elseif (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
                $data['email_err'] = 'Please enter a valid email format.';
            } elseif ($newEmail !== $currentUser->email && $this->usersModel->findUserByEmail($newEmail)) {
                 // Check if email changed AND if the new one is taken by SOMEONE ELSE
                 $data['email_err'] = 'Email is already registered by another user.';
             }

            // Add validation for first_name, last_name, shop_name if they become required

             // --- If No Errors, Proceed with Update ---
            if (empty($data['username_err']) && empty($data['email_err']) /* && other errors empty */) {

                // Prepare data for the model update method
                $updateData = [
                    'id' => $userId,
                    'username' => $newUsername,
                    'email' => $newEmail,
                    'first_name' => $newFirstName,
                    'last_name' => $newLastName,
                ];
                 // Add artisan fields only if user is artisan
                if ($_SESSION['user_role'] === 'artisan') {
                     $updateData['bio'] = $newBio;
                     $updateData['shop_name'] = $newShopName;
                }

                // Attempt to update profile via model
                if ($this->usersModel->updateProfile($updateData)) {
                    // Update session variables if username/email changed
                    $_SESSION['user_username'] = $newUsername;
                    $_SESSION['user_email'] = $newEmail;
                    $_SESSION['user_first_name'] = $newFirstName;
                    $_SESSION['user_last_name'] = $newLastName;
                    $_SESSION['user_shop_name'] = $newShopName; // Update if artisan
                    $_SESSION['user_bio'] = $newBio; // Update if artisan

                    // flash('profile_update_success', 'Your profile has been updated.', 'alert alert-success');
                    $this->redirect('users/dashboard'); // Redirect back to dashboard
                    return;
                } else {
                     // flash('profile_update_error', 'Failed to update profile. Please try again.', 'alert alert-danger');
                     $data['general_err'] = 'Failed to update profile. Please try again.';
                     // Reload the view with the error
                      $this->view('users/edit_profile', $data);
                }

            } else {
                // Validation errors occurred, reload the form with errors and submitted data
                $this->view('users/edit_profile', $data);
            }

        // --- Handle GET Request (Display Form) ---
        } else {
            // Fetch current user data
            $user = $this->usersModel->getUserById($userId);

            if (!$user) {
                // Handle error if user not found (edge case for logged-in user)
                // flash('error', 'Could not retrieve user data.', 'alert alert-danger');
                $this->redirect('users/dashboard');
                return;
            }

            // Prepare data for the view
            $data = [
                'title' => 'Edit Profile',
                 'cssFile' => 'edit-profile.css', // Optional specific CSS
                // Pre-fill form fields with current data
                'username' => $user->username,
                'email' => $user->email,
                'first_name' => $user->first_name ?? '', // Use null coalesce for optional fields
                'last_name' => $user->last_name ?? '',
                'bio' => $user->bio ?? '',
                'shop_name' => $user->shop_name ?? '',
                // Empty errors for initial load
                'username_err' => '',
                'email_err' => '',
                'first_name_err' => '',
                'last_name_err' => '',
                'shop_name_err' => '',
                'general_err' => ''
            ];

            // Load the view
            $this->view('users/edit_profile', $data);
        }
    }

    public function changePassword() {
// 1. Ensure user is logged in
        if (!isset($_SESSION['user_id'])) {
            // flash('login_required', 'Please log in to change your password.', 'alert alert-warning');
            $this->redirect('users/loginRegister');
            return;
        }

        $userId = $_SESSION['user_id'];

        // Default data structure for the view
        $data = [
            'title' => 'Change Password',
            'cssFile' => 'change-password.css', // Optional specific CSS
            // Do NOT send passwords back to the view for security
            'current_password_err' => '',
            'new_password_err' => '',
            'confirm_new_password_err' => '',
            'general_err' => '',
            'success_message' => '' // For success feedback on the same page
        ];

        // --- Handle POST Request (Form Submission) ---
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Get raw passwords and ONLY trim them
            $currentPassword = trim($_POST['current_password'] ?? '');
            $newPassword = trim($_POST['new_password'] ?? '');
            $confirmNewPassword = trim($_POST['confirm_new_password'] ?? '');

            // --- Validation ---
            if (empty($currentPassword)) {
                $data['current_password_err'] = 'Please enter your current password.';
            }
            if (empty($newPassword)) {
                $data['new_password_err'] = 'Please enter a new password.';
            } elseif (strlen($newPassword) < 6) { // Keep length check
                $data['new_password_err'] = 'New password must be at least 6 characters.';
            }
            if (empty($confirmNewPassword)) {
                $data['confirm_new_password_err'] = 'Please confirm your new password.';
            } elseif ($newPassword !== $confirmNewPassword) {
                $data['confirm_new_password_err'] = 'New passwords do not match.';
            }

            // --- If Validation Passes, Attempt Update ---
            if (empty($data['current_password_err']) && empty($data['new_password_err']) && empty($data['confirm_new_password_err'])) {

                 // Call the PLAIN TEXT update model method
                $updateResult = $this->usersModel->updatePasswordPlain($userId, $currentPassword, $newPassword);

                if ($updateResult === true) {
                    // Success!
                     $data['success_message'] = 'Your password has been updated successfully!';
                     // Reload view with success message
                     $this->view('users/change_password', $data);
                     return; // Stop execution
                } elseif ($updateResult === 'current_password_incorrect') {
                     $data['current_password_err'] = 'Your current password is incorrect.';
                } elseif ($updateResult === 'user_not_found') {
                     $data['general_err'] = 'Could not find user account.'; // Should not happen
                } else { // Includes 'db_error'
                     $data['general_err'] = 'Could not update password due to a server error. Please try again.';
                }
                // If errors occurred, fall through to reload view with errors
            }
             // Reload the view with errors if validation failed or update failed
              $this->view('users/change_password', $data);

        // --- Handle GET Request (Display Form) ---
        } else {
            // Just load the view with the empty form
            $this->view('users/change_password', $data);
        }
    }

    public function deleteAccount() {
        // 1. Ensure user is logged in
        if (!isset($_SESSION['user_id'])) {
            // Should not be reachable if triggered from dashboard, but double-check
            $this->redirect('users/loginRegister');
            return;
        }

        // 2. Ensure it's a POST request to prevent accidental deletion via GET
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // flash('error', 'Invalid request method for account deletion.', 'alert alert-danger');
            $this->redirect('users/dashboard'); // Redirect back if not POST
            return;
        }

        // 3. Get the User ID *from the session* (most secure way)
        $userIdToDelete = $_SESSION['user_id'];

        // 4. Attempt to delete the user via the model
        if ($this->usersModel->deleteById($userIdToDelete)) {
            $this->logout(); // This handles session destruction and redirection
            return; // Ensure script stops after calling logout's redirect

        } else {
            // 6. Deletion failed (Database error)
            // flash('error', 'Failed to delete your account due to a server error. Please contact support.', 'alert alert-danger');
            $this->redirect('users/dashboard'); // Redirect back to dashboard with an error message
            return;
        }
    }
}
