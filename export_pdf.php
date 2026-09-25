<?php
// export_pdf.php
session_start();
if (empty($_SESSION['logged_in'])) {
    die("Unauthorized Access");
}

$dbhost = 'localhost';$dbuser = 'root'; 
$dbpass = '';$db = 'it';

$conn = mysqli_connect($dbhost,$dbuser, $dbpass,$db);
if (!$conn) { die("Database Connection failed"); }

// තෝරාගත් Table එක (Default: ITsem1)
$table = isset($_GET['table']) && strpos($_GET['table'], 'ITsem') !== false ? mysqli_real_escape_string($conn,$_GET['table']) : 'ITsem1';

// Admin විසින් ලබා දෙන අගයන්
$exam_title   = isset($_GET['exam_title']) && !empty($_GET['exam_title']) ? trim($_GET['exam_title']) : 'SECOND YEAR - FIRST SEMESTER EXAMINATION 2025';
$issue_date   = isset($_GET['issue_date']) && !empty($_GET['issue_date']) ? date('d.m.Y', strtotime($_GET['issue_date'])) : date('d.m.Y');
$effective_dt = isset($_GET['effective_date']) && !empty($_GET['effective_date']) ? date('d.m.Y', strtotime($_GET['effective_date'])) : '01.03.2026';
$institute    = isset($_GET['institute']) && !empty($_GET['institute']) ? trim($_GET['institute']) : 'Tangalle';

// Admin විසින් ලබා දෙන නිලධාරීන්ගේ නම් 5
$officer_prep = isset($_GET['officer_prep']) && !empty($_GET['officer_prep']) ? trim($_GET['officer_prep']) : 'C.D.Kumudu Manike';$officer_chk  = isset($_GET['officer_chk']) && !empty($_GET['officer_chk']) ? trim($_GET['officer_chk']) : 'S.R.P.Gamage';$officer_cert = isset($_GET['officer_cert']) && !empty($_GET['officer_cert']) ? trim($_GET['officer_cert']) : 'B.M.Terrence Chandike';$officer_recom= isset($_GET['officer_recom']) && !empty($_GET['officer_recom']) ? trim($_GET['officer_recom']) : 'Dr.W.B.K.Bandara';$officer_appr = isset($_GET['officer_appr']) ? trim($_GET['officer_appr']) : 'M.C.L.Rodrigo';

// New Syllabus අනුව නිල විෂය නාම
$subject_headers = array(
    // Semester 1
    'IT1012' => 'HNDIT1012 - Visual Application Programming',
    'IT1022' => 'HNDIT1022 - Web Design',
    'IT1032' => 'HNDIT1032 - Computer and Network Systems',
    'IT1042' => 'HNDIT1042 - Information Management and Information Systems',
    'IT1062' => 'HNDIT1062 - Communication Skills',
    'IT1052' => 'HNDIT1052 - ICT Project (Individual)',
    
    // Semester 2
    'IT2012' => 'HNDIT2012 - Fundamentals of Programming',
    'IT2022' => 'HNDIT2022 - Software Development',
    'IT2032' => 'HNDIT2032 - System Analysis and Design',
    'IT2042' => 'HNDIT2042 - Data Communication and Computer Networks',
    'IT2052' => 'HNDIT2052 - Principles of User Interface Design',
    'IT2062' => 'HNDIT2062 - ICT Project (Group)',
    'IT2072' => 'HNDIT2072 - Technical Writing',
    'IT2082' => 'HNDIT2082 - Human Value & Professional Ethics',

    // Semester 3
    'IT3012' => 'HNDIT3012 - Object Oriented Programming',
    'IT3022' => 'HNDIT3022 - Web Programming',
    'IT3032' => 'HNDIT3032 - Data Structures and Algorithms',
    'IT3042' => 'HNDIT3042 - Database Management Systems',
    'IT3052' => 'HNDIT3052 - Operating Systems',
    'IT3062' => 'HNDIT3062 - Information and Computer Security',
    'IT3072' => 'HNDIT3072 - Statistics for IT',

    // Semester 4
    'IT4012' => 'HNDIT4012 - Software Engineering',
    'IT4022' => 'HNDIT4022 - Software Quality Assurance',
    'IT4032' => 'HNDIT4032 - IT Project Management',
    'IT4042' => 'HNDIT4042 - Professional World',
    'IT4052' => 'HNDIT4052 - Programming Individual Project',
    'IT4212' => 'HNDIT4212 - Machine Learning',
    'IT4232' => 'HNDIT4232 - Enterprise Architecture',
    'IT4222' => 'HNDIT4222 - Business Analysis Practice',
    'IT4242' => 'HNDIT4242 - Computer Services Management'
);

function markToDisplayGrade($val) {
    $val = strtoupper(trim($val));
    if ($val === '' or$val === '-') {
        return '-';
    }
    if (in_array($val, array('NE', 'I(SE)', 'DFR', 'I(CA)', 'AB', 'INC'))) {
        return $val;
    }
    if (is_numeric($val)) {
        $mark = floatval($val);
        if ($mark < 40) return 'I(SE)';
        if ($mark >= 85) return 'A+';
        if ($mark >= 70) return 'A';
        if ($mark >= 65) return 'A-';
        if ($mark >= 60) return 'B+';
        if ($mark >= 55) return 'B';
        if ($mark >= 50) return 'B-';
        if ($mark >= 45) return 'C+';
        if ($mark >= 40) return 'C';
    }
    if (in_array($val, array('D+', 'D', 'E', 'C-'))) {
        return 'I(SE)';
    }
    return $val;
}

// ITstd වෙතින් ශිෂ්‍ය නම (stdname) ලබාගැනීම
$sql = "SELECT s.stdname, t.* 
        FROM `" . $table . "` t 
        LEFT JOIN `ITstd` s ON REPLACE(TRIM(t.stdno), ' ', '') = REPLACE(TRIM(s.stdno), ' ', '') 
        ORDER BY t.stdno ASC";
$res = mysqli_query($conn,$sql);

