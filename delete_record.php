<?php
// delete_record.php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$dbhost = 'localhost'; 
$dbuser = 'root'; 
$dbpass = ''; 
$db = 'it';

$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $db);
if (!$conn) { 
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']); 
    exit(); 
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $table = isset($_POST['table']) ? mysqli_real_escape_string($conn, $_POST['table']) : '';
    $id = isset($_POST['id']) ? mysqli_real_escape_string($conn, trim($_POST['id'])) : '';

    if (!empty($table) && !empty($id)) {
        // Admin table සඳහා Username ද, අනෙක් tables සඳහා stdno ද Primary Key වේ
        $primary_key = ($table === 'Admin') ? 'Username' : 'stdno';

        $sql = "DELETE FROM `$table` WHERE `$primary_key` = '$id'";
        if (mysqli_query($conn, $sql)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid parameters']);
    }
    exit();
}
?>