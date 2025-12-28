<?php
/**
 * SQLite Database Setup
 * Run ONCE: http://localhost/home-service-platform/setup.php
 */

echo "<h1>🔧 Home Service Platform - SQLite Setup</h1>";
echo "<hr>";

// Create data directory if it doesn't exist
if (!file_exists('data')) {
    mkdir('data', 0777, true);
    echo "✅ Created 'data' directory<br>";
}

try {
    // Create/Connect to SQLite database
    $db = new PDO('sqlite:data/database.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Connected to SQLite database<br>";

    // ================================================
    // CREATE TABLES
    // ================================================

    // Service Categories Table
    $db->exec("CREATE TABLE IF NOT EXISTS service_categories (
        category_id INTEGER PRIMARY KEY AUTOINCREMENT,
        category_name TEXT NOT NULL,
        description TEXT,
        icon TEXT
    )");
    echo "✅ Created 'service_categories' table<br>";

    // Users Table
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        user_id INTEGER PRIMARY KEY AUTOINCREMENT,
        full_name TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL,
        phone TEXT,
        address TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    echo "✅ Created 'users' table<br>";

    // Providers Table
    $db->exec("CREATE TABLE IF NOT EXISTS providers (
        provider_id INTEGER PRIMARY KEY AUTOINCREMENT,
        full_name TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL,
        phone TEXT,
        service_category_id INTEGER,
        experience_years INTEGER DEFAULT 0,
        hourly_rate REAL,
        bio TEXT,
        profile_image TEXT,
        rating REAL DEFAULT 0.0,
        total_jobs INTEGER DEFAULT 0,
        is_verified INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (service_category_id) REFERENCES service_categories(category_id)
    )");
    echo "✅ Created 'providers' table<br>";

    // Bookings Table
    $db->exec("CREATE TABLE IF NOT EXISTS bookings (
        booking_id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        provider_id INTEGER NOT NULL,
        category_id INTEGER NOT NULL,
        booking_date TEXT NOT NULL,
        booking_time TEXT NOT NULL,
        address TEXT NOT NULL,
        description TEXT,
        status TEXT DEFAULT 'pending',
        total_price REAL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id),
        FOREIGN KEY (provider_id) REFERENCES providers(provider_id),
        FOREIGN KEY (category_id) REFERENCES service_categories(category_id)
    )");
    echo "✅ Created 'bookings' table<br>";

    // Reviews Table
    $db->exec("CREATE TABLE IF NOT EXISTS reviews (
        review_id INTEGER PRIMARY KEY AUTOINCREMENT,
        booking_id INTEGER NOT NULL,
        user_id INTEGER NOT NULL,
        provider_id INTEGER NOT NULL,
        rating INTEGER NOT NULL,
        comment TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (booking_id) REFERENCES bookings(booking_id),
        FOREIGN KEY (user_id) REFERENCES users(user_id),
        FOREIGN KEY (provider_id) REFERENCES providers(provider_id)
    )");
    echo "✅ Created 'reviews' table<br>";

    // ================================================
    // INSERT DEFAULT DATA
    // ================================================

    // Check if categories exist
    $stmt = $db->query("SELECT COUNT(*) FROM service_categories");
    $count = $stmt->fetchColumn();

    if ($count == 0) {
        $db->exec("INSERT INTO service_categories (category_name, description, icon) VALUES
            ('Plumbing', 'Pipe repairs, leak fixes, installations', 'fa-wrench'),
            ('Electrical', 'Wiring, repairs, installations', 'fa-bolt'),
            ('HVAC', 'Heating, ventilation, air conditioning', 'fa-snowflake'),
            ('Cleaning', 'Home and office cleaning services', 'fa-broom'),
            ('Painting', 'Interior and exterior painting', 'fa-paint-roller'),
            ('Carpentry', 'Furniture repair, woodwork', 'fa-hammer')
        ");
        echo "✅ Inserted default service categories<br>";
    }

    // Check if providers exist
    $stmt = $db->query("SELECT COUNT(*) FROM providers");
    $count = $stmt->fetchColumn();

    if ($count == 0) {
        $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);
        
        $stmt = $db->prepare("INSERT INTO providers (full_name, email, password, phone, service_category_id, experience_years, hourly_rate, bio, rating, total_jobs, is_verified) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $providers = [
            ['John Smith', 'john@example.com', $hashedPassword, '555-0101', 1, 8, 45.00, 'Expert plumber with 8 years of experience in residential and commercial plumbing.', 4.8, 156, 1],
            ['Sarah Johnson', 'sarah@example.com', $hashedPassword, '555-0102', 2, 6, 50.00, 'Licensed electrician specializing in home electrical systems and repairs.', 4.9, 203, 1],
            ['Mike Williams', 'mike@example.com', $hashedPassword, '555-0103', 3, 10, 55.00, 'HVAC specialist with expertise in all heating and cooling systems.', 4.7, 178, 1],
            ['Emily Brown', 'emily@example.com', $hashedPassword, '555-0104', 4, 4, 35.00, 'Professional cleaner providing top-quality home and office cleaning services.', 4.9, 245, 1],
            ['David Lee', 'david@example.com', $hashedPassword, '555-0105', 5, 12, 40.00, 'Professional painter with an eye for detail and quality finishes.', 4.6, 134, 1],
            ['Lisa Martinez', 'lisa@example.com', $hashedPassword, '555-0106', 6, 7, 48.00, 'Skilled carpenter specializing in furniture repair and custom woodwork.', 4.8, 167, 1]
        ];

        foreach ($providers as $provider) {
            $stmt->execute($provider);
        }
        echo "✅ Inserted sample providers<br>";
    }

    // Check if users exist
    $stmt = $db->query("SELECT COUNT(*) FROM users");
    $count = $stmt->fetchColumn();

    if ($count == 0) {
        $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (full_name, email, password, phone, address) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['Test User', 'user@example.com', $hashedPassword, '555-1234', '123 Main Street, City, Country']);
        echo "✅ Inserted sample user<br>";
    }

    echo "<hr>";
    echo "<h2>🎉 Database Setup Complete!</h2>";
    echo "<p><strong>Database file created at:</strong> data/database.db</p>";
    echo "<h3>Sample Login Credentials:</h3>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>Type</th><th>Email</th><th>Password</th></tr>";
    echo "<tr><td>User</td><td>user@example.com</td><td>password123</td></tr>";
    echo "<tr><td>Provider</td><td>john@example.com</td><td>password123</td></tr>";
    echo "</table>";
    echo "<br>";
    echo "<p><a href='index.html' style='font-size: 20px; padding: 10px 20px; background: #2563eb; color: white; text-decoration: none; border-radius: 5px;'>👉 Go to Homepage</a></p>";
    echo "<p style='color: red; margin-top: 20px;'><strong>⚠️ Delete this file (setup.php) after running for security!</strong></p>";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>