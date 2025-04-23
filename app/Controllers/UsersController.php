<?php
class UsersController extends Controller
{

    private $usersModel;
    private $eventModel;
    private $productModel;

    // ---------------------------------------------------------------------
    // Base functions
    // ---------------------------------------------------------------------

    // --- Constructor ---
    public function __construct()
    {
        $this->usersModel = $this->model('Users');
        $this->eventModel = $this->model('Event');
        $this->productModel = $this->model('Product');
        if (!$this->usersModel || !$this->eventModel || !$this->productModel) {
            die("Error: Could not load required resources.");
        }
    }

    // --- Default action ---
    public function index()
    {
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/users/dashboard');
        } else {
            $this->redirect('/users/loginRegister');
        }
    }

    // --- Session Helper ---
    private function createUserSession($user)
    {
        session_regenerate_id(true);

        // Set session variables
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_email'] = $user->email;
        $_SESSION['user_username'] = $user->username;
        $_SESSION['user_role'] = $user->role;
        $_SESSION['user_first_name'] = $user->first_name ?? null;
        $_SESSION['user_last_name'] = $user->last_name ?? null;
        $_SESSION['user_shop_name'] = $user->shop_name ?? null;
        $_SESSION['user_bio'] = $user->bio ?? '';
        $_SESSION['user_is_active'] = $user->is_active ?? 1;
        $_SESSION['user_profile_picture_path'] = $user->profile_picture_path ?? null;
    }

    // --------------------------------------------------------------------------
    // User functions
    // --------------------------------------------------------------------------

    // --- Login and Register ---
    public function loginRegister()
    {
        if (isset($_SESSION['user_id'])) {
            $this->redirect('users/dashboard');
            return;
        }

        // Default data structure for the view
        $data = [
            'title' => 'Login / Register',
            'cssFile' => 'login-register.css',

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

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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
                } elseif (strlen($data['register_username']) < 3) {
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
                } elseif (strlen($data['register_password']) < 6) {
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
                        return;
                    } else {
                        flash('register_error', 'Registration failed due to a server error. Please try again.', 'alert alert-danger');
                        $data['register_general_err'] = 'Registration failed due to a server error. Please try again.';
                    }
                }
            }

            // Process Login
            elseif (isset($_POST['login_submit'])) {
                $raw_identifier = $_POST['login_identifier'] ?? '';
                $raw_password = $_POST['login_password'] ?? '';

                $data['login_identifier'] = trim(filter_var($raw_identifier, FILTER_SANITIZE_EMAIL));
                $data['login_password'] = trim($raw_password);

                // Validation
                if (empty($data['login_identifier'])) {
                    $data['login_identifier_err'] = 'Please enter your email address.';
                } elseif (!filter_var($data['login_identifier'], FILTER_VALIDATE_EMAIL)) {
                    $data['login_identifier_err'] = 'Please enter a valid email format.';
                }
                if (empty($data['login_password'])) {
                    $data['login_password_err'] = 'Please enter password.';
                }
                if (empty($data['login_identifier_err']) && empty($data['login_password_err'])) {
                    $loggedInUser = $this->usersModel->login($data['login_identifier'], $data['login_password']);

                    if ($loggedInUser === 'inactive') {
                        $data['login_general_err'] = 'Your account is inactive. Please contact support.';
                        $data['login_password'] = '';
                    } elseif ($loggedInUser) {
                        $this->createUserSession($loggedInUser);
                        $this->redirect('users/dashboard');
                        return;
                    } else {
                        $data['login_general_err'] = 'Invalid credentials. Please check your email/username and password.';
                        $data['login_password'] = '';
                    }
                }
            }

            $this->view('/users/login-register', $data);
        } else {
            if (isset($_GET['registered']) && $_GET['registered'] === 'success') {
                $data['login_general_err'] = 'Registration successful! You can now log in.';
            }
            $this->view('/users/login-register', $data);
        }
    }

    // --- Logout ---
    public function logout()
    {
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
        session_destroy();
        $this->redirect('/users/loginRegister');
    }

    // --- Dashboard ---
    public function dashboard()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/users/loginRegister');
            return;
        }

        $data = [
            'title' => 'My Dashboard',
            'cssFile' => 'dashboard.css',
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

        $data['orders'] = [];
        $data['events'] = [];
        $data['arts'] = [];

        if ($data['role'] === 'artisan') {
            $data['my_events'] = $this->eventModel->getEventsByArtisanId($_SESSION['user_id']);
            $data['arts'] = $this->productModel->getActiveProductsByArtisanId($_SESSION['user_id']);
        }

        $this->view('users/dashboard', $data);
    }

    // --- Edit Profile ---
    public function editProfile()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('users/loginRegister');
            return;
        }

        $userId = $_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $newUsername = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
            $newEmail = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
            $newFirstName = trim(filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
            $newLastName = trim(filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
            $newBio = null;
            $newShopName = null;
            if ($_SESSION['user_role'] === 'artisan') {
                $newBio = trim(filter_input(INPUT_POST, 'bio', FILTER_SANITIZE_FULL_SPECIAL_CHARS)); // Adjust filter if needed
                $newShopName = trim(filter_input(INPUT_POST, 'shop_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
            }

            $currentUser = $this->usersModel->getUserById($userId);

            if (!$currentUser) {
                $this->redirect('users/dashboard');
                return;
            }
            $currentImagePath = $currentUser->profile_picture_path ?? null;

            $data = [
                'title' => 'Edit Profile',
                'user' => $currentUser,

                // Submitted values
                'username' => $newUsername,
                'email' => $newEmail,
                'first_name' => $newFirstName,
                'last_name' => $newLastName,
                'bio' => $newBio,
                'shop_name' => $newShopName,

                // Error messages
                'username_err' => '',
                'email_err' => '',
                'general_err' => ''
            ];

            // Image Upload
            $newImageFilename = null;
            $deleteOldImage = false;

            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['profile_picture']['tmp_name'];
                $fileName = basename($_FILES['profile_picture']['name']);
                $fileSize = $_FILES['profile_picture']['size'];
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $maxFileSize = 3 * 1024 * 1024; // 3MB limit

                if (!in_array($fileExtension, $allowedExtensions)) {
                    $data['image_err'] = 'Invalid file type (JPG, PNG, GIF, WEBP only).';
                } elseif ($fileSize > $maxFileSize) {
                    $data['image_err'] = 'Image size exceeds 3MB limit.';
                } else {
                    // Generate unique filename (prefix with user id for organization)
                    $newFileName = 'user_' . $userId . '_' . uniqid('', true) . '.' . $fileExtension;
                    $dest_path = PROFILE_IMG_UPLOAD_DIR . $newFileName;

                    if (move_uploaded_file($fileTmpPath, $dest_path)) {
                        $newImageFilename = $newFileName;
                        $deleteOldImage = true;
                    } else {
                        $data['image_err'] = 'Error saving uploaded image. Check permissions.';
                        error_log("Error moving profile picture to: " . $dest_path);
                    }
                }
            } elseif (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] != UPLOAD_ERR_NO_FILE && $_FILES['profile_picture']['error'] != UPLOAD_ERR_OK) {
                $data['image_err'] = 'File upload error code: ' . $_FILES['profile_picture']['error'];
            }

            // Validation
            // Username
            if (empty($newUsername)) {
                $data['username_err'] = 'Please enter username.';
            } elseif ($newUsername !== $currentUser->username && $this->usersModel->findUserByUsername($newUsername)) {
                $data['username_err'] = 'Username is already taken.';
            }

            // Email
            if (empty($newEmail)) {
                $data['email_err'] = 'Please enter email.';
            } elseif (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
                $data['email_err'] = 'Please enter a valid email format.';
            } elseif ($newEmail !== $currentUser->email && $this->usersModel->findUserByEmail($newEmail)) {
                $data['email_err'] = 'Email is already registered by another user.';
            }

            if (empty($data['username_err']) && empty($data['email_err'])) {

                $updateData = [
                    'id' => $userId,
                    'username' => $newUsername,
                    'email' => $newEmail,
                    'first_name' => $newFirstName,
                    'last_name' => $newLastName,
                ];
                if ($_SESSION['user_role'] === 'artisan') {
                    $updateData['bio'] = $newBio;
                    $updateData['shop_name'] = $newShopName;
                }
                if ($newImageFilename !== null) {
                    $updateData['profile_picture_path'] = $newImageFilename;
                }

                if ($this->usersModel->updateProfile($updateData)) {
                    $_SESSION['user_username'] = $newUsername;
                    $_SESSION['user_email'] = $newEmail;
                    $_SESSION['user_first_name'] = $newFirstName;
                    $_SESSION['user_last_name'] = $newLastName;
                    if ($newImageFilename !== null) {
                        $_SESSION['user_profile_picture_path'] = $newImageFilename;
                    }
                    if ($_SESSION['user_role'] === 'artisan') {
                        $_SESSION['user_shop_name'] = $newShopName;
                        $_SESSION['user_bio'] = $newBio;
                    }

                    if ($deleteOldImage && !empty($currentImagePath) && $currentImagePath !== $newImageFilename) {
                        $oldImagePathFull = PROFILE_IMG_UPLOAD_DIR . $currentImagePath;
                        if (file_exists($oldImagePathFull)) {
                            @unlink($oldImagePathFull);
                        }
                    }

                    $this->redirect('users/dashboard');
                    return;
                } else {
                    $data['general_err'] = 'Failed to update profile. Please try again.';
                    if ($newImageFilename !== null && file_exists(PROFILE_IMG_UPLOAD_DIR . $newImageFilename)) {
                        @unlink(PROFILE_IMG_UPLOAD_DIR . $newImageFilename);
                    }
                    $this->view('users/edit_profile', $data);
                }
            } else {
                if ($newImageFilename !== null && file_exists(PROFILE_IMG_UPLOAD_DIR . $newImageFilename)) {
                    @unlink(PROFILE_IMG_UPLOAD_DIR . $newImageFilename);
                }
                $this->view('users/edit_profile', $data);
            }
        } else {
            $user = $this->usersModel->getUserById($userId);

            if (!$user) {
                $this->redirect('users/dashboard');
                return;
            }

            $data = [
                'title' => 'Edit Profile',

                // Pre-fill form fields with current data
                'username' => $user->username,
                'email' => $user->email,
                'first_name' => $user->first_name ?? '',
                'last_name' => $user->last_name ?? '',
                'bio' => $user->bio ?? '',
                'shop_name' => $user->shop_name ?? '',
                'current_image_path' => $user->profile_picture_path ?? null,

                // Empty errors for initial load
                'username_err' => '',
                'email_err' => '',
                'first_name_err' => '',
                'last_name_err' => '',
                'shop_name_err' => '',
                'general_err' => ''
            ];

            $this->view('users/edit_profile', $data);
        }
    }

    // --- Change Password ---
    public function changePassword()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('users/loginRegister');
            return;
        }

        $userId = $_SESSION['user_id'];

        $data = [
            'title' => 'Change Password',
            'current_password_err' => '',
            'new_password_err' => '',
            'confirm_new_password_err' => '',
            'general_err' => '',
            'success_message' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $currentPassword = trim($_POST['current_password'] ?? '');
            $newPassword = trim($_POST['new_password'] ?? '');
            $confirmNewPassword = trim($_POST['confirm_new_password'] ?? '');

            // --- Validation ---
            if (empty($currentPassword)) {
                $data['current_password_err'] = 'Please enter your current password.';
            }
            if (empty($newPassword)) {
                $data['new_password_err'] = 'Please enter a new password.';
            } elseif (strlen($newPassword) < 6) {
                $data['new_password_err'] = 'New password must be at least 6 characters.';
            }
            if (empty($confirmNewPassword)) {
                $data['confirm_new_password_err'] = 'Please confirm your new password.';
            } elseif ($newPassword !== $confirmNewPassword) {
                $data['confirm_new_password_err'] = 'New passwords do not match.';
            }

            if (empty($data['current_password_err']) && empty($data['new_password_err']) && empty($data['confirm_new_password_err'])) {

                $updateResult = $this->usersModel->updatePasswordPlain($userId, $currentPassword, $newPassword);

                if ($updateResult === true) {
                    $data['success_message'] = 'Your password has been updated successfully!';
                    $this->view('users/change_password', $data);
                    return;
                } elseif ($updateResult === 'current_password_incorrect') {
                    $data['current_password_err'] = 'Your current password is incorrect.';
                } elseif ($updateResult === 'user_not_found') {
                    $data['general_err'] = 'Could not find user account.';
                } else {
                    $data['general_err'] = 'Could not update password due to a server error. Please try again.';
                }
            }
            $this->view('users/change_password', $data);
        } else {
            $this->view('users/change_password', $data);
        }
    }

    // --- Delete Account ---
    public function deleteAccount()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('users/loginRegister');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('users/dashboard');
            return;
        }
        $userIdToDelete = $_SESSION['user_id'];
        if ($this->usersModel->deleteById($userIdToDelete)) {
            $this->logout();
            return;
        } else {
            $this->redirect('users/dashboard');
            return;
        }
    }

    // ----------------------------------------------------------------------------
    // Admin functions
    // ----------------------------------------------------------------------------


    // --- Manage Users ---
    public function manageUsers($data)
    {
        // Check if logged in AND if role is admin
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/users/loginRegister');
            return;
        }
        if ($_SESSION['user_role'] !== 'admin') {
            flash('auth_error', 'You do not have permission to access that area.', 'alert alert-danger');
            $this->redirect('/users/dashboard');
            return;
        }
        $this->view('/admin/manage_users', data: $data);
    }
}
