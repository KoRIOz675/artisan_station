<?php

function flash($name = '', $message = '', $class = 'alert alert-success') {
    if (!empty($name)) {
        // Check if setting or displaying the message
        if (!empty($message) && empty($_SESSION['flash'][$name])) {
            // Setting the message: Clear any old message with the same name first
            if (!empty($_SESSION['flash'][$name])) {
                unset($_SESSION['flash'][$name]);
            }
            if (!empty($_SESSION['flash'][$name . '_class'])) {
                unset($_SESSION['flash'][$name . '_class']);
            }

            // Store the new message and its class in the session
            $_SESSION['flash'][$name] = $message;
            $_SESSION['flash'][$name . '_class'] = $class;

        } elseif (empty($message) && !empty($_SESSION['flash'][$name])) {
            // Displaying the message: Get the class, echo the div, then unset
            $classOutput = !empty($_SESSION['flash'][$name . '_class']) ? $_SESSION['flash'][$name . '_class'] : 'alert alert-info'; // Default class
            echo '<div class="' . htmlspecialchars($classOutput) . '" id="msg-flash-' . htmlspecialchars($name) . '">' . htmlspecialchars($_SESSION['flash'][$name]) . '</div>';

            // Unset the message from the session after displaying
            unset($_SESSION['flash'][$name]);
            unset($_SESSION['flash'][$name . '_class']);
        }
    }
}
?>