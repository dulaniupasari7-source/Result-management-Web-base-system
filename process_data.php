<?php
// process_data.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$dbhost = 'localhost'; 
$dbuser = 'root'; 
$dbpass = ''; 
$db = 'it';

$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $db);
if (!$conn) { die("Database Connection failed: " . mysqli_connect_error()); }

// SLIATE Official Handbook 5.6 & 5.5 Table Parsing
function getGradePointFromMarks($input) {
    $val = strtoupper(trim($input));

    // Elective නොවන හෝ හිස් කොටු
    if ($val === '-' || $val === '') {
        return ['gpa' => 0.00, 'count_credit' => false];
    }

    // Special Result Codes (NE, I(SE), DFR, I(CA), AB, INC, E)
    if (in_array($val, ['NE', 'I(SE)', 'DFR', 'I(CA)', 'AB', 'INC', 'E', 'F'])) {
        return ['gpa' => 0.00, 'count_credit' => true];
    }

    // සංඛ්‍යාත්මක ලකුණු පරාස (Marks to Grade Points)
    if (is_numeric($val)) {
        $m = floatval($val);
        
        // SLIATE Rule: ලකුණු 40ට අඩු නම් I(SE) වේ (Grade Point = 0.00)
        if ($m < 40) return ['gpa' => 0.00, 'count_credit' => true];
        
        if ($m >= 85 && $m <= 100) return ['gpa' => 4.00, 'count_credit' => true];
        elseif ($m >= 70) return ['gpa' => 4.00, 'count_credit' => true];
        elseif ($m >= 65) return ['gpa' => 3.70, 'count_credit' => true];
        elseif ($m >= 60) return ['gpa' => 3.30, 'count_credit' => true];
        elseif ($m >= 55) return ['gpa' => 3.00, 'count_credit' => true];
        elseif ($m >= 50) return ['gpa' => 2.70, 'count_credit' => true];
        elseif ($m >= 45) return ['gpa' => 2.30, 'count_credit' => true];
        elseif ($m >= 40) return ['gpa' => 2.00, 'count_credit' => true];
    }

    // Direct Letter Grades Input
    switch ($val) {
        case 'A+': case 'A': return ['gpa' => 4.00, 'count_credit' => true];
        case 'A-': return ['gpa' => 3.70, 'count_credit' => true];
        case 'B+': return ['gpa' => 3.30, 'count_credit' => true];
        case 'B':  return ['gpa' => 3.00, 'count_credit' => true];
        case 'B-': return ['gpa' => 2.70, 'count_credit' => true];
        case 'C+': return ['gpa' => 2.30, 'count_credit' => true];
        case 'C':  return ['gpa' => 2.00, 'count_credit' => true];
        default:   return ['gpa' => 0.00, 'count_credit' => true];
    }
}

