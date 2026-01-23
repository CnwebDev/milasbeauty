<?php
require_once '../../config/db.php';
require_login();
session_destroy();
header("Location: login.php");
exit;
