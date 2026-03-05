<?php

require_once __DIR__ . '/../includes/auth.php';

if (is_admin_logged_in()) {
    admin_logout();
}

redirect('login.php');

