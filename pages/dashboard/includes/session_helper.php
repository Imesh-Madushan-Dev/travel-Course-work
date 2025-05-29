<?php
// Session helper to safely start sessions
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?> 