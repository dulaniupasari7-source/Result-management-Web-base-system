<?php
$dbhost = 'localhost';
$dbuser = 'root';
$dbpass = ''; 
$db = 'it';

$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $db);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

echo "<h2>Inserting HNDIT Student Records...</h2>";


$sql0 = "INSERT INTO ITstd (stdno, stdname, phone, effectdate, certiflag) VALUES 
('TAN/IT/2021/F/001', 'Aruni Jayawardena', 0771112223, '2021-01-10', 1),
('TAN/IT/2022/F/045', 'Sahan Perera', 0713334445, '2022-05-15', 1),
('TAN/IT/2324/F/209', 'Nuwan Gamage', 0755556667, '2023-09-20', 0),
('TAN/IT/2021/F/012', 'Dilini Silva', 0727778889, '2021-02-12', 1),
('TAN/IT/2223/F/040', 'Pathum Nissanka', 0789990001, '2023-01-05', 0)";

if ($conn->query($sql0) === TRUE) { echo "1. ITstd: Rows added successfully.<br>"; }


$sql1 = "INSERT INTO ITsem1 (stdno, IT1012, IT1022, IT1032, IT1042, IT1062, IT1052, SGPA) VALUES 
('TAN/IT/2021/F/001', 'A', 'B+', 'A-', 'A', 'B', 'B+', 3.55),
('TAN/IT/2022/F/045', 'B', 'C', 'B-', 'I(SE)', 'C+', 'B', 2.10),
('TAN/IT/2324/F/209', 'A-', 'A', 'A', 'B+', 'A-', 'A', 3.80),
('TAN/IT/2021/F/012', 'C', 'C+', 'B', 'B', 'B-', 'C', 2.45),
('TAN/IT/2223/F/040', 'I(CA)', 'B', 'C', 'A-', 'B+', 'B', 2.00)";

if ($conn->query($sql1) === TRUE) { echo "2. ITsem1: Rows added successfully.<br>"; }


$sql2 = "INSERT INTO ITsem2 (stdno, IT2012, IT2022, IT2032, IT2042, IT2052, IT2062, IT2072, IT2082, SGPA) VALUES 
('TAN/IT/2021/F/001', 'A-', 'B+', 'A', 'B', 'A', 'B+', 'A-', 'B', 3.40),
('TAN/IT/2022/F/045', 'C+', 'C', 'B', 'C-', 'B', 'I(SE)', 'C', 'C+', 1.95),
('TAN/IT/2324/F/209', 'A', 'A', 'A-', 'A', 'B+', 'A', 'A-', 'A', 3.90),
('TAN/IT/2021/F/012', 'B-', 'B', 'C+', 'B', 'C', 'B', 'B-', 'C+', 2.60),
('TAN/IT/2223/F/040', 'B', 'I(CA)', 'B-', 'B+', 'B', 'C', 'B', 'B-', 2.30)";

if ($conn->query($sql2) === TRUE) { echo "3. ITsem2: Rows added successfully.<br>"; }


$sql3 = "INSERT INTO ITsem3 (stdno, IT3062, IT3042, IT3032, IT3012, IT3052, IT3022, IT3072, SGPA) VALUES 
('TAN/IT/2021/F/001', 'A', 'A-', 'B+', 'A', 'B', 'A-', 'B+', 3.65),
('TAN/IT/2022/F/045', 'B-', 'C+', 'C', 'B', 'C', 'I(SE)', 'C+', 2.05),
('TAN/IT/2324/F/209', 'A', 'A', 'A', 'A-', 'A', 'B+', 'A', 3.95),
('TAN/IT/2021/F/012', 'C+', 'B', 'B-', 'C+', 'B', 'C', 'B-', 2.40),
('TAN/IT/2223/F/040', 'B', 'B-', 'I(CA)', 'B', 'B+', 'B', 'C+', 2.25)";

if ($conn->query($sql3) === TRUE) { echo "4. ITsem3: Rows added successfully.<br>"; }


$sql4 = "INSERT INTO ITsem4 (stdno, IT4012, IT4022, IT4032, IT4042, IT4052, IT4212, IT4232, IT4222, IT4242, SGPA) VALUES 
('TAN/IT/2021/F/001', 'A-', 'A', 'B+', 'A-', 'B+', 'A', 'B', 'A-', 'A', 3.50),
('TAN/IT/2022/F/045', 'C', 'C+', 'I(SE)', 'C', 'B-', 'C+', 'C', 'B', 'C', 1.80),
('TAN/IT/2324/F/209', 'A', 'A-', 'A', 'A', 'B+', 'A-', 'A', 'A', 'B+', 3.85),
('TAN/IT/2021/F/012', 'B', 'B-', 'C+', 'B', 'C+', 'B-', 'B', 'C+', 'B', 2.75),
('TAN/IT/2223/F/040', 'I(CA)', 'B', 'B', 'B+', 'A-', 'B', 'B-', 'C+', 'B', 2.40)";

if ($conn->query($sql4) === TRUE) { echo "5. ITsem4: Rows added successfully.<br>"; }

$conn->close();
echo "<br><b>All test records for HNDIT students have been inserted.</b>";
?>