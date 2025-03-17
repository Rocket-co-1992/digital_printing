<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'digital_printing');

define('SITE_NAME', 'Digital Printing Management');
define('SITE_URL', 'http://localhost/digital_printing');
define('EMAIL_FROM', 'noreply@digitalprinting.com');

// Session configuration
ini_set('session.cookie_lifetime', 60 * 60 * 24); // 24 hours
ini_set('session.gc_maxlifetime', 60 * 60 * 24); // 24 hours
