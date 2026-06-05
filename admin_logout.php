<?php

declare(strict_types=1);

session_start();

require __DIR__ . '/admin_auth.php';

logoutAdmin();
header('Location: admin_login.php');
exit;