// SLIATE 5.7 Formula: GPA = sum(credit * gpa) / sum(credit)
function calculateSliateSGPA($subjects_array) {
    $tot_points = 0.0;
    $tot_credits = 0.0;

    foreach ($subjects_array as $sub_code => $item) {
        $calc = getGradePointFromMarks($item['mark']);
        if ($calc['count_credit'] && $item['credit'] > 0) {
            $tot_points += ($item['credit'] * $calc['gpa']);
            $tot_credits += $item['credit'];
        }
    }

    if ($tot_credits > 0) {
        // දශමස්ථාන 2කට round කිරීම (SLIATE Handbook Rule)
        return number_format(round($tot_points / $tot_credits, 2), 2, '.', '');
    }
    return '0.00';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action == 'csv_import') {
        $target_table = isset($_POST['target_table']) ? $_POST['target_table'] : 'ITstd';
        $data_rows = [];

        // File කියවීම
        if (isset($_FILES['csv_file']['tmp_name']) && !empty($_FILES['csv_file']['tmp_name'])) {
            $file = fopen($_FILES['csv_file']['tmp_name'], "r");
            while (($data = fgetcsv($file, 1000, ",")) !== FALSE) {
                $data_rows[] = $data;
            }
            fclose($file);
        } 
        // Modal Grid කියවීම
        elseif (isset($_POST['grid_data_json']) && !empty($_POST['grid_data_json'])) {
            $grid_records = json_decode($_POST['grid_data_json'], true);
            if (is_array($grid_records)) {
                $data_rows = $grid_records;
            }
        }

        foreach ($data_rows as $row) {
            if (empty($row)) continue;

            $col0 = trim($row[0] ?? '');
            $col1 = trim($row[1] ?? '');

            // Serial Number (sn: 1, 2, 3...) මඟහැර Index No තෝරාගැනීම
            if (is_numeric($col0) && !empty($col1)) {
                $sno = mysqli_real_escape_string($conn, $col1);
                $d = array_slice($row, 1);
            } else {
                $sno = mysqli_real_escape_string($conn, $col0);
                $d = $row;
            }

            if ($sno === '' || strtoupper($sno) === 'INDEX NUMBER' || strtoupper($sno) === 'STDNO' || strtoupper($sno) === 'USERNAME') {
                continue;
            }

            // -------------------------------------------------------------
            // SEMESTER 1 (Credits: IT1012:4, IT1022:4, IT1032:3, IT1042:4, IT1062:2, IT1052:3) -> Total: 20
            // -------------------------------------------------------------
            if ($target_table === 'ITsem1') {
                $m = [
                    'IT1012' => trim($d[1] ?? ''),
                    'IT1022' => trim($d[2] ?? ''),
                    'IT1032' => trim($d[3] ?? ''),
                    'IT1042' => trim($d[4] ?? ''),
                    'IT1062' => trim($d[5] ?? ''),
                    'IT1052' => trim($d[6] ?? '')
                ];
                $subjects = [
                    'IT1012' => ['mark' => $m['IT1012'], 'credit' => 4],
                    'IT1022' => ['mark' => $m['IT1022'], 'credit' => 4],
                    'IT1032' => ['mark' => $m['IT1032'], 'credit' => 3],
                    'IT1042' => ['mark' => $m['IT1042'], 'credit' => 4],
                    'IT1062' => ['mark' => $m['IT1062'], 'credit' => 2], // Communication Skills (2 Credits)
                    'IT1052' => ['mark' => $m['IT1052'], 'credit' => 3], // ICT Project (3 Credits)
                ];
                $sgpa = calculateSliateSGPA($subjects);

                $conn->query("DELETE FROM itsem1 WHERE stdno='$sno'");
                $conn->query("INSERT INTO itsem1 (stdno, IT1012, IT1022, IT1032, IT1042, IT1062, IT1052, SGPA) 
                              VALUES ('$sno', '{$m['IT1012']}', '{$m['IT1022']}', '{$m['IT1032']}', '{$m['IT1042']}', '{$m['IT1062']}', '{$m['IT1052']}', '$sgpa')");
            }

            // -------------------------------------------------------------
            // SEMESTER 2 (Credits: 4, 3, 3, 3, 3, 2, 2 | IT2082 is Non-GPA Credit 0 -> Total: 20)
            // -------------------------------------------------------------
            elseif ($target_table === 'ITsem2') {
                $m = [
                    'IT2012' => trim($d[1] ?? ''),
                    'IT2022' => trim($d[2] ?? ''),
                    'IT2032' => trim($d[3] ?? ''),
                    'IT2042' => trim($d[4] ?? ''),
                    'IT2052' => trim($d[5] ?? ''),
                    'IT2062' => trim($d[6] ?? ''),
                    'IT2072' => trim($d[7] ?? ''),
                    'IT2082' => trim($d[8] ?? '')
                ];
                $subjects = [
                    'IT2012' => ['mark' => $m['IT2012'], 'credit' => 4],
                    'IT2022' => ['mark' => $m['IT2022'], 'credit' => 3],
                    'IT2032' => ['mark' => $m['IT2032'], 'credit' => 3],
                    'IT2042' => ['mark' => $m['IT2042'], 'credit' => 3],
                    'IT2052' => ['mark' => $m['IT2052'], 'credit' => 3],
                    'IT2062' => ['mark' => $m['IT2062'], 'credit' => 2],
                    'IT2072' => ['mark' => $m['IT2072'], 'credit' => 2],
                    'IT2082' => ['mark' => $m['IT2082'], 'credit' => 0], // Non-GPA Course
                ];
                $sgpa = calculateSliateSGPA($subjects);

                $conn->query("DELETE FROM itsem2 WHERE stdno='$sno'");
                $conn->query("INSERT INTO itsem2 (stdno, IT2012, IT2022, IT2032, IT2042, IT2052, IT2062, IT2072, IT2082, SGPA) 
                              VALUES ('$sno', '{$m['IT2012']}', '{$m['IT2022']}', '{$m['IT2032']}', '{$m['IT2042']}', '{$m['IT2052']}', '{$m['IT2062']}', '{$m['IT2072']}', '{$m['IT2082']}', '$sgpa')");
            }

            // -------------------------------------------------------------
            // SEMESTER 3 (Credits: 3062:2, 3042:3, 3032:2, 3012:4, 3052:2, 3022:4, 3072:3 -> Total: 20)
            // -------------------------------------------------------------
            elseif ($target_table === 'ITsem3') {
                $m = [
                    'IT3062' => trim($d[1] ?? ''),
                    'IT3042' => trim($d[2] ?? ''),
                    'IT3032' => trim($d[3] ?? ''),
                    'IT3012' => trim($d[4] ?? ''),
                    'IT3052' => trim($d[5] ?? ''),
                    'IT3022' => trim($d[6] ?? ''),
                    'IT3072' => trim($d[7] ?? '')
                ];
                $subjects = [
                    'IT3062' => ['mark' => $m['IT3062'], 'credit' => 2], // Info Security
                    'IT3042' => ['mark' => $m['IT3042'], 'credit' => 3], // DBMS
                    'IT3032' => ['mark' => $m['IT3032'], 'credit' => 2], // DSA
                    'IT3012' => ['mark' => $m['IT3012'], 'credit' => 4], // OOP
                    'IT3052' => ['mark' => $m['IT3052'], 'credit' => 2], // OS
                    'IT3022' => ['mark' => $m['IT3022'], 'credit' => 4], // Web Programming
                    'IT3072' => ['mark' => $m['IT3072'], 'credit' => 3], // Statistics
                ];
                $sgpa = calculateSliateSGPA($subjects);

                $conn->query("DELETE FROM itsem3 WHERE stdno='$sno'");
                $conn->query("INSERT INTO itsem3 (stdno, IT3062, IT3042, IT3032, IT3012, IT3052, IT3022, IT3072, SGPA) 
                              VALUES ('$sno', '{$m['IT3062']}', '{$m['IT3042']}', '{$m['IT3032']}', '{$m['IT3012']}', '{$m['IT3052']}', '{$m['IT3022']}', '{$m['IT3072']}', '$sgpa')");
            }

            // -------------------------------------------------------------
            // SEMESTER 4 (Compulsory: 14 Credits + 2 Electives: 6 Credits -> Total: 20)
            // -------------------------------------------------------------
            elseif ($target_table === 'ITsem4') {
                $m = [
                    'IT4012' => trim($d[1] ?? ''),
                    'IT4022' => trim($d[2] ?? ''),
                    'IT4032' => trim($d[3] ?? ''),
                    'IT4042' => trim($d[4] ?? ''),
                    'IT4052' => trim($d[5] ?? ''),
                    'IT4212' => (trim($d[6] ?? '') === '') ? '-' : trim($d[6]),
                    'IT4232' => (trim($d[7] ?? '') === '') ? '-' : trim($d[7]),
                    'IT4222' => (trim($d[8] ?? '') === '') ? '-' : trim($d[8]),
                    'IT4242' => (trim($d[9] ?? '') === '') ? '-' : trim($d[9]),
                ];
                $subjects = [
                    'IT4012' => ['mark' => $m['IT4012'], 'credit' => 3],
                    'IT4022' => ['mark' => $m['IT4022'], 'credit' => 3],
                    'IT4032' => ['mark' => $m['IT4032'], 'credit' => 3],
                    'IT4042' => ['mark' => $m['IT4042'], 'credit' => 3],
                    'IT4052' => ['mark' => $m['IT4052'], 'credit' => 2],
                    'IT4212' => ['mark' => $m['IT4212'], 'credit' => 3],
                    'IT4232' => ['mark' => $m['IT4232'], 'credit' => 3],
                    'IT4222' => ['mark' => $m['IT4222'], 'credit' => 3],
                    'IT4242' => ['mark' => $m['IT4242'], 'credit' => 3],
                ];
                $sgpa = calculateSliateSGPA($subjects);

                $conn->query("DELETE FROM itsem4 WHERE stdno='$sno'");
                $conn->query("INSERT INTO itsem4 (stdno, IT4012, IT4022, IT4032, IT4042, IT4052, IT4212, IT4232, IT4222, IT4242, SGPA) 
                              VALUES ('$sno', '{$m['IT4012']}', '{$m['IT4022']}', '{$m['IT4032']}', '{$m['IT4042']}', '{$m['IT4052']}', '{$m['IT4212']}', '{$m['IT4232']}', '{$m['IT4222']}', '{$m['IT4242']}', '$sgpa')");
            }

            // -------------------------------------------------------------
            // STUDENT TABLE (ITstd)
            // -------------------------------------------------------------
            elseif ($target_table === 'ITstd') {
                $sname = mysqli_real_escape_string($conn, trim($d[1] ?? ''));
                $p = preg_replace('/[^0-9]/', '', trim($d[2] ?? '0'));
                $raw_date = trim($d[3] ?? '');
                $cflag = isset($d[4]) && trim($d[4]) !== '' ? intval(trim($d[4])) : 1;
                $time = strtotime(str_replace('/', '-', $raw_date));
                $formatted_date = ($time !== false) ? date('Y-m-d', $time) : date('Y-m-d');

                $conn->query("DELETE FROM itstd WHERE stdno='$sno'");
                $conn->query("INSERT INTO itstd (stdno, stdname, phone, effectdate, certiflag) 
                              VALUES ('$sno', '$sname', '$p', '$formatted_date', '$cflag')");
            }

            // -------------------------------------------------------------
            // ADMIN TABLE
            // -------------------------------------------------------------
            elseif ($target_table === 'Admin') {
                $p = mysqli_real_escape_string($conn, trim($d[1] ?? ''));
                $raw_role = strtolower(trim($d[2] ?? 'user'));
                $r = ($raw_role === 'admin') ? 'admin' : 'user';
                $fn = isset($d[3]) ? mysqli_real_escape_string($conn, trim($d[3])) : '';

                $conn->query("DELETE FROM Admin WHERE Username='$sno'");
                $conn->query("INSERT INTO Admin (Username, password, role, Full_name) VALUES ('$sno', '$p', '$r', '$fn')");
            }
        }

        header("Location: dashboard.php?status=success"); 
        exit();
    }

    if ($action == 'add_admin') {
        $u = mysqli_real_escape_string($conn, trim($_POST['Username']));
        $p = mysqli_real_escape_string($conn, trim($_POST['password']));
        $raw_role = strtolower(trim($_POST['role'] ?? 'user'));
        $r = ($raw_role === 'admin') ? 'admin' : 'user';
        $fn = mysqli_real_escape_string($conn, trim($_POST['Full_name']));

        $conn->query("DELETE FROM Admin WHERE Username='$u'");
        $conn->query("INSERT INTO Admin (Username, password, role, Full_name) VALUES ('$u', '$p', '$r', '$fn')");

        header("Location: dashboard.php?status=success"); 
        exit();
    }
}
?>