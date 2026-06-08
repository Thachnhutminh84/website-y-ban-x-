<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Test Errors & Functions</h1>";

// Test database connection
echo "<h2>1. Database Connection</h2>";
try {
    require_once 'config.php';
    $conn = getDBConnection();
    if ($conn) {
        echo "✅ Database connection: OK<br>";
        $conn->close();
    } else {
        echo "❌ Database connection: FAILED<br>";
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

// Test contact helper functions
echo "<h2>2. Contact Helper Functions</h2>";
try {
    require_once 'contact-message-helper.php';
    
    $conn = getContactStorageConnection();
    if ($conn) {
        echo "✅ Contact storage connection: OK<br>";
        
        if (ensureContactsTableExists($conn)) {
            echo "✅ Contacts table: OK<br>";
        } else {
            echo "❌ Contacts table: FAILED<br>";
        }
        
        $stats = getContactStats();
        echo "✅ Contact stats: " . json_encode($stats) . "<br>";
        
        $conn->close();
    } else {
        echo "❌ Contact storage connection: FAILED<br>";
    }
} catch (Exception $e) {
    echo "❌ Contact helper error: " . $e->getMessage() . "<br>";
}

// Test auth functions
echo "<h2>3. Auth Functions</h2>";
try {
    require_once 'auth.php';
    
    echo "✅ Auth functions loaded<br>";
    echo "Current role: " . authCurrentRole() . "<br>";
    echo "Is logged in: " . (authIsLoggedIn() ? 'Yes' : 'No') . "<br>";
    echo "Is admin: " . (authIsAdmin() ? 'Yes' : 'No') . "<br>";
    
} catch (Exception $e) {
    echo "❌ Auth error: " . $e->getMessage() . "<br>";
}

// Test file existence
echo "<h2>4. File Existence</h2>";
$files = [
    'tin-nhan-lien-he.php',
    'dashboard.php', 
    'api-search.php',
    'api-contact-detail.php',
    'api-update-contact.php',
    'contact-management.js',
    'search-widget.js',
    'contact-messages-style.css',
    'dashboard-style.css'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ $file: EXISTS<br>";
    } else {
        echo "❌ $file: NOT FOUND<br>";
    }
}

echo "<h2>5. PHP Version & Extensions</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "MySQLi Extension: " . (extension_loaded('mysqli') ? '✅ Loaded' : '❌ Not loaded') . "<br>";
echo "JSON Extension: " . (extension_loaded('json') ? '✅ Loaded' : '❌ Not loaded') . "<br>";

echo "<hr>";
echo "<p><a href='test-new-features.php'>← Back to Test Features</a></p>";
?>