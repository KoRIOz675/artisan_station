<?php

class Event
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // Create a new event
    public function createEvent($data)
    {
        // Basic slug generation (you might want a more robust slug library later)
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['name']), '-'));
        // Ensure slug is unique (append number if needed - simple version)
        $originalSlug = $slug;
        $counter = 1;
        while ($this->findEventBySlug($slug)) {
            $slug = $originalSlug . '-' . $counter++;
        }

        $this->db->query('INSERT INTO events (artisan_id, name, slug, description, start_datetime, end_datetime, location, image_path, is_active)
                          VALUES (:artisan_id, :name, :slug, :description, :start_datetime, :end_datetime, :location, :image_path, :is_active)');

        // Bind values
        $this->db->bind(':artisan_id', $data['artisan_id']);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':slug', $slug); // Use generated slug
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':start_datetime', $data['start_datetime']);
        $this->db->bind(':end_datetime', $data['end_datetime'] ?: null); // Handle optional end date
        $this->db->bind(':location', $data['location'] ?: null);
        $this->db->bind(':image_path', $data['image_path'] ?: null); // Handle optional image path
        $this->db->bind(':is_active', $data['is_active'] ?? 1); // Default to active

        // Execute
        return $this->db->execute();
    }

    // Find event by slug (helper for createEvent)
    public function findEventBySlug($slug)
    {
        $this->db->query('SELECT id FROM events WHERE slug = :slug LIMIT 1');
        $this->db->bind(':slug', $slug);
        $this->db->execute();
        return $this->db->rowCount() > 0;
    }

    // Get all events created by a specific artisan
    public function getEventsByArtisanId($artisanId)
    {
        $this->db->query('SELECT id, name, slug, start_datetime, location, is_active
                          FROM events
                          WHERE artisan_id = :artisan_id
                          ORDER BY start_datetime DESC');
        $this->db->bind(':artisan_id', $artisanId);
        return $this->db->resultSet(); // Returns array of event objects
    }

    public function getUpcomingActiveEvents($limit = null)
    {
        $sql = 'SELECT id, name, slug, description, start_datetime, image_path
                FROM events
                WHERE start_datetime >= NOW() AND is_active = 1
                ORDER BY start_datetime ASC';

        // Add LIMIT clause if provided
        if ($limit !== null && is_int($limit) && $limit > 0) {
            $sql .= ' LIMIT :limit';
        }

        $this->db->query($sql);

        // Bind limit if necessary
        if ($limit !== null && is_int($limit) && $limit > 0) {
            $this->db->bind(':limit', $limit, PDO::PARAM_INT); // PDO::PARAM_INT might require `use PDO;` at top or ::PARAM_INT
        }

        return $this->db->resultSet(); // Returns array of event objects
    }

    public function getTotalEventCount()
    {
        try {
            $this->db->query('SELECT COUNT(*) as count FROM events');
            $row = $this->db->single();
            return ($row && isset($row->count)) ? $row->count : 0;
        } catch (Exception $e) {
            error_log("Error getting event count: " . $e->getMessage());
            return 'Error';
        }
    }

    public function getAllEvents($orderBy = 'start_datetime', $orderDir = 'DESC')
    {
        $allowedOrderBy = ['e.id', 'e.name', 'e.start_datetime', 'e.is_active', 'u.username'];
        $allowedOrderDir = ['ASC', 'DESC'];
        $orderBy = in_array($orderBy, $allowedOrderBy) ? $orderBy : 'e.start_datetime';
        $orderDir = in_array(strtoupper($orderDir), $allowedOrderDir) ? strtoupper($orderDir) : 'DESC';

        $this->db->query("SELECT e.id, e.name, e.slug, e.start_datetime, e.location, e.is_active, u.username as artisan_username
                          FROM events e
                          LEFT JOIN users u ON e.artisan_id = u.id
                          ORDER BY {$orderBy} {$orderDir}");
        return $this->db->resultSet();
    }

    public function updateEventActiveStatus($eventId, $isActive)
    {
        $this->db->query('UPDATE events SET is_active = :is_active WHERE id = :id');
        $this->db->bind(':is_active', $isActive ? 1 : 0, PDO::PARAM_INT); // Ensure 0 or 1
        $this->db->bind(':id', $eventId, PDO::PARAM_INT);
        return $this->db->execute();
    }

    public function getActiveEvents($upcomingOnly = true)
    {
        $sql = "SELECT e.id, e.name, e.slug, e.description, e.start_datetime, e.location, e.image_path,
                       u.username as artisan_username, u.shop_name as artisan_shop_name
                FROM events e
                LEFT JOIN users u ON e.artisan_id = u.id
                WHERE e.is_active = 1";

        if ($upcomingOnly) {
            $sql .= " AND e.start_datetime >= NOW()";
        }

        $sql .= " ORDER BY e.start_datetime ASC"; // Show soonest first

        try {
            $this->db->query($sql);
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("Error fetching active events: " . $e->getMessage());
            return [];
        }
    }

    public function getActiveEventBySlugWithArtisan($slug)
    {
        try {
            $this->db->query("SELECT
                                e.*,  -- Select all from events table
                                u.username as artisan_username,
                                u.shop_name as artisan_shop_name,
                                u.profile_picture_path as artisan_image_path
                              FROM events e
                              LEFT JOIN users u ON e.artisan_id = u.id
                              WHERE e.slug = :slug AND e.is_active = 1
                              LIMIT 1");

            $this->db->bind(':slug', $slug);
            $event = $this->db->single();

            return $event; // Returns object or false

        } catch (Exception $e) {
            error_log("Error fetching event by slug ($slug): " . $e->getMessage());
            return false;
        }
    }

    public function isUserAttending($userId, $eventId)
    {
        try {
            $this->db->query('SELECT user_id FROM event_attendees WHERE user_id = :user_id AND event_id = :event_id LIMIT 1');
            $this->db->bind(':user_id', $userId, PDO::PARAM_INT);
            $this->db->bind(':event_id', $eventId, PDO::PARAM_INT);
            $this->db->execute();
            return $this->db->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Error checking event attendance (User: $userId, Event: $eventId): " . $e->getMessage());
            return false; // Assume not attending on error
        }
    }

    public function attendEvent($userId, $eventId)
    {
        // Double check they aren't already attending to prevent errors on primary key conflict
        if ($this->isUserAttending($userId, $eventId)) {
            return true; /* Already attending */
        }

        try {
            $this->db->query('INSERT INTO event_attendees (user_id, event_id) VALUES (:user_id, :event_id)');
            $this->db->bind(':user_id', $userId, PDO::PARAM_INT);
            $this->db->bind(':event_id', $eventId, PDO::PARAM_INT);
            return $this->db->execute();
        } catch (Exception $e) {
            // Catch potential duplicate key error if primary key check fails or isn't done
            error_log("Error adding event attendance (User: $userId, Event: $eventId): " . $e->getMessage());
            return false;
        }
    }

    public function unattendEvent($userId, $eventId)
    {
        try {
            $this->db->query('DELETE FROM event_attendees WHERE user_id = :user_id AND event_id = :event_id');
            $this->db->bind(':user_id', $userId, PDO::PARAM_INT);
            $this->db->bind(':event_id', $eventId, PDO::PARAM_INT);
            return $this->db->execute();
        } catch (Exception $e) {
            error_log("Error removing event attendance (User: $userId, Event: $eventId): " . $e->getMessage());
            return false;
        }
    }

    public function getEventById($id)
    {
        try {
            // Select at least id and slug, maybe name for debugging
            $this->db->query('SELECT id, name, slug FROM events WHERE id = :id LIMIT 1');
            $this->db->bind(':id', $id, PDO::PARAM_INT);
            $event = $this->db->single();
            return $event; // Returns object or false
        } catch (Exception $e) {
            error_log("Error fetching event by ID ($id): " . $e->getMessage());
            return false;
        }
    }

    public function getEventByIdForEdit($id)
    {
        $this->db->query('SELECT * FROM events WHERE id = :id');
        $this->db->bind(':id', $id, PDO::PARAM_INT);
        return $this->db->single();
    }

    // Update an existing event
    public function updateEvent($data)
    {
        // Regenerate slug if name changes, ensuring uniqueness excluding current ID
        $currentEvent = $this->getEventById($data['id']); // Use existing getEventById
        $slug = $currentEvent->slug; // Default to original slug
        if ($currentEvent && $currentEvent->name !== $data['name']) {
            $newSlugBase = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['name']), '-'));
            $slug = $newSlugBase;
            $counter = 1;
            while ($this->findEventBySlugExcludingId($slug, $data['id'])) { // New helper method
                $slug = $newSlugBase . '-' . $counter++;
            }
        }

        $sql = 'UPDATE events SET
                    name = :name,
                    slug = :slug,
                    description = :description,
                    start_datetime = :start_datetime,
                    end_datetime = :end_datetime,
                    location = :location,
                    is_active = :is_active';

        // Only include image_path in update if a new one is provided
        if (isset($data['image_path'])) {
            $sql .= ', image_path = :image_path';
        }

        $sql .= ' WHERE id = :id AND artisan_id = :artisan_id'; // Crucial ownership check

        try {
            $this->db->query($sql);
            $this->db->bind(':name', $data['name']);
            $this->db->bind(':slug', $slug);
            $this->db->bind(':description', $data['description']);
            $this->db->bind(':start_datetime', $data['start_datetime']);
            $this->db->bind(':end_datetime', $data['end_datetime']); // Can be null
            $this->db->bind(':location', $data['location']);       // Can be null
            $this->db->bind(':is_active', $data['is_active'], PDO::PARAM_INT);
            $this->db->bind(':id', $data['id'], PDO::PARAM_INT);
            $this->db->bind(':artisan_id', $data['artisan_id'], PDO::PARAM_INT);

            if (isset($data['image_path'])) {
                $this->db->bind(':image_path', $data['image_path']); // Can be null if removing
            }
            return $this->db->execute();
        } catch (Exception $e) {
            error_log("Error updating event ID ({$data['id']}): " . $e->getMessage());
            error_log("Event Update Data: " . print_r($data, true));
            return false;
        }
    }

    // Helper to find event by slug, excluding a specific ID (for updates)
    public function findEventBySlugExcludingId($slug, $excludeId)
    {
        $this->db->query('SELECT id FROM events WHERE slug = :slug AND id != :exclude_id LIMIT 1');
        $this->db->bind(':slug', $slug);
        $this->db->bind(':exclude_id', $excludeId, PDO::PARAM_INT);
        $this->db->execute();
        return $this->db->rowCount() > 0;
    }

    // Delete an event (with ownership check)
    public function deleteEvent($id, $artisanId)
    {
        try {
            // Optional: Delete from event_attendees first if foreign key isn't set to CASCADE
            // $this->db->query('DELETE FROM event_attendees WHERE event_id = :event_id');
            // $this->db->bind(':event_id', $id, PDO::PARAM_INT);
            // $this->db->execute();

            $this->db->query('DELETE FROM events WHERE id = :id AND artisan_id = :artisan_id');
            $this->db->bind(':id', $id, PDO::PARAM_INT);
            $this->db->bind(':artisan_id', $artisanId, PDO::PARAM_INT);
            return $this->db->execute();
        } catch (Exception $e) {
            error_log("Error deleting event ID ($id) for artisan ($artisanId): " . $e->getMessage());
            return false;
        }
    }
}
