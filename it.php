<?php
echo "<h2>Database and Tables Creation Process</h2>";

$dbhost = 'localhost';
$dbuser = 'root';
$dbpass = ''; 
$db = 'it';


$conn = mysqli_connect($dbhost, $dbuser, $dbpass);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}


$sql_db = "CREATE DATABASE IF NOT EXISTS $db";
if ($conn->query($sql_db) === TRUE) {
    echo "Database '$db' created or already exists.<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
}


mysqli_select_db($conn, $db);

$tables = [
    "ITstd" => "CREATE TABLE IF NOT EXISTS ITstd (
        sn INT(4) NOT NULL AUTO_INCREMENT,
        stdno VARCHAR(30) NOT NULL,
        stdname VARCHAR(128),
        phone INT(11),
        effectdate DATE,
        certiflag BOOL, 
        PRIMARY KEY (sn)
    )",

    "ITsem1" => "CREATE TABLE IF NOT EXISTS ITsem1 (
        stdno VARCHAR(30) NOT NULL,
        IT1012 VARCHAR(5), 
        IT1022 VARCHAR(5), 
        IT1032 VARCHAR(5), 
        IT1042 VARCHAR(5), 
        IT1062 VARCHAR(5), 
        IT1052 VARCHAR(5), 
        SGPA DECIMAL(3,2),
        PRIMARY KEY (stdno)
    )",

    "ITsem2" => "CREATE TABLE IF NOT EXISTS ITsem2 (
        stdno VARCHAR(30) NOT NULL,
        IT2012 VARCHAR(5), 
        IT2022 VARCHAR(5), 
        IT2032 VARCHAR(5), 
        IT2042 VARCHAR(5), 
        IT2052 VARCHAR(5), 
        IT2062 VARCHAR(5),
        IT2072 VARCHAR(5), 
        IT2082 VARCHAR(5),  
        SGPA DECIMAL(3,2),
        PRIMARY KEY (stdno)
    )",

    "ITsem3" => "CREATE TABLE IF NOT EXISTS ITsem3 (
        stdno VARCHAR(30) NOT NULL,
        IT3062 VARCHAR(5), 
        IT3042 VARCHAR(5), 
        IT3032 VARCHAR(5), 
        IT3012 VARCHAR(5), 
        IT3052 VARCHAR(5), 
        IT3022 VARCHAR(5),
        IT3072 VARCHAR(5),   
        SGPA DECIMAL(3,2),
        PRIMARY KEY (stdno)
    )",

    "ITsem4" => "CREATE TABLE IF NOT EXISTS ITsem4 (
        stdno VARCHAR(30) NOT NULL,
        IT4012 VARCHAR(5), 
        IT4022 VARCHAR(5), 
        IT4032 VARCHAR(5), 
        IT4042 VARCHAR(5), 
        IT4052 VARCHAR(5), 
        IT4212 VARCHAR(5),
        IT4232 VARCHAR(5), 
        IT4222 VARCHAR(5), 
        IT4242 VARCHAR(5), 
        SGPA DECIMAL(3,2),
        PRIMARY KEY (stdno)
    )"
];

foreach ($tables as $tableName => $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "Table '$tableName' created successfully.<br>";
    } else {
        echo "Error creating table '$tableName': " . $conn->error . "<br>";
    }
}

$conn->close();
?>