<?php
/**
 * Admin Student Listing Page
 * School Management Website
 */

require_once __DIR__ . '/../../includes/admin_header.php';

// Pagination settings
$limit = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Filter Parameters
$search = sanitize_input($_GET['search'] ?? '');
$class_id = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;
$section_id = isset($_GET['section_id']) ? (int)$_GET['section_id'] : 0;

// Fetch Classes for filter select
$classes = [];
try {
    $classes = $pdo->query("SELECT * FROM `classes` ORDER BY `numeric_name` ASC")->fetchAll();
} catch (PDOException $e) {}

// Build Query
$where_clauses = [];
$params = [];

if (!empty($search)) {
    $where_clauses[] = " (s.name_bn LIKE ? OR s.name_en LIKE ? OR s.roll = ?) ";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = $search;
}

if ($class_id > 0) {
    $where_clauses[] = " s.class_id = ? ";
    $params[] = $class_id;
}

if ($section_id > 0) {
    $where_clauses[] = " s.section_id = ? ";
    $params[] = $section_id;
}

$where_sql = "";
if (count($where_clauses) > 0) {
    $where_sql = " WHERE " . implode(" AND ", $where_clauses);
}

// Fetch total records count for pagination
$total_records = 0;
try {
    $count_sql = "SELECT COUNT(*) FROM `students` s" . $where_sql;
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
    $total_records = $stmt->fetchColumn();
} catch (PDOException $e) {}

$total_pages = ceil($total_records / $limit);
if ($total_pages < 1) $total_pages = 1;
if ($page > $total_pages) $page = $total_pages;

