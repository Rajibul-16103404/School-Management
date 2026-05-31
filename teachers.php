<?php
/**
 * Public Teachers & Staff Directory
 * School Management Website
 */

require_once __DIR__ . '/includes/header.php';

// Fetch teachers & staff
$members = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM `teachers` WHERE `status` = 'Active' ORDER BY `joining_date` ASC");
        $members = $stmt->fetchAll();
    } catch (PDOException $e) {
        // Silent catch
    }
}

// Get unique designations and departments for filtering
$departments = array_unique(array_filter(array_column($members, 'department')));
$designations = array_unique(array_filter(array_column($members, 'designation_bn')));
?>

<div class="card" style="margin-bottom: 25px;">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px; border-bottom: 2px solid var(--accent); padding-bottom: 10px; margin-bottom: 20px;">
        <h2 style="font-size:20px; color:var(--primary-dark); border-bottom:none; margin-bottom:0; display:flex; align-items:center; gap:10px;">
            <i class="fa fa-users" style="color: var(--accent);"></i> শিক্ষক ও কর্মকর্তা-কর্মচারী তালিকা
        </h2>
        
        <!-- View Toggle Controls -->
        <div style="display:flex; gap:10px;">
            <button id="btn-grid-view" class="top-bar-btn" style="border:none; cursor:pointer; background:var(--primary); color:white;"><i class="fa fa-th-large"></i> গ্রিড ভিউ</button>
            <button id="btn-list-view" class="top-bar-btn" style="border:none; cursor:pointer; background:#cbd5e1; color:#0f172a;"><i class="fa fa-list"></i> টেবিল ভিউ</button>
        </div>
    </div>

    <!-- Filter Form -->
    <div style="background-color: #f8fafc; border: 1px solid var(--border-color); padding: 15px; border-radius: 8px; margin-bottom: 25px; display:flex; gap:15px; flex-wrap:wrap; align-items:center;">
        <span style="font-weight: bold; font-size:14px;"><i class="fa fa-filter"></i> ফিল্টার করুন:</span>
        
        <select id="filter-dept" style="padding: 8px 12px; border-radius:6px; border:1px solid var(--border-color); font-family:var(--font-bn); font-size:14px; outline:none;">
            <option value="">সকল বিভাগ</option>
            <?php foreach ($departments as $dept): ?>
                <option value="<?php echo escape($dept); ?>"><?php echo escape($dept); ?></option>
            <?php endforeach; ?>
            <option value="Staff">কর্মচারী (Staff)</option>
        </select>
        
        <select id="filter-desig" style="padding: 8px 12px; border-radius:6px; border:1px solid var(--border-color); font-family:var(--font-bn); font-size:14px; outline:none;">
            <option value="">সকল পদবী</option>
            <?php foreach ($designations as $desig): ?>
                <option value="<?php echo escape($desig); ?>"><?php echo escape($desig); ?></option>
            <?php endforeach; ?>
        </select>
        
        <button id="btn-clear-filters" style="background:none; border:none; color:var(--danger); cursor:pointer; font-weight:bold; font-size:14px;"><i class="fa fa-times"></i> ফিল্টার মুছুন</button>
    </div>

    <!-- 1. Grid View Layout -->
    <div id="grid-container" class="cards-grid">
        <?php if (!empty($members)): ?>
            <?php foreach ($members as $m): 
                $is_t = (int)$m['is_teacher'];
                $dept_val = $is_t === 0 ? 'Staff' : escape($m['department'] ?: 'General');
            ?>
                <div class="profile-card member-item" data-dept="<?php echo $dept_val; ?>" data-desig="<?php echo escape($m['designation_bn']); ?>">
                    <div class="profile-img-wrap">
                        <?php if (!empty($m['photo']) && file_exists(UPLOAD_DIR . '/' . $m['photo'])): ?>
                            <img src="<?php echo UPLOAD_URL . '/' . escape($m['photo']); ?>" alt="<?php echo escape($m['name_en']); ?>" class="profile-img">
                        <?php else: ?>
                            <div class="profile-img-placeholder"><i class="fa fa-user"></i></div>
                        <?php endif; ?>
                    </div>
                    <div class="profile-body">
                        <div class="profile-name"><?php echo escape($m['name_bn']); ?></div>
                        <div class="profile-title"><?php echo escape($m['designation_bn']); ?></div>
                        <p style="font-size:13px; color: var(--text-muted); margin-bottom:5px;"><strong>প্রধান বিষয়:</strong> <?php echo escape($m['subject_bn'] ?: 'প্রযোজ্য নয়'); ?></p>
                        <p style="font-size:13px; color: var(--text-muted); margin-bottom:5px;"><strong>যোগ্যতা:</strong> <?php echo escape($m['qualification_bn'] ?: '-'); ?></p>
                        
                        <div class="profile-details">
                            <i class="fa fa-calendar-alt"></i> যোগদানের তারিখ: <?php echo format_date($m['joining_date']); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color: var(--text-muted); text-align: center; grid-column: 1/-1;">কোনো বিবরণী পাওয়া যায়নি।</p>
        <?php endif; ?>
    </div>

    <!-- 2. List View Layout (Hidden by default) -->
    <div id="list-container" class="table-responsive" style="display: none;">
        <table>
            <thead>
                <tr>
                    <th>ছবি</th>
                    <th>নাম</th>
                    <th>পদবী</th>
                    <th>বিভাগ</th>
                    <th>শিক্ষাগত যোগ্যতা</th>
                    <th>যোগদানের তারিখ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($members)): ?>
                    <?php foreach ($members as $m): 
                        $is_t = (int)$m['is_teacher'];
                        $dept_val = $is_t === 0 ? 'Staff' : escape($m['department'] ?: 'General');
                    ?>
                        <tr class="member-row" data-dept="<?php echo $dept_val; ?>" data-desig="<?php echo escape($m['designation_bn']); ?>">
                            <td>
                                <?php if (!empty($m['photo']) && file_exists(UPLOAD_DIR . '/' . $m['photo'])): ?>
                                    <img src="<?php echo UPLOAD_URL . '/' . escape($m['photo']); ?>" alt="Avatar" style="width:40px; height:40px; border-radius:50%; object-fit:cover;">
                                <?php else: ?>
                                    <div style="width:40px; height:40px; border-radius:50%; background-color:#e2e8f0; display:inline-flex; align-items:center; justify-content:center;"><i class="fa fa-user" style="color:#94a3b8;"></i></div>
                                <?php endif; ?>
                            </td>
                            <td style="font-weight: bold;"><?php echo escape($m['name_bn']); ?></td>
                            <td><?php echo escape($m['designation_bn']); ?></td>
                            <td><?php echo $is_t === 0 ? 'স্টাফ (৩য়/৪র্থ শ্রেণি)' : escape($m['department'] ?: 'General'); ?></td>
                            <td><?php echo escape($m['qualification_bn'] ?: '-'); ?></td>
                            <td><?php echo format_date($m['joining_date']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="color: var(--text-muted);">কোনো বিবরণী পাওয়া যায়নি।</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const btnGrid = document.getElementById('btn-grid-view');
    const btnList = document.getElementById('btn-list-view');
    const gridContainer = document.getElementById('grid-container');
    const listContainer = document.getElementById('list-container');
    
    // View Switch Actions
    btnGrid.addEventListener('click', () => {
        btnGrid.style.backgroundColor = 'var(--primary)';
        btnGrid.style.color = 'white';
        btnList.style.backgroundColor = '#cbd5e1';
        btnList.style.color = '#0f172a';
        gridContainer.style.display = 'grid';
        listContainer.style.display = 'none';
    });

    btnList.addEventListener('click', () => {
        btnList.style.backgroundColor = 'var(--primary)';
        btnList.style.color = 'white';
        btnGrid.style.backgroundColor = '#cbd5e1';
        btnGrid.style.color = '#0f172a';
        listContainer.style.display = 'block';
        gridContainer.style.display = 'none';
    });

    // Filtering Actions
    const filterDept = document.getElementById('filter-dept');
    const filterDesig = document.getElementById('filter-desig');
    const clearFilters = document.getElementById('btn-clear-filters');
    
    function applyFilters() {
        const selectedDept = filterDept.value;
        const selectedDesig = filterDesig.value;
        
        // Filter Grid Cards
        const gridItems = document.querySelectorAll('.member-item');
        gridItems.forEach(item => {
            const itemDept = item.getAttribute('data-dept');
            const itemDesig = item.getAttribute('data-desig');
            
            const matchDept = !selectedDept || itemDept === selectedDept;
            const matchDesig = !selectedDesig || itemDesig === selectedDesig;
            
            if (matchDept && matchDesig) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });

        // Filter Table Rows
        const tableRows = document.querySelectorAll('.member-row');
        tableRows.forEach(row => {
            const rowDept = row.getAttribute('data-dept');
            const rowDesig = row.getAttribute('data-desig');
            
            const matchDept = !selectedDept || rowDept === selectedDept;
            const matchDesig = !selectedDesig || rowDesig === selectedDesig;
            
            if (matchDept && matchDesig) {
                row.style.display = 'table-row';
            } else {
                row.style.display = 'none';
            }
        });
    }

    filterDept.addEventListener('change', applyFilters);
    filterDesig.addEventListener('change', applyFilters);
    
    clearFilters.addEventListener('click', () => {
        filterDept.value = '';
        filterDesig.value = '';
        applyFilters();
    });
});
</script>
<?php
require_once __DIR__ . '/includes/footer.php';
?>
