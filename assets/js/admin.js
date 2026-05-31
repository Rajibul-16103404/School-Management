/**
 * Admin Panel Javascript
 * School Management Website
 */

document.addEventListener('DOMContentLoaded', () => {
    // Toggle admin sidebar
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.admin-sidebar');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            if (window.innerWidth <= 992) {
                sidebar.classList.toggle('show');
            } else {
                sidebar.classList.toggle('collapsed');
            }
        });
        
        // Close sidebar on tapping outer area in mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 992 && !sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                sidebar.classList.remove('show');
            }
        });
    }

    // Dynamic Class Section Loading (AJAX)
    const classSelect = document.getElementById('class_id');
    const sectionSelect = document.getElementById('section_id');

    if (classSelect && sectionSelect) {
        classSelect.addEventListener('change', function() {
            const classId = this.value;
            sectionSelect.innerHTML = '<option value="">শাখা লোড হচ্ছে...</option>';

            if (!classId) {
                sectionSelect.innerHTML = '<option value="">প্রথমে শ্রেণি নির্বাচন করুন</option>';
                return;
            }

            // Perform fetch call to get_sections API
            const apiUrl = `${window.location.origin}${window.location.pathname.includes('/admin/students') ? '../../api/get_sections.php' : '../api/get_sections.php'}?class_id=${classId}`;
            
            // Adjust API URL relative paths
            let finalUrl = apiUrl;
            if (window.location.pathname.includes('/admin/students/') || window.location.pathname.includes('/admin/teachers/') || window.location.pathname.includes('/admin/classes/') || window.location.pathname.includes('/admin/committee/') || window.location.pathname.includes('/admin/mpo/') || window.location.pathname.includes('/admin/settings/') || window.location.pathname.includes('/admin/users/') || window.location.pathname.includes('/admin/academic/')) {
                finalUrl = '../../api/get_sections.php?class_id=' + classId;
            } else {
                finalUrl = '../api/get_sections.php?class_id=' + classId;
            }

            fetch(finalUrl)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response error');
                    }
                    return response.json();
                })
                .then(data => {
                    sectionSelect.innerHTML = '<option value="">শাখা নির্বাচন করুন</option>';
                    if (data && data.length > 0) {
                        data.forEach(sec => {
                            const option = document.createElement('option');
                            option.value = sec.id;
                            option.textContent = sec.name_bn + ' (' + sec.name_en + ')';
                            sectionSelect.appendChild(option);
                        });
                    } else {
                        sectionSelect.innerHTML = '<option value="">কোনো শাখা পাওয়া যায়নি</option>';
                    }
                })
                .catch(err => {
                    console.error('AJAX error loading sections:', err);
                    sectionSelect.innerHTML = '<option value="">লোড করতে ত্রুটি হয়েছে</option>';
                });
        });
    }

    // CSV Exporter for Administrative Tables
    const exportBtn = document.getElementById('exportCsvBtn');
    if (exportBtn) {
        exportBtn.addEventListener('click', () => {
            const targetTableId = exportBtn.getAttribute('data-table-id');
            const table = document.getElementById(targetTableId);
            if (!table) return;

            let csv = [];
            const rows = table.querySelectorAll('tr');
            
            for (let i = 0; i < rows.length; i++) {
                const row = [], cols = rows[i].querySelectorAll('td, th');
                
                for (let j = 0; j < cols.length; j++) {
                    // Skip action column in rows
                    if (cols[j].classList.contains('actions-cell') || cols[j].textContent.includes('অ্যাকশন') || cols[j].textContent.includes('Action')) {
                        continue;
                    }
                    
                    // Clean content and escape quotes
                    let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, '').replace(/(\s\s+)/gm, ' ');
                    data = data.replace(/"/g, '""');
                    row.push('"' + data + '"');
                }
                if (row.length > 0) {
                    csv.push(row.join(','));
                }
            }

            // Create download link
            const csvContent = 'data:text/csv;charset=utf-8,\uFEFF' + csv.join('\n');
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement('a');
            link.setAttribute('href', encodedUri);
            
            const filename = exportBtn.getAttribute('data-filename') || 'exported_data.csv';
            link.setAttribute('download', filename);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    }
});
