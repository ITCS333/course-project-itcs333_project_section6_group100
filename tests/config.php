<?php
// Start session once for the whole app
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// General site configuration
$SITE_NAME = "Course Management System";

// IMPORTANT: change this if your folder name is different
// e.g. if your folder is ITCS333 project, use "/ITCS333%20project"
$BASE_URL  = "/project333";