$subject_cols = array();
if ($res) {
    $fields = mysqli_fetch_fields($res);
    foreach ($fields as$f) {
        if ($f->name !== 'stdno' &&$f->name !== 'stdname' && $f->name !== 'SGPA' && strtolower($f->name) !== 'sn') {
            $subject_cols[] =$f->name;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SLIATE Result Sheet</title>
    <style>
        @page { size: A4 landscape; margin: 4mm 8mm; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 11px; margin: 0; padding: 2px 5px; color: #000; }
        
        /* 1. Header Block එකට ඉහළින් Logo එක සහ SLIATE නම */
        .top-logo-strip {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 2px;
            margin-top: 0;
        }
        .top-logo-strip img {
            width: 95px;
            height: auto;
            border: 1.5px solid #000;
            padding: 2px;
        }
        .top-logo-strip .sliate-title-txt {
            font-size: 68px;
            font-weight: 900;
            font-family: 'Arial Black', Arial, sans-serif;
            letter-spacing: 3px;
            color: #111;
            line-height: 0.9;
        }

        /* 2. ඉහළට ගන්නා ලද ප්‍රධාන ආයතනික මාතෘකා කොටස */
        .header-center-titles { 
            text-align: center; 
            width: 100%; 
            margin-top: -10px;
            margin-bottom: 4px;
        }
        .inst-title { 
            font-size: 18.5px; 
            font-weight: 900; 
            margin-bottom: 2px; 
            text-transform: uppercase; 
            letter-spacing: 0.5px;
        }
        .exam-title { font-size: 13px; font-weight: bold; margin-bottom: 1px; text-transform: uppercase; }
        .course-title { font-size: 12px; font-weight: bold; margin-bottom: 1px; }
        .result-sheet-title { font-size: 11.5px; font-weight: bold; }

        /* Meta Bar: තවත් ඉහළට ගෙන (margin-top: 10px පමණි) වගුවේ ඉහළ ඉර මතට කෙළින්ම සවි කිරීම */
        .meta-container { 
            display: flex; 
            justify-content: space-between; 
            align-items: flex-end; 
            margin-top: 10px; 
            margin-bottom: 0px; 
            padding-bottom: 3px;
        }
        .institute-left { font-size: 11.5px; font-weight: bold; }
        .dates-right-stack { display: flex; flex-direction: column; gap: 2px; font-size: 11.5px; font-weight: bold; text-align: right; }
        .date-row { display: flex; justify-content: flex-end; gap: 12px; }

        /* Data Table: ඉහළ ඉඩ සම්පූර්ණයෙන්ම ඉවත් කර ඉහළට ගැනීම */
        .official-table { width: 100%; border-collapse: collapse; margin-top: 0px; }
        .official-table th, .official-table td { border: 1px solid #000; padding: 4px; text-align: center; font-size: 11px; }
        
        .official-table th { font-weight: bold; font-size: 11px; }
        .official-table th.vertical-header { height: 180px; vertical-align: bottom; padding-bottom: 6px; width: 62px; }
        .official-table th.vertical-header div { 
            writing-mode: vertical-rl; 
            transform: rotate(180deg); 
            white-space: nowrap; 
            font-size: 11px; 
            font-weight: bold; 
            margin: 0 auto; 
            letter-spacing: 0.5px;
        }
        .official-table td.text-left { text-align: left; padding-left: 6px; }

        /* Signatures / Footer */
        .footer-container { margin-top: 12px; page-break-inside: avoid; }
        .withheld-note { font-weight: bold; font-size: 11px; margin-bottom: 6px; }
        .sig-grid { display: grid; grid-template-columns: repeat(5, 1fr); text-align: center; font-size: 10px; }
        .sig-col { display: flex; flex-direction: column; justify-content: flex-end; align-items: center; min-height: 70px; }
        .sig-label { font-weight: bold; margin-bottom: 30px; }
        .sig-name { font-weight: bold; font-size: 10.5px; }
        .sig-post { font-size: 9.5px; }

        .btn-print-action { position: fixed; top: 12px; right: 12px; background: #002060; color: #fff; padding: 8px 18px; font-weight: bold; border: none; border-radius: 4px; cursor: pointer; }
        @media print { .btn-print-action { display: none; } }
    </style>
</head>
<body>
    <button class="btn-print-action" onclick="window.print()">Print / Save as PDF</button>

    <!-- 1. ඉහළින්ම Logo එක සහ SLIATE අකුරු පෙළ -->
    <div class="top-logo-strip">
        <img src="SL.jpg" alt="SLIATE Logo">
        <span class="sliate-title-txt">SLIATE</span>
    </div>

    <!-- 2. ඉහළට කරන ලද ප්‍රධාන ආයතනික මාතෘකා කොටස -->
    <div class="header-center-titles">
        <div class="inst-title">SRI LANKA INSTITUTE OF ADVANCED TECHNOLOGICAL EDUCATION</div>
        <div class="exam-title"><?php echo htmlspecialchars($exam_title); ?></div>
        <div class="course-title">Higher National Diploma in Information Technology</div>
        <div class="result-sheet-title">Result Sheet</div>
    </div>

    <!-- META BAR: INSTITUTE & DATES (ඉහළට ගෙන සකසන ලද කොටස) -->
    <div class="meta-container">
        <div class="institute-left">
            INSTITUTE: <?php echo htmlspecialchars($institute); ?>
        </div>
        <div class="dates-right-stack">
            <div class="date-row">
                <span>Effective Date:</span>
                <span style="min-width: 80px; text-align: left;"><?php echo htmlspecialchars($effective_dt); ?></span>
            </div>
            <div class="date-row">
                <span>Date of Issuing:</span>
                <span style="min-width: 80px; text-align: left;"><?php echo htmlspecialchars($issue_date); ?></span>
            </div>
        </div>
    </div>

    <!-- DATA TABLE -->
    <table class="official-table">
        <thead>
            <tr>
                <th style="width: 32px;">sn</th>
                <th style="width: 175px; text-align: center;">NAME</th>
                <th style="width: 130px; text-align: center;">INDEX NUMBER</th>
                <?php
                foreach ($subject_cols as$c) {
                    $title = isset($subject_headers[$c]) ?$subject_headers[$c] :$c;
                    echo "<th class='vertical-header'><div>" . htmlspecialchars($title) . "</div></th>";
                }
                ?>
                <th style="width: 50px; vertical-align: middle;">SGPA</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sn = 1;
            if ($res && mysqli_num_rows($res) > 0) {
                while ($row = mysqli_fetch_assoc($res)) {$name  = (!empty($row['stdname']) &&$row['stdname'] !== '-') ? $row['stdname'] : '-';$index = !empty($row['stdno']) ?$row['stdno'] : '-';

                    echo "<tr>";
                    echo "<td>" . $sn++ . "</td>";
                    echo "<td class='text-left'>" . htmlspecialchars($name) . "</td>";
                    echo "<td>" . htmlspecialchars($index) . "</td>";

                    foreach ($subject_cols as $c) {$displayGrade = markToDisplayGrade($row[$c] ?? '');
                        echo "<td>" . htmlspecialchars($displayGrade) . "</td>";
                    }

                    echo "<td style='font-weight:bold;'>" . htmlspecialchars($row['SGPA'] ?? '0.00') . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='" . (count($subject_cols) + 4) . "'>කිසිදු විභාග දත්තයක් හමු නොවීය.</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <!-- FOOTER / SIGNATURES BLOCK -->
    <div class="footer-container">
        <div class="withheld-note">* withheld</div>
        
        <div class="sig-grid">
            <div class="sig-col">
                <div class="sig-label">Prepared by:......................</div>
                <div class="sig-name"><?php echo htmlspecialchars($officer_prep); ?></div>
                <div class="sig-post">Management Assistant</div>
            </div>
            
            <div class="sig-col">
                <div class="sig-label">Checked by: .........................</div>
                <div class="sig-name"><?php echo htmlspecialchars($officer_chk); ?></div>
                <div class="sig-post">Management Assistant</div>
            </div>

            <div class="sig-col">
                <div class="sig-label">Certified by:...................</div>
                <div class="sig-name"><?php echo htmlspecialchars($officer_cert); ?></div>
                <div class="sig-post">Director (Examinations)</div>
            </div>

            <div class="sig-col">
                <div class="sig-label">Recommended by:..................</div>
                <div class="sig-name"><?php echo htmlspecialchars($officer_recom); ?></div>
                <div class="sig-post">DDG(AA&P&R) Acting</div>
            </div>

            <div class="sig-col">
                <div class="sig-label">Approved by:..................</div>
                <div class="sig-name"><?php echo htmlspecialchars($officer_appr); ?></div>
                <div class="sig-post">Director General</div>
            </div>
        </div>
    </div>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => { window.print(); }, 400);
        });
    </script>
</body>
</html>