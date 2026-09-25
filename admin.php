<?php
echo "<h2>Adding Structured Admin Table to IT Database...</h2>";

$dbhost = 'localhost';
$dbuser = 'root';
$dbpass = ''; 
$db = 'it';

// සම්බන්ධතාවය ඇති කිරීම
$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $db);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Admin වගුව සෑදීමේ SQL විධානය
$sql = "CREATE TABLE IF NOT EXISTS Admin (
    id INT NOT NULL AUTO_INCREMENT,
    Username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') NOT NULL, -- මෙයින් admin සහ user පමණක් සීමා කරයි
    Full_name VARCHAR(100) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE (Username)
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'Admin' created successfully.<br><br>";
    
    // පරීක්ෂා කිරීම සඳහා සාමාන්‍ය පරිශීලකයෙකු (User) සහ ප්‍රධාන පරිශීලකයෙකු (Admin) ඇතුළත් කිරීම
    $hashed_password = password_hash('password123', PASSWORD_DEFAULT);
    
    // 1. Admin ගිණුමක් ඇතුළත් කිරීම (ශිෂ්‍ය අංකයක් Username එක ලෙස යොදා)
    $check_admin = "SELECT * FROM Admin WHERE Username = 'TAN/IT/2324/F/208'";
    $result1 = $conn->query($check_admin);
    
    if ($result1->num_rows == 0) {
        $insert_admin = "INSERT INTO Admin (Username, password, role, Full_name) 
                         VALUES ('TAN/IT/2324/F/208', '$hashed_password', 'admin', 'Kamal Ramanayaka')";
        $conn->query($insert_admin);
        echo "Example Admin added (Username: TAN/IT/2324/F/208)<br>";
    }

    // 2. සාමාන්‍ය User ගිණුමක් ඇතුළත් කිරීම (නමක් Username එක ලෙස යොදා)
    $check_user = "SELECT * FROM Admin WHERE Username = 'kumara'";
    $result2 = $conn->query($check_user);
    
    if ($result2->num_rows == 0) {
        $insert_user = "INSERT INTO Admin (Username, password, role, Full_name) 
                        VALUES ('kumara', '$hashed_password', 'user', 'Kumara Perera')";
        $conn->query($insert_user);
        echo "Example User added (Username: kumara)<br>";
    }

} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?>