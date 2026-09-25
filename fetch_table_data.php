<?php
// fetch_table_data.php
$dbhost = 'localhost'; 
$dbuser = 'root'; 
$dbpass = ''; 
$db = 'it';

$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $db);
if (!$conn) { die("Database Connection failed"); }

$table = isset($_GET['table']) ? mysqli_real_escape_string($conn, $_GET['table']) : 'ITstd';
$query = isset($_GET['query']) ? mysqli_real_escape_string($conn, trim($_GET['query'])) : '';

// ඩේටාබේස් එකේ ඇති ලකුණ Dashboard එකෙහි Display වන Grade එක බවට හැරවීම
function markToDisplayGrade($val) {
    $val = strtoupper(trim($val));

    if ($val === '-' || $val === '') return '-';
    if (in_array($val, ['NE', 'I(SE)', 'DFR', 'I(CA)', 'AB', 'INC'])) return $val;

    if (is_numeric($val)) {
        $mark = floatval($val);
        if ($mark < 40) return 'I(SE)';
        elseif ($mark >= 85) return 'A+';
        elseif ($mark >= 70) return 'A';
        elseif ($mark >= 65) return 'A-';
        elseif ($mark >= 60) return 'B+';
        elseif ($mark >= 55) return 'B';
        elseif ($mark >= 50) return 'B-';
        elseif ($mark >= 45) return 'C+';
        elseif ($mark >= 40) return 'C';
    }

    if (in_array($val, ['D+', 'D', 'E', 'C-'])) return 'I(SE)';
    return $val;
}

$subject_headers = [
    // Semester 1
    'IT1012' => 'HNDIT1012 - Information Systems',
    'IT1022' => 'HNDIT1022 - Computer Systems Architecture',
    'IT1032' => 'HNDIT1032 - Fundamentals of Programming',
    'IT1042' => 'HNDIT1042 - Data Communication and Networks',
    'IT1062' => 'HNDIT1062 - Mathematics for IT',
    'IT1052' => 'HNDIT1052 - Personal Computer Applications',
    
    // Semester 2
    'IT2012' => 'HNDIT2012 - Object Oriented Analysis & Design',
    'IT2022' => 'HNDIT2022 - Database Management Systems I',
    'IT2032' => 'HNDIT2032 - Visual Application Programming',
    'IT2042' => 'HNDIT2042 - Software Engineering',
    'IT2052' => 'HNDIT2052 - Web Development',
    'IT2062' => 'HNDIT2062 - Operating Systems',
    'IT2072' => 'HNDIT2072 - Technical Communication',
    'IT2082' => 'HNDIT2082 - Project',

    // Semester 3
    'IT3062' => 'HNDIT3062 - Information and Computer Security',
    'IT3042' => 'HNDIT3042 - Database Management Systems',
    'IT3032' => 'HNDIT3032 - Data Structures and Algorithms',
    'IT3012' => 'HNDIT3012 - Object Oriented Programming',
    'IT3052' => 'HNDIT3052 - Operating Systems',
    'IT3022' => 'HNDIT3022 - Web Programming',
    'IT3072' => 'HNDIT3072 - Statistics for IT',

    // Semester 4
    'IT4012' => 'HNDIT4012 - Software Testing & QA',
    'IT4022' => 'HNDIT4022 - Management Information Systems',
    'IT4032' => 'HNDIT4032 - Mobile Application Development',
    'IT4042' => 'HNDIT4042 - Network Administration',
    'IT4052' => 'HNDIT4052 - Professional Issues in IT',
    'IT4212' => 'HNDIT4212 - Enterprise Application Development',
    'IT4232' => 'HNDIT4232 - Cloud Computing',
    'IT4222' => 'HNDIT4222 - Data Science',
    'IT4242' => 'HNDIT4242 - Mini Project',

    // General
    'stdno' => 'INDEX NUMBER',
    'SGPA' => 'SGPA',
    'stdname' => 'STUDENT NAME',
    'phone' => 'PHONE',
    'effectdate' => 'EFFECTIVE DATE',
    'certiflag' => 'CERTIFICATE FLAG',
    'Username' => 'USERNAME',
    'role' => 'ROLE',
    'Full_name' => 'FULL NAME'
];

$sql = "SELECT * FROM `$table`";
if ($query !== '') {
    if ($table === 'Admin') {
        $sql .= " WHERE `Full_name` LIKE '%$query%' OR `Username` LIKE '%$query%'";
    } else {
        $sql .= " WHERE `stdno` LIKE '%$query%'";
    }
}
$sql .= " LIMIT 200";

$res = mysqli_query($conn, $sql);
if (!$res || mysqli_num_rows($res) === 0) {
    echo "<p style='padding:15px; color:#888;'>කිසිදු දත්ත වාර්තාවක් හමු නොවීය (No records found).</p>";
    exit();
}

echo "<table><thead><tr>";
// Search එකක් සිදු කළ විට පමණක් ඉදිරියෙන් Action Column එක පෙන්වයි
if ($query !== '') {
    echo "<th style='background-color:#D61C1C; text-align:center; width:90px;'>ACTION</th>";
}

$fields = mysqli_fetch_fields($res);
foreach ($fields as $field) {
    $colName = $field->name;
    $title = isset($subject_headers[$colName]) ? $subject_headers[$colName] : $colName;
    echo "<th>" . htmlspecialchars($title) . "</th>";
}
echo "</tr></thead><tbody>";

while ($row = mysqli_fetch_assoc($res)) {
    // Unique Identifier එක සොයා ගැනීම
    $id_val = ($table === 'Admin') ? ($row['Username'] ?? '') : ($row['stdno'] ?? '');

    echo "<tr>";
    
    // Search කළ විට ඉදිරියෙන්ම රතු පැහැති Delete බොත්තම දැමීම
    if ($query !== '') {
        echo "<td style='text-align:center;'>";
        echo "<button type='button' class='btn-row-delete' onclick=\"deleteSelectedRow(this, '" . htmlspecialchars($table, ENT_QUOTES) . "', '" . htmlspecialchars($id_val, ENT_QUOTES) . "')\">Delete</button>";
        echo "</td>";
    }

    foreach ($row as $colName => $val) {
        $displayVal = $val;

        if ($colName !== 'stdno' && $colName !== 'SGPA' && $colName !== 'stdname' && $table !== 'Admin' && $table !== 'ITstd' && $table !== 'itstd') {
            $displayVal = markToDisplayGrade($val);
        }

        echo "<td>" . htmlspecialchars($displayVal) . "</td>";
    }
    echo "</tr>";
}
echo "</tbody></table>";
?>