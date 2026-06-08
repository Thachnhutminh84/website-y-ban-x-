<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>DEBUG THEM TIN</h2>";
echo "<pre>";

echo "PHP Version: " . phpversion() . "\n\n";

session_start();
echo "Session data:\n";
print_r($_SESSION);

echo "\n\nGET data:\n";
print_r($_GET);

echo "\n\nTrying to include them-tin.php...\n";
include 'them-tin.php';

echo "\n\nDone!";
echo "</pre>";
?>