// Fetch students with classes and sections
$students = [];
try {
    $select_sql = "
        SELECT s.*, c.name_bn AS class_name, sec.name_bn AS section_name
        FROM `students` s
        JOIN `classes` c ON s.class_id = c.id
        JOIN `sections` sec ON s.section_id = sec.id
        " . $where_sql . "
        ORDER BY c.numeric_name ASC, s.roll ASC
        LIMIT ? OFFSET ?
    ";
    
    // Bind limit and offset as integers
    $stmt = $pdo->prepare($select_sql);
    
    // Merge parameters
    $param_idx = 1;
    foreach ($params as $param) {
        $stmt->bindValue($param_idx++, $param);
    }
    $stmt->bindValue($param_idx++, $limit, PDO::PARAM_INT);
    $stmt->bindValue($param_idx++, $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $students = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "Query error: " . $e->getMessage();
}
?>

<div class="page-title">
    <span><i class="fa-solid fa-graduation-cap"></i> শিক্ষার্থী তালিকা (Student Registry)</span>
    <div style="display:flex; gap:10px;">
        <button id="exportCsvBtn" data-table-id="studentsTable" data-filename="students_list.csv" class="btn-admin btn-accent"><i class="fa fa-file-csv"></i> CSV এক্সপোর্ট</button>
        <a href="add.php" class="btn-admin btn-primary"><i class="fa fa-user-plus"></i> নতুন শিক্ষার্থী</a>
    </div>
</div>

<!-- Filter Box -->
<div class="admin-card" style="margin-bottom: 25px;">
    <form method="GET" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)) 80px; gap: 15px; align-items: end;">
        <div class="admin-form-group">
            <label for="search">অনুসন্ধান (নাম / রোল)</label>
            <input type="text" id="search" name="search" class="form-control" value="<?php echo escape($search); ?>" placeholder="রোল বা নাম লিখুন">
        </div>
        
        <div class="admin-form-group">
            <label for="class_id">শ্রেণি</label>
            <select id="class_id" name="class_id" class="form-control">
                <option value="">সকল শ্রেণি</option>
                <?php foreach ($classes as $c): ?>
                    <option value="<?php echo $c['id']; ?>" <?php echo $class_id === (int)$c['id'] ? 'selected' : ''; ?>><?php echo escape($c['name_bn']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="admin-form-group">
            <label for="section_id">শাখা</label>
            <select id="section_id" name="section_id" class="form-control">
                <option value="">সকল শাখা</option>
                <?php if ($class_id > 0): 
                    // Fetch sections for active class
                    try {
                        $secs = $pdo->prepare("SELECT * FROM `sections` WHERE `class_id` = ?");
                        $secs->execute([$class_id]);
                        foreach ($secs->fetchAll() as $sc) {
                            $sel = $section_id === (int)$sc['id'] ? 'selected' : '';
                            echo "<option value='{$sc['id']}' {$sel}>{$sc['name_bn']}</option>";
                        }
                    } catch (PDOException $e) {}
                endif; ?>
            </select>
        </div>
        
        <div>
            <button type="submit" class="btn-admin btn-primary" style="width: 100%; height:42px;"><i class="fa fa-search"></i> খুঁজুন</button>
        </div>
    </form>
</div>

<!-- List Table -->
<div class="admin-card">
    <div class="admin-table-responsive">
        <table class="admin-table" id="studentsTable">
            <thead>
                <tr>
                    <th>ছবি</th>
                    <th>রোল</th>
                    <th>নাম (বাংলা)</th>
                    <th>শ্রেণি</th>
                    <th>শাখা</th>
                    <th>লিঙ্গ</th>
                    <th>অভিভাবক</th>
                    <th>মোবাইল নম্বর</th>
                    <th class="actions-cell">অ্যাকশন</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($students)): ?>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td>
                                <?php if (!empty($student['photo']) && file_exists(UPLOAD_DIR . '/' . $student['photo'])): ?>
                                    <img src="<?php echo UPLOAD_URL . '/' . escape($student['photo']); ?>" alt="Student Photo" style="width:40px; height:40px; border-radius:50%; object-fit:cover; border: 1px solid var(--border-color);">
                                <?php else: ?>
                                    <div style="width:40px; height:40px; border-radius:50%; background-color:#e2e8f0; display:inline-flex; align-items:center; justify-content:center; border: 1px solid var(--border-color); margin: 0 auto;"><i class="fa fa-user" style="color:#94a3b8;"></i></div>
                                <?php endif; ?>
                            </td>
                            <td style="font-family: var(--font-en); font-weight: bold;"><?php echo escape($student['roll']); ?></td>
                            <td style="font-weight: bold; text-align: left;"><?php echo escape($student['name_bn']); ?></td>
                            <td><?php echo escape($student['class_name']); ?></td>
                            <td><?php echo escape($student['section_name']); ?></td>
                            <td><?php echo $student['gender'] === 'Male' ? 'ছাত্র' : 'ছাত্রী'; ?></td>
                            <td><?php echo escape($student['guardian_name_bn']); ?></td>
                            <td style="font-family: var(--font-en);"><?php echo escape($student['mobile']); ?></td>
                            <td class="actions-cell">
                                <a href="edit.php?id=<?php echo $student['id']; ?>" class="btn-action edit" title="সম্পাদনা"><i class="fa fa-edit"></i></a>
                                <a href="delete.php?id=<?php echo $student['id']; ?>" class="btn-action delete" title="মুছে ফেলুন" onclick="return confirm('আপনি কি নিশ্চিতভাবে এই শিক্ষার্থীর প্রোফাইলটি মুছে ফেলতে চান?');"><i class="fa fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" style="color: var(--text-muted);">কোনো শিক্ষার্থীর বিবরণ পাওয়া যায়নি।</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination links -->
    <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                <div class="page-item <?php echo $page === $p ? 'active' : ''; ?>">
                    <a href="?page=<?php echo $p; ?>&search=<?php echo urlencode($search); ?>&class_id=<?php echo $class_id; ?>&section_id=<?php echo $section_id; ?>" class="page-link"><?php echo $p; ?></a>
                </div>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/../../includes/admin_footer.php';
?>
