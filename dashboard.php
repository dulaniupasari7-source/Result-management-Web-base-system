<?php
// dashboard.php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$dbhost = 'localhost'; $dbuser = 'root'; $dbpass = ''; $db = 'it';
$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $db);
if (!$conn) { die("Connection failed: " . mysqli_connect_error()); }

$user_role = $_SESSION['role'] ?? 'user';
$user_name = $_SESSION['full_name'] ?? $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HNDIT Examination Results Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { display: flex; height: 100vh; background-color: #f4f7f6; overflow: hidden; }
        
        .sidebar { width: 260px; background-color: #002060; color: white; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 3px 0 10px rgba(0,0,0,0.1); }
        .sidebar-brand { padding: 20px; text-align: center; background-color: #001540; border-bottom: 4px solid #FFC000; }
        .sidebar-brand img { width: 80px; height: auto; background: white; padding: 2px; border-radius: 5px; }
        .sidebar-brand h3 { font-size: 16px; color: #FFC000; margin-top: 5px; }
        .sidebar-menu { list-style: none; margin-top: 10px; }
        .sidebar-menu li a { display: block; padding: 15px 20px; color: #e0e0e0; text-decoration: none; font-size: 14px; border-left: 4px solid transparent; cursor: pointer; }
        .sidebar-menu li a:hover, .sidebar-menu li a.active { background-color: #002b80; color: #FFC000; border-left: 4px solid #FFC000; }
        .hnd-photo-container { text-align: center; padding: 10px; width: 100%; }
        .hnd-photo-container img { max-width: 85%; border-radius: 5px; border: 2px solid #FFC000; }
        .logout-btn { background-color: #D61C1C !important; color: white !important; text-align: center; font-weight: bold; width: 85%; padding: 8px 10px; font-size: 14px; border-radius: 4px; text-decoration: none; margin: 0 auto 15px auto; display: block; }
        
        .main-content { flex-grow: 1; display: flex; flex-direction: column; overflow-y: auto; }
        header { background-color: #FFFFFF; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e0e0e0; flex-wrap: wrap; gap: 15px; }
        header .header-left { display: flex; align-items: center; gap: 20px; flex-wrap: wrap; }
        header h2 { color: #002060; margin: 0; font-size: 20px; }
        
        .header-actions { display: flex; align-items: center; gap: 10px; }
        .btn-header-action { border: none; padding: 8px 16px; font-size: 13px; font-weight: bold; border-radius: 5px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: 0.2s; }
        .btn-download-pdf { background-color: #107c41; color: white; }
        .btn-download-pdf:hover { background-color: #0b592e; }
        .btn-print-out { background-color: #002060; color: white; }
        .btn-print-out:hover { background-color: #FFC000; color: #002060; }
        
        .user-profile { background: #FFC000; padding: 6px 16px; border-radius: 20px; font-size: 13px; font-weight: bold; color: #002060; }
        .container { padding: 30px; }
        .content-section { display: none; background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .content-section.active-section { display: block; }
        .dashboard-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .card { background: white; padding: 20px; border-radius: 8px; border-top: 4px solid #002060; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .card.yellow-tint { border-top: 4px solid #FFC000; }
        .view-buttons-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin: 20px 0; }
        .btn-view { background-color: #002060; color: white; border: none; padding: 12px; font-weight: bold; border-radius: 6px; cursor: pointer; }
        .btn-view:hover { background-color: #FFC000; color: #002060; }

        .csv-nav-buttons { display: flex; gap: 12px; justify-content: flex-start; flex-wrap: wrap; margin-top: 20px; }
        .btn-csv-launch { background-color: #002060; color: white; border: 2px solid #002060; padding: 12px 22px; font-weight: bold; font-size: 14px; border-radius: 6px; cursor: pointer; transition: all 0.3s; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .btn-csv-launch:hover { background-color: #FFC000; color: #002060; border-color: #FFC000; }

        .btn-row-delete { background-color: #D61C1C; color: white; border: none; padding: 6px 14px; font-weight: bold; font-size: 12px; border-radius: 4px; cursor: pointer; }
        .btn-row-delete:hover { background-color: #a71414; }

        .section-title { font-size: 20px; color: #002060; margin-bottom: 20px; border-bottom: 2px solid #FFC000; padding-bottom: 8px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; max-width: 800px; }
        .form-group { display: flex; flex-direction: column; }
        .form-group label { font-weight: 600; color: #002060; margin-bottom: 6px; font-size: 14px; }
        .form-group input, .form-group select { padding: 10px 14px; border: 1px solid #ccc; border-radius: 5px; font-size: 14px; }
        .form-group.full-width { grid-column: span 2; }
        
        .password-wrapper { position: relative; display: flex; align-items: center; }
        .password-wrapper input { width: 100%; padding-right: 40px; }
        .toggle-password-btn { position: absolute; right: 10px; background: none; border: none; cursor: pointer; }
        .toggle-password-btn svg { width: 20px; height: 20px; fill: #666; }
        
        .btn-submit { background-color: #002060; color: white; padding: 12px 20px; border: none; border-radius: 5px; font-weight: bold; font-size: 15px; cursor: pointer; }

        /* Modal Overlay Window Styling */
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.65); z-index: 9999; justify-content: center; align-items: center; }
        .modal-overlay.active-modal { display: flex; }
        
        /* Modal Card එක Screen එකට සරිලන සේ හිර කර scroll/overflow පාලනය */
        .modal-card { 
            background: #ffffff; 
            width: 95%; 
            max-width: 1000px; 
            border-radius: 8px; 
            padding: 20px; 
            position: relative; 
            max-height: 92vh; 
            display: flex; 
            flex-direction: column; 
            overflow: hidden; 
        }

        .modal-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid #FFC000; padding-bottom: 10px; margin-bottom: 12px; }
        .modal-header h3 { color: #002060; font-size: 18px; }
        .close-modal-btn { background: none; border: none; font-size: 24px; font-weight: bold; color: #888; cursor: pointer; }
        .close-modal-btn:hover { color: #D61C1C; }

        .excel-view-container { border: 1px solid #d0d7de; border-radius: 4px; background: #ffffff; margin-bottom: 12px; }
        
        /* Excel Grid එකේ උස අඩු කර Scroll වීමට සැලැස්වීම */
        .excel-scroll-area { 
            max-height: 250px; 
            overflow: auto; 
            position: relative; 
            background: #fdfdfd; 
        }

        .excel-grid-table { border-collapse: collapse; font-size: 13px; white-space: nowrap; min-width: 100%; }
        .excel-grid-table th, .excel-grid-table td { border: 1px solid #d4d4d4; padding: 6px 12px; text-align: left; min-width: 110px; }
        .excel-grid-table td[contenteditable="true"]:focus { background-color: #e8f4fe !important; border: 2px solid #107c41 !important; outline: none; }
        .excel-grid-table td.cell-error { background-color: #ffcccc !important; border: 2px solid #d61c1c !important; }
        
        .excel-grid-table th.excel-col-head { background-color: #f3f3f3; color: #555; text-align: center; position: sticky; top: 0; z-index: 2; border-bottom: 1px solid #ccc; user-select: none; }
        .excel-grid-table td.excel-row-head { background-color: #f3f3f3; color: #555; text-align: center; position: sticky; left: 0; z-index: 1; min-width: 45px; width: 45px; border-right: 1px solid #ccc; user-select: none; }
        .excel-grid-table tr.header-data-row td { font-weight: bold; color: #000000; background-color: #ffffff; }

        .csv-input-box { border: 2px dashed #002060; background: #f9fbfd; padding: 10px 15px; border-radius: 6px; margin-bottom: 10px; }
        
        /* Upload බොත්තම පහළින්ම පැහැදිලිව රඳවා තැබීම */
        .modal-footer { 
            display: flex; 
            justify-content: flex-end; 
            padding-top: 10px; 
            border-top: 1px solid #eee; 
            margin-top: auto; 
        }

        .btn-upload-right { 
            background-color: #002060; 
            color: white; 
            border: none; 
            padding: 10px 35px; 
            font-weight: bold; 
            font-size: 15px; 
            border-radius: 4px; 
            cursor: pointer; 
            box-shadow: 0 3px 6px rgba(0,0,0,0.15); 
            transition: background 0.3s; 
        }
        .btn-upload-right:hover { background-color: #001540; }

        .data-viewer-container { margin-top: 25px; display: none; }
        .table-responsive { width: 100%; overflow-x: auto; margin-top: 15px; }
        table { width: 100%; border-collapse: collapse; }
        table th { background-color: #002060; color: white; padding: 10px; }
        table td { padding: 10px; border-bottom: 1px solid #ddd; }
        table tr:nth-child(even) { background-color: #f8f9fa; }
        .alert { padding: 12px; background: #d4edda; color: #155724; border-radius: 4px; margin-bottom: 15px; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-top">
            <div class="sidebar-brand"><img src="SL.jpg" alt="Logo"><h3>HNDIT EXAMS</h3></div>
            <ul class="sidebar-menu">
                <li><a onclick="switchTab('dashboard')" id="tab-dashboard" class="active">Dashboard Summary</a></li>
                <?php if ($user_role === 'admin'): ?>
                    <li><a onclick="switchTab('bulk-entry')" id="tab-bulk-entry">Upload CSV Sheet (Excel)</a></li>
                    <li><a onclick="switchTab('admin-entry')" id="tab-admin-entry">Add System Users (Admin)</a></li>
                <?php endif; ?>
            </ul>
        </div>
        <div class="sidebar-bottom">
            <div class="hnd-photo-container"><img src="HND.jpg" alt="HND"></div>
            <a href="logout.php" class="logout-btn">LOGOUT</a>
        </div>
    </div>

    <div class="main-content">
        <header>
            <div class="header-left">
                <h2>Examination Results Management System</h2>
                <div class="header-actions">
                    <button class="btn-header-action btn-download-pdf" onclick="openExportModal('download')">
                        📥 Download PDF
                    </button>
                    <button class="btn-header-action btn-print-out" onclick="openExportModal('print')">
                        🖨️ Printout
                    </button>
                </div>
            </div>
            <div class="user-profile"><?php echo htmlspecialchars($user_name) . " (" . strtoupper($user_role) . ")"; ?></div>
        </header>

        <div class="container">
            <?php if(isset($_GET['status']) && $_GET['status'] == 'success'): ?>
                <div class="alert">ක්‍රියාවලිය සාර්ථකව නිම කරන ලදී! (Action Completed Successfully!)</div>
            <?php endif; ?>

            <!-- SECTION 1: DASHBOARD SUMMARY -->
            <div id="sec-dashboard" class="content-section active-section">
                <h3 class="section-title">Overview Dashboard</h3>
                <div class="dashboard-cards">
                    <div class="card"><h4>Total Students</h4><div class="value" id="totalStudentsCount"><?php echo mysqli_num_rows(mysqli_query($conn, "SELECT * FROM ITstd")); ?></div></div>
                    <?php if ($user_role === 'admin'): ?>
                        <div class="card yellow-tint"><h4>System Users</h4><div class="value"><?php echo mysqli_num_rows(mysqli_query($conn, "SELECT * FROM Admin")); ?></div></div>
                    <?php endif; ?>
                </div>

                <h3 style="margin-top:30px;">Data Records Viewer</h3>
                <div class="view-buttons-grid">
                    <button class="btn-view" onclick="loadTableData('ITstd')">View Students Table</button>
                    <button class="btn-view" onclick="loadTableData('ITsem1')">View Semester 1</button>
                    <button class="btn-view" onclick="loadTableData('ITsem2')">View Semester 2</button>
                    <button class="btn-view" onclick="loadTableData('ITsem3')">View Semester 3</button>
                    <button class="btn-view" onclick="loadTableData('ITsem4')">View Semester 4</button>
                    <?php if ($user_role === 'admin'): ?>
                        <button class="btn-view" onclick="loadTableData('Admin')">View Admin and User</button>
                    <?php endif; ?>
                </div>

                <div id="data-viewer" class="data-viewer-container">
                    <h4 id="viewer-title" style="color: #002060;">Table Records</h4>
                    <div style="background: #f8f9fa; padding: 15px; display: flex; gap: 10px; align-items: center; margin: 15px 0; border: 1px solid #ddd; border-radius: 6px;">
                        <label id="searchLabel" style="font-weight: bold; color: #002060;">Index No:</label>
                        <input type="text" id="searchQuery" style="flex-grow: 1; padding: 8px;" placeholder="Add Student Reg Number">
                        <button onclick="executeSearch()" style="background:#002060; color:white; padding:8px 20px; border:none; font-weight:bold; cursor:pointer;">Search</button>
                        <button onclick="clearSearch()" style="background:#6c757d; color:white; padding:8px 15px; border:none; font-weight:bold; cursor:pointer;">Reset</button>
                    </div>
                    <div class="table-responsive" id="table-content"></div>
                </div>
            </div>

            <!-- SECTION 2: BULK CSV UPLOAD -->
            <?php if ($user_role === 'admin'): ?>
                <div id="sec-bulk-entry" class="content-section">
                    <h3 class="section-title">Upload Excel CSV Sheet (Bulk Upload)</h3>
                    <div class="csv-nav-buttons">
                        <button type="button" class="btn-csv-launch" onclick="openCsvModal('ITstd')">Student Table</button>
                        <button type="button" class="btn-csv-launch" onclick="openCsvModal('ITsem1')">Semester 1</button>
                        <button type="button" class="btn-csv-launch" onclick="openCsvModal('ITsem2')">Semester 2</button>
                        <button type="button" class="btn-csv-launch" onclick="openCsvModal('ITsem3')">Semester 3</button>
                        <button type="button" class="btn-csv-launch" onclick="openCsvModal('ITsem4')">Semester 4</button>
                        <button type="button" class="btn-csv-launch" onclick="openCsvModal('Admin')">Password</button>
                    </div>
                </div>

                <div id="sec-admin-entry" class="content-section">
                    <h3 class="section-title">Create System Account</h3>
                    <form action="process_data.php" method="POST" class="form-grid">
                        <input type="hidden" name="action" value="add_admin">
                        <div class="form-group">
                            <label>Full Name:</label>
                            <input type="text" name="Full_name" placeholder="Enter Full Name" required>
                        </div>
                        <div class="form-group">
                            <label>Username / Index:</label>
                            <input type="text" name="Username" placeholder="Enter Username" required>
                        </div>
                        <div class="form-group">
                            <label>Password:</label>
                            <div class="password-wrapper">
                                <input type="password" id="adminPassword" name="password" placeholder="Enter Password" required>
                                <button type="button" class="toggle-password-btn" onclick="togglePasswordVisibility()">
                                    <svg id="eyeIcon" viewBox="0 0 24 24"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>
                                </button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Role:</label>
                            <select name="role">
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="form-group full-width" style="margin-top: 10px;">
                            <button type="submit" class="btn-submit">Register Account</button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- PRINT & PDF EXPORT CONFIGURATION MODAL -->
    <div id="exportConfigModal" class="modal-overlay">
        <div class="modal-card" style="max-width: 650px;">
            <div class="modal-header">
                <h3 style="color:#002060;">Print / PDF Sheet Settings (නිල ලේඛන සැකසුම්)</h3>
                <button type="button" class="close-modal-btn" onclick="closeExportModal()">&times;</button>
            </div>
            
            <form onsubmit="generateOfficialSheet(event)" class="form-grid" style="grid-template-columns: 1fr 1fr; gap:12px; overflow-y: auto; padding-right: 5px;">
                <div class="form-group full-width">
                    <label>Examination Title (විභාග ශීර්ෂය):</label>
                    <input type="text" id="cfgExamTitle" value="SECOND YEAR-FIRST SEMESTER EXAMINATION 2025" required>
                </div>

                <div class="form-group">
                    <label>Institute (ආයතනය):</label>
                    <input type="text" id="cfgInstitute" value="Tangalle" required>
                </div>

                <div class="form-group">
                    <label>Date of Issuing (නිකුත් කළ දිනය):</label>
                    <input type="date" id="cfgIssueDate" value="<?php echo date('Y-m-d'); ?>" required>
                </div>

                <div class="form-group full-width">
                    <label>Effective Date (බලපැවැත්වෙන දිනය):</label>
                    <input type="date" id="cfgEffectiveDate" value="2026-03-01" required>
                </div>

                <div class="form-group full-width" style="border-top:1px solid #ccc; padding-top:10px; margin-top:5px;">
                    <label style="color:#D61C1C;">Authorized Officers' Names (නිලධාරීන්ගේ නම්):</label>
                </div>

                <div class="form-group">
                    <label>Prepared by (Management Assistant):</label>
                    <input type="text" id="cfgOfficerPrep" value="C.D.Kumudu Manike" required>
                </div>

                <div class="form-group">
                    <label>Checked by (Management Assistant):</label>
                    <input type="text" id="cfgOfficerChk" value="S.R.P.Gamage" required>
                </div>

                <div class="form-group">
                    <label>Certified by (Director Examinations):</label>
                    <input type="text" id="cfgOfficerCert" value="B.M.Terrence Chandike" required>
                </div>

                <div class="form-group">
                    <label>Recommended by (DDG Acting):</label>
                    <input type="text" id="cfgOfficerRecom" value="Dr.W.B.K.Bandara" required>
                </div>

                <div class="form-group full-width">
                    <label>Approved by (Director General):</label>
                    <input type="text" id="cfgOfficerAppr" value="M.C.L.Rodrigo" required>
                </div>

                <div class="form-group full-width" style="margin-top:15px; display:flex; justify-content:flex-end;">
                    <button type="submit" class="btn-submit" style="background:#002060; padding:10px 25px;">Generate Official Sheet</button>
                </div>
            </form>
        </div>
    </div>

    <!-- DYNAMIC EXCEL-LIKE CSV MODAL (FIXED SCROLL & VISIBLE UPLOAD BUTTON) -->
    <?php if ($user_role === 'admin'): ?>
    <div id="csvModalOverlay" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3 id="modalTitle">Excel CSV Structure View</h3>
                <button type="button" class="close-modal-btn" onclick="closeCsvModal()">&times;</button>
            </div>
            
            <form action="process_data.php" method="POST" enctype="multipart/form-data" onsubmit="return validateAndPrepareGridData()" style="display:flex; flex-direction:column; height:100%; overflow:hidden;">
                <input type="hidden" name="action" value="csv_import">
                <input type="hidden" name="target_table" id="modalTargetTable" value="ITstd">
                <input type="hidden" name="grid_data_json" id="gridDataJson">

                <p style="font-size:13px; font-weight:bold; color:#002060; margin-bottom:8px;">
                    Excel Data Grid (කොටු තුළ දත්ත ටයිප් කර හෝ CSV ගොනුවක් මඟින් Upload කළ හැක - Arrow Keys මඟින් ගමන් කළ හැක):
                </p>

                <div class="excel-view-container">
                    <div class="excel-scroll-area" id="excelScrollArea"></div>
                </div>

                <div class="csv-input-box">
                    <label style="font-size:13px; font-weight:bold; color:#002060; display:block; margin-bottom:5px;">Select CSV File (Optional):</label>
                    <input type="file" name="csv_file" id="csvFileInput" accept=".csv">
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn-upload-right">Upload</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <script>
        var currentActiveTable = 'ITsem1';
        var activeHeadersCount = 0;

        function switchTab(t) {
            document.querySelectorAll('.content-section').forEach(s => s.classList.remove('active-section'));
            document.querySelectorAll('.sidebar-menu li a').forEach(l => l.classList.remove('active'));
            var targetSec = document.getElementById('sec-' + t);
            var targetTab = document.getElementById('tab-' + t);
            if (targetSec) targetSec.classList.add('active-section');
            if (targetTab) targetTab.classList.add('active');
        }

        function togglePasswordVisibility() {
            var p = document.getElementById('adminPassword'); var i = document.getElementById('eyeIcon');
            if (p) {
                if (p.type === 'password') { p.type = 'text'; i.style.fill = '#002060'; } 
                else { p.type = 'password'; i.style.fill = '#666'; }
            }
        }

        function openExportModal(mode) {
            document.getElementById('exportConfigModal').classList.add('active-modal');
        }

        function closeExportModal() {
            document.getElementById('exportConfigModal').classList.remove('active-modal');
        }

        function generateOfficialSheet(e) {
            e.preventDefault();
            var target = currentActiveTable || 'ITsem1';

            var examTitle    = document.getElementById('cfgExamTitle').value;
            var institute    = document.getElementById('cfgInstitute').value;
            var issueDate    = document.getElementById('cfgIssueDate').value;
            var effectiveDt  = document.getElementById('cfgEffectiveDate').value;
            
            var offPrep      = document.getElementById('cfgOfficerPrep').value;
            var offChk       = document.getElementById('cfgOfficerChk').value;
            var offCert      = document.getElementById('cfgOfficerCert').value;
            var offRecom     = document.getElementById('cfgOfficerRecom').value;
            var offAppr      = document.getElementById('cfgOfficerAppr').value;

            var url = 'export_pdf.php?table=' + encodeURIComponent(target)
                    + '&exam_title=' + encodeURIComponent(examTitle)
                    + '&institute=' + encodeURIComponent(institute)
                    + '&issue_date=' + encodeURIComponent(issueDate)
                    + '&effective_date=' + encodeURIComponent(effectiveDt)
                    + '&officer_prep=' + encodeURIComponent(offPrep)
                    + '&officer_chk=' + encodeURIComponent(offChk)
                    + '&officer_cert=' + encodeURIComponent(offCert)
                    + '&officer_recom=' + encodeURIComponent(offRecom)
                    + '&officer_appr=' + encodeURIComponent(offAppr);

            closeExportModal();
            window.open(url, '_blank');
        }

        function openCsvModal(table) {
            var targetTbl = document.getElementById('modalTargetTable');
            if (!targetTbl) return;
            targetTbl.value = table;
            document.getElementById('csvFileInput').value = "";
            var title = document.getElementById('modalTitle');
            var excelArea = document.getElementById('excelScrollArea');

            var headers = [];
            if (table === 'ITstd') {
                title.innerText = "Excel Data Entry: Student Table (ITstd)";
                headers = ['stdno', 'stdname', 'phone', 'effectdate', 'certiflag'];
            } else if (table === 'ITsem1') {
                title.innerText = "Excel Data Entry: Semester 1 (ITsem1)";
                headers = ['stdno', 'IT1012', 'IT1022', 'IT1032', 'IT1042', 'IT1062', 'IT1052'];
            } else if (table === 'ITsem2') {
                title.innerText = "Excel Data Entry: Semester 2 (ITsem2)";
                headers = ['stdno', 'IT2012', 'IT2022', 'IT2032', 'IT2042', 'IT2052', 'IT2062', 'IT2072', 'IT2082'];
            } else if (table === 'ITsem3') {
                title.innerText = "Excel Data Entry: Semester 3 (ITsem3)";
                headers = ['stdno', 'IT3062', 'IT3042', 'IT3032', 'IT3012', 'IT3052', 'IT3022', 'IT3072'];
            } else if (table === 'ITsem4') {
                title.innerText = "Excel Data Entry: Semester 4 (ITsem4)";
                headers = ['stdno', 'IT4012', 'IT4022', 'IT4032', 'IT4042', 'IT4052', 'IT4212', 'IT4232', 'IT4222', 'IT4242'];
            } else if (table === 'Admin') {
                title.innerText = "Excel Data Entry: Password / User Table (Admin)";
                headers = ['Username', 'password', 'role', 'Full_name'];
            }

            activeHeadersCount = headers.length;
            var colLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S'];

            var html = "<table class='excel-grid-table' id='excelInteractiveTable'>";
            html += "<thead><tr><th class='excel-col-head'></th>";
            for(var i=0; i < colLetters.length; i++) {
                html += "<th class='excel-col-head'>" + colLetters[i] + "</th>";
            }
            html += "</tr></thead><tbody>";

            html += "<tr class='header-data-row'><td class='excel-row-head'>1</td>";
            for(var i=0; i < colLetters.length; i++) {
                if(i < headers.length) {
                    html += "<td contenteditable='false' style='background:#f0f4f8; font-weight:bold;'>" + headers[i] + "</td>";
                } else {
                    html += "<td contenteditable='true'></td>";
                }
            }
            html += "</tr>";

            for(var r=2; r<=200; r++) {
                html += "<tr class='grid-data-row'><td class='excel-row-head'>" + r + "</td>";
                for(var c=0; c < colLetters.length; c++) {
                    html += "<td contenteditable='true'></td>";
                }
                html += "</tr>";
            }

            html += "</tbody></table>";
            excelArea.innerHTML = html;
            document.getElementById('csvModalOverlay').classList.add('active-modal');

            bindGridArrowNavigation();
        }

        function bindGridArrowNavigation() {
            var table = document.getElementById('excelInteractiveTable');
            if (!table) return;

            table.addEventListener('keydown', function(e) {
                var currentCell = e.target;
                if (currentCell.tagName !== 'TD' || currentCell.getAttribute('contenteditable') !== 'true') return;

                var key = e.key;
                if (!['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'Enter', 'Tab'].includes(key)) return;

                var currentRow = currentCell.parentElement;
                var cellIndex = Array.prototype.indexOf.call(currentRow.children, currentCell);
                var targetCell = null;

                if (key === 'ArrowUp') {
                    var prevRow = currentRow.previousElementSibling;
                    if (prevRow && !prevRow.classList.contains('header-data-row')) targetCell = prevRow.children[cellIndex];
                } else if (key === 'ArrowDown' || key === 'Enter') {
                    var nextRow = currentRow.nextElementSibling;
                    if (nextRow) targetCell = nextRow.children[cellIndex];
                } else if (key === 'ArrowLeft') {
                    if (cellIndex > 1) targetCell = currentRow.children[cellIndex - 1];
                } else if (key === 'ArrowRight' || key === 'Tab') {
                    if (cellIndex < currentRow.children.length - 1) {
                        targetCell = currentRow.children[cellIndex + 1];
                    } else {
                        var nextRow = currentRow.nextElementSibling;
                        if (nextRow) targetCell = nextRow.children[1];
                    }
                }

                if (targetCell && targetCell.getAttribute('contenteditable') === 'true') {
                    e.preventDefault();
                    targetCell.focus();
                    var range = document.createRange();
                    var sel = window.getSelection();
                    range.selectNodeContents(targetCell);
                    range.collapse(false);
                    sel.removeAllRanges();
                    sel.addRange(range);
                }
            });
        }

        function validateAndPrepareGridData() {
            document.querySelectorAll('td.cell-error').forEach(c => c.classList.remove('cell-error'));

            var fileInput = document.getElementById('csvFileInput');
            if (fileInput && fileInput.files.length > 0) return true;

            var targetTable = document.getElementById('modalTargetTable').value;
            var rows = document.querySelectorAll('#excelInteractiveTable tr.grid-data-row');
            var gridRecords = [];
            var hasError = false;

            rows.forEach(function(row) {
                var cells = row.querySelectorAll('td[contenteditable="true"]');
                var primaryVal = cells[0] ? cells[0].innerText.trim() : '';
                
                if (primaryVal !== '') {
                    var rowValues = [primaryVal];
                    for (var i = 1; i < activeHeadersCount; i++) {
                        var cellVal = cells[i] ? cells[i].innerText.trim() : '';
                        if (targetTable === 'ITsem4' && i >= 6 && i <= 9) {
                            if (cellVal === '') cellVal = '-';
                        } else {
                            if (cellVal === '') {
                                hasError = true;
                                cells[i].classList.add('cell-error');
                            }
                        }
                        rowValues.push(cellVal);
                    }
                    gridRecords.push(rowValues);
                }
            });

            if (hasError) {
                alert("Error: කරුණාකර අනිවාර්ය විෂයයන් සඳහා වන සියලුම කොටු හිස් නොතබා සම්පූර්ණ කරන්න!");
                return false;
            }

            if (gridRecords.length === 0) {
                alert("Error: කරුණාකර Grid එකෙහි දත්ත ටයිප් කරන්න හෝ CSV File එකක් Select කරන්න!");
                return false;
            }

            document.getElementById('gridDataJson').value = JSON.stringify(gridRecords);
            return true;
        }

        function closeCsvModal() {
            var overlay = document.getElementById('csvModalOverlay');
            if (overlay) overlay.classList.remove('active-modal');
        }

        function loadTableData(t) {
            currentActiveTable = t; 
            document.getElementById('data-viewer').style.display = 'block';
            if (t === 'Admin') { 
                document.getElementById('searchLabel').innerText = "Full Name:"; 
                document.getElementById('searchQuery').placeholder = "Add Full name"; 
            } else { 
                document.getElementById('searchLabel').innerText = "Index No:"; 
                document.getElementById('searchQuery').placeholder = "Add Student Reg Number"; 
            }
            fetchData('');
        }

        function executeSearch() { 
            var q = document.getElementById('searchQuery').value.trim(); 
            if(q === '') { alert('Enter value!'); return; } 
            fetchData(q); 
        }

        function clearSearch() { 
            document.getElementById('searchQuery').value = ''; 
            fetchData(''); 
        }

        function fetchData(q) {
            fetch('fetch_table_data.php?table=' + currentActiveTable + '&query=' + encodeURIComponent(q))
            .then(r => r.text())
            .then(html => document.getElementById('table-content').innerHTML = html);
        }

        function deleteSelectedRow(btn, tableName, idValue) {
            if (!confirm("මෙම Record එක Database එකෙන් ස්ථිරවම Delete කිරීමට ඔබට විශ්වාසද? (" + idValue + ")")) {
                return;
            }

            var formData = new FormData();
            formData.append('table', tableName);
            formData.append('id', idValue);

            fetch('delete_record.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(res => {
                if (res.status === 'success') {
                    var tr = btn.closest('tr');
                    if (tr) tr.remove();

                    var countEl = document.getElementById('totalStudentsCount');
                    if (countEl && tableName === 'ITstd') {
                        var currentCount = parseInt(countEl.innerText);
                        if (!isNaN(currentCount) && currentCount > 0) {
                            countEl.innerText = currentCount - 1;
                        }
                    }
                    alert('Record එක සාර්ථකව Delete කරන ලදී!');
                } else {
                    alert('Delete කිරීම අසාර්ථක විය: ' + (res.message || 'Error occurred'));
                }
            })
            .catch(err => {
                alert('සන්නිවේදන දෝෂයක් සිදුවිය: ' + err);
            });
        }
    </script>
</body>
</html>