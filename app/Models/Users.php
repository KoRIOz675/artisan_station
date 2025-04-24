<?php

class Users
{
    private $db;

    public function __construct()
    {
        // Get the singleton Database instance
        $this->db = Database::getInstance();
    }

    // Find user by email (Check if email exists)
    public function findUserByEmail($email)
    {
        $this->db->query('SELECT id FROM users WHERE email = :email LIMIT 1');
        $this->db->bind(':email', $email);
        // Execute happens implicitly in rowCount or single/resultSet in this Database class version
        // For clarity, you could call execute() then check rowCount()
        $this->db->execute();
        return ($this->db->rowCount() > 0);
    }

    // Find user by username (Check if username exists)
    public function findUserByUsername($username)
    {
        $this->db->query('SELECT id FROM users WHERE username = :username LIMIT 1');
        $this->db->bind(':username', $username);
        $this->db->execute();
        return ($this->db->rowCount() > 0);
    }


    // Get user data by ID (excluding password hash)
    public function getUserById($id)
    {
        // Select fields consistent with the schema
        $this->db->query('SELECT id, email, username, first_name, last_name, role, shop_name, bio, profile_picture_path, is_active, created_at FROM users WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single(); // Returns single user object or false
    }

    // Register user
    public function register($data)
    {
        // Hash the password
        // $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        // Use correct column names from schema
        $this->db->query('INSERT INTO users (email, username, password_hash, first_name, last_name, role, shop_name, bio)
                          VALUES (:email, :username, :password_hash, :first_name, :last_name, :role, :shop_name, :bio)');

        // Bind values (make sure keys exist in $data or use null coalesce ??)
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':username', $data['username']);
        $this->db->bind(':password_hash', $data['password']);
        $this->db->bind(':first_name', $data['first_name'] ?? null);
        $this->db->bind(':last_name', $data['last_name'] ?? null);
        $this->db->bind(':role', $data['role'] ?? 'customer'); // Default role if not provided
        $this->db->bind(':shop_name', $data['shop_name'] ?? null);
        $this->db->bind(':bio', $data['bio'] ?? null);

        // Execute and return success/failure
        return $this->db->execute();
    }

    // Login User - Compares hashed password
    // Inside app/Models/User.php
    public function login($emailOrUsername, $password)
    {
        echo "<script>console.log('Login method called');</script>";
        echo "<script>console.log('Email or Username: " . htmlspecialchars($emailOrUsername) . "');</script>";
        echo "<script>console.log('Password: " . htmlspecialchars($password) . "');</script>";

        // $password here is the one from the form
        $this->db->query('SELECT id, email, username, password_hash, role, is_active, first_name, last_name, shop_name, bio FROM users WHERE email = :email LIMIT 1');
        $this->db->bind(':email', $emailOrUsername);
        $user = $this->db->single(); // Fetches the user object, including $user->password_hash

        if ($user === false) {
            $dbError = $this->db->getError(); // Get error from Database class
            if ($dbError) {
                error_log("Database error after trying to fetch user '" . $emailOrUsername . "': " . $dbError);
                echo "<script>console.error('Database Error (check PHP error log): " . htmlspecialchars($dbError) . "');</script>";
            } else {
                // This means single() returned false, but getError() returned nothing.
                // This strongly implies 0 rows were found by the query.
                error_log("User identifier not found in DB (0 rows returned) for: " . $emailOrUsername);
                echo "<script>console.warn('Query returned 0 rows for identifier: " . htmlspecialchars($emailOrUsername) . "');</script>";
            }
        }

        echo "<script>console.log('User fetched: " . json_encode($user) . "');</script>";

        if ($user) {
            // Is $password exactly what was submitted (after trim)?
            // Is $user->password_hash exactly what's in the DB?
            // if (password_verify($password, $user->password_hash)) { // The comparison happens here
            if ($password === $user->password_hash) {
                echo "<script>console.log('Password verified successfully');</script>";
                if ($user->is_active == 1) { // Check if user is active
                    return $user; // Successful login
                } else {
                    error_log("User is not active: " . $emailOrUsername); // Add logging if not present
                    return false; // This path leads to "Account not activated"
                }
            } else {
                error_log("Password verification failed for identifier: " . $emailOrUsername); // Add logging if not present
                return false; // This path leads to "Invalid credentials"
            }
        } else {
            error_log("Login identifier not found: " . $emailOrUsername); // Add logging if not present
            return false; // This path leads to "Invalid credentials"
        }
    }

    // Get ALL Artisans (active ones)
    public function getArtisans()
    {
        // Select necessary fields, filter by role=artisan and active
        $this->db->query("SELECT id, email, username, first_name, last_name, shop_name, bio, profile_picture_path
                           FROM users
                           WHERE role = 'artisan' AND is_active = 1
                           ORDER BY shop_name ASC");
        return $this->db->resultSet(); // Returns array of artisan objects
    }

    // Get Featured Artisans (active ones)
    public function getFeaturedArtisans()
    {
        // Use correct column name: is_featured_artisan, filter by active
        $this->db->query("SELECT id, email, username, first_name, last_name, shop_name, bio, profile_picture_path
                          FROM users
                          WHERE role = 'artisan' AND is_active = 1 AND is_featured_artisan = 1
                          ORDER BY shop_name ASC"); // Or order by something else
        return $this->db->resultSet(); // Returns array of featured artisan objects
    }

    // Update user profile information
    public function updateProfile($data)
    {
        // Base query
        $sql = 'UPDATE users SET username = :username, email = :email, first_name = :first_name, last_name = :last_name';

        // Dynamically add artisan fields if they exist in the data array
        if (isset($data['bio'])) {
            $sql .= ', bio = :bio';
        }
        if (isset($data['shop_name'])) {
            $sql .= ', shop_name = :shop_name';
        }
        if (isset($data['profile_picture_path'])) { // Check if profile_picture_path key exists
            $sql .= ', profile_picture_path = :profile_picture_path';
        }

        // Add the WHERE clause - VERY IMPORTANT
        $sql .= ' WHERE id = :id';

        // Prepare the query
        $this->db->query($sql);

        // Bind common values
        $this->db->bind(':username', $data['username']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':first_name', $data['first_name']);
        $this->db->bind(':last_name', $data['last_name']);
        $this->db->bind(':id', $data['id']); // Bind the user ID

        // Bind artisan values if they exist
        if (isset($data['bio'])) {
            $this->db->bind(':bio', $data['bio']);
        }
        if (isset($data['shop_name'])) {
            $this->db->bind(':shop_name', $data['shop_name']);
        }
        if (isset($data['profile_picture_path'])) {
            // Bind the new filename or null if removing
            $this->db->bind(':profile_picture_path', $data['profile_picture_path']);
        }

        // Execute and return success/failure
        if ($this->db->execute()) {
            return true;
        } else {
            // Optional: Log $this->db->getError() here
            error_log("Database error during profile update for user ID " . $data['id'] . ": " . $this->db->getError());
            return false;
        }
    }

    public function changePassword($userId, $currentPassword, $newPassword)
    {
        $this->db->query('SELECT password_hash FROM users WHERE id = :id');
        $this->db->bind(':id', $userId);
        $row = $this->db->single();

        if (!$row) {
            error_log("Attempt to update password for non-existent user ID: " . $userId);
            return 'user_not_found'; // Indicate user doesn't exist
        }

        // 2. Compare the submitted current password with the stored plain text password
        // Direct string comparison (insecure)
        if ($currentPassword !== $row->password_hash) {
            error_log("Incorrect current PLAIN TEXT password provided for user ID: " . $userId);
            return 'current_password_incorrect'; // Indicate current password mismatch
        }

        // 3. Update the database with the new plain text password
        $this->db->query('UPDATE users SET password_hash = :new_password WHERE id = :id');
        // Bind the NEW PLAIN TEXT password to the 'password_hash' column
        $this->db->bind(':new_password', $newPassword);
        $this->db->bind(':id', $userId);

        // Execute and return success/failure
        if ($this->db->execute()) {
            return true; // Success
        } else {
            error_log("Database error during PLAIN TEXT password update for user ID " . $userId . ": " . $this->db->getError());
            return 'db_error'; // Indicate database error
        }
    }

    public function deleteById($id)
    {

        $this->db->query('DELETE FROM users WHERE id = :id');
        $this->db->bind(':id', $id);

        if ($this->db->execute()) {
            return true;
        } else {
            error_log("Database error during user deletion for ID " . $id . ": " . $this->db->getError());
            return false;
        }
    }

    public function getTotalUserCount()
    {
        try {
            $this->db->query('SELECT COUNT(*) as count FROM users');
            $row = $this->db->single();
            // Check if row and count property exist
            return ($row && isset($row->count)) ? $row->count : 0;
        } catch (Exception $e) {
            error_log("Error getting user count: " . $e->getMessage());
            return 'Error'; // Return error indicator
        }
    }

    public function getAllUsersWithDetails($orderBy = 'username', $orderDir = 'ASC')
    {
        // Basic validation for order columns/directions
        $allowedOrderBy = ['id', 'username', 'email', 'role', 'is_active', 'created_at'];
        $allowedOrderDir = ['ASC', 'DESC'];
        $orderBy = in_array($orderBy, $allowedOrderBy) ? $orderBy : 'username';
        $orderDir = in_array(strtoupper($orderDir), $allowedOrderDir) ? strtoupper($orderDir) : 'ASC';

        // Fetch data (add pagination later if needed)
        $this->db->query("SELECT id, username, email, role, is_active, created_at
                          FROM users
                          ORDER BY {$orderBy} {$orderDir}");
        return $this->db->resultSet();
    }

    public function updateUserActiveStatus($userId, $isActive)
    {
        $this->db->query('UPDATE users SET is_active = :is_active WHERE id = :id');
        $this->db->bind(':is_active', $isActive ? 1 : 0, PDO::PARAM_INT); // Ensure 0 or 1
        $this->db->bind(':id', $userId, PDO::PARAM_INT);
        return $this->db->execute();
    }
    public function getAllArtisansForFeatured()
    {
        $this->db->query("SELECT id, username, shop_name, is_featured_artisan, is_active
                          FROM users
                          WHERE role = 'artisan'
                          ORDER BY username ASC");
        return $this->db->resultSet();
    }

    public function updateArtisanFeaturedStatus($artisanId, $status)
    {
        $this->db->query('UPDATE users SET is_featured_artisan = :status WHERE id = :id AND role = \'artisan\'');
        $this->db->bind(':status', $status ? 1 : 0, PDO::PARAM_INT); // Ensure 0 or 1
        $this->db->bind(':id', $artisanId, PDO::PARAM_INT);
        return $this->db->execute();
    }

    public function getDailyRegistrationsCount($days = 7)
    {
        if (!is_int($days) || $days < 1) {
            $days = 7; // Default safeguard
        }

        $this->db->query("SELECT DATE(created_at) as registration_date, COUNT(*) as count
                          FROM users
                          WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
                          GROUP BY DATE(created_at)
                          ORDER BY registration_date ASC");

        $this->db->bind(':days', $days - 1, PDO::PARAM_INT);

        return $this->db->resultSet();
    }

    public function updateUserRole($userId, $newRole)
    {
        // Optional: Validate $newRole here if needed, though controller should do it too
        $allowedRoles = ['customer', 'artisan', 'admin'];
        if (!in_array($newRole, $allowedRoles)) {
            error_log("Invalid role provided to updateUserRole: " . $newRole);
            return false; // Prevent invalid role update
        }

        $this->db->query('UPDATE users SET role = :role WHERE id = :id');
        $this->db->bind(':role', $newRole);
        $this->db->bind(':id', $userId, PDO::PARAM_INT);

        // Execute and return success/failure
        if ($this->db->execute()) {
            return true;
        } else {
            error_log("Database error updating role for user ID " . $userId . ": " . $this->db->getError());
            return false;
        }
    }

    public function getActiveArtisanByUsername($username)
    {
        try {
            // Select public-facing profile information
            $this->db->query("SELECT
                                id, username, first_name, last_name, shop_name, bio, profile_picture_path, created_at
                              FROM users
                              WHERE username = :username
                                AND role = 'artisan'
                                AND is_active = 1
                              LIMIT 1");

            $this->db->bind(':username', $username);
            $artisan = $this->db->single(); // Returns object or false

            return $artisan;
        } catch (Exception $e) {
            error_log("Error fetching artisan by username ($username): " . $e->getMessage());
            return false;
        }
    }

    public function getAttendedEventsByUserId($userId)
    {
        try {
            // Select event details by joining events and event_attendees
            $this->db->query("SELECT
                                e.id, e.name, e.slug, e.description, e.start_datetime, e.location, e.image_path
                              FROM events e
                              JOIN event_attendees ea ON e.id = ea.event_id
                              WHERE ea.user_id = :user_id
                              -- Optional: Only show active events they're attending? AND e.is_active = 1
                              ORDER BY e.start_datetime ASC"); // Order by date

            $this->db->bind(':user_id', $userId, PDO::PARAM_INT);
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("Error fetching attended events for User ID ($userId): " . $e->getMessage());
            return [];
        }
    }
}
