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

    // Custom Confirmation Modal Integration
    const confirmModal = document.getElementById('customConfirmModal');
    const confirmMessage = document.getElementById('customConfirmMessage');
    const confirmCancelBtn = document.getElementById('customConfirmCancelBtn');
    const confirmOkBtn = document.getElementById('customConfirmOkBtn');
    
    let confirmCallback = null;

    function showCustomConfirm(message, callback) {
        if (!confirmModal) return;
        confirmMessage.textContent = message;
        confirmCallback = callback;
        confirmModal.style.display = 'flex';
    }

    if (confirmModal && confirmCancelBtn && confirmOkBtn) {
        confirmCancelBtn.addEventListener('click', () => {
            confirmModal.style.display = 'none';
            confirmCallback = null;
        });
        
        confirmOkBtn.addEventListener('click', () => {
            confirmModal.style.display = 'none';
            if (confirmCallback) {
                confirmCallback();
            }
        });

        // Intercept all elements using confirm()
        const interceptConfirms = () => {
            const confirmElements = document.querySelectorAll('[onclick*="confirm"]');
            confirmElements.forEach(elem => {
                const onclickAttr = elem.getAttribute('onclick');
                if (onclickAttr) {
                    const match = onclickAttr.match(/confirm\(['"](.+?)['"]\)/);
                    const message = match ? match[1] : "আপনি কি নিশ্চিত?";
                    
                    // Remove inline click handler
                    elem.removeAttribute('onclick');
                    
                    elem.addEventListener('click', (e) => {
                        e.preventDefault();
                        showCustomConfirm(message, () => {
                            if (elem.tagName === 'A' && elem.getAttribute('href')) {
                                window.location.href = elem.getAttribute('href');
                            } else if (elem.getAttribute('type') === 'submit' || elem.tagName === 'BUTTON') {
                                const form = elem.closest('form');
                                if (form) {
                                    if (elem.getAttribute('name')) {
                                        const hiddenInput = document.createElement('input');
                                        hiddenInput.type = 'hidden';
                                        hiddenInput.name = elem.getAttribute('name');
                                        hiddenInput.value = elem.getAttribute('value') || '1';
                                        form.appendChild(hiddenInput);
                                    }
                                    form.submit();
                                }
                            }
                        });
                    });
                }
            });
        };

        // Run interception immediately
        interceptConfirms();

        // Also run it periodically/dynamically in case elements are added or changed
        const observer = new MutationObserver(() => {
            interceptConfirms();
        });
        observer.observe(document.body, { childList: true, subtree: true });
    }

    // Dynamic Image Upload Preview
    const setupImagePreviews = () => {
        const fileInputs = document.querySelectorAll('input[type="file"]');
        fileInputs.forEach(input => {
            if (input.dataset.previewBound) return;
            input.dataset.previewBound = "true";
            
            input.addEventListener('change', () => {
                const files = input.files;
                if (files && files.length > 0) {
                    const file = files[0];
                    
                    // Remove existing preview if any
                    const existingPreview = input.parentNode.querySelector('.img-upload-preview-wrap');
                    if (existingPreview) {
                        existingPreview.remove();
                    }
                    
                    // Hide existing server-side image if any in the same form-group
                    const formGroup = input.closest('.admin-form-group');
                    if (formGroup) {
                        const existingImages = formGroup.querySelectorAll('img');
                        existingImages.forEach(img => {
                            if (!img.closest('.img-upload-preview-wrap')) {
                                const container = img.closest('div');
                                if (container && container !== input.parentNode) {
                                    container.style.display = 'none';
                                } else {
                                    img.style.display = 'none';
                                }
                            }
                        });
                    }
                    
                    if (file.type.startsWith('image/')) {
                        const previewUrl = URL.createObjectURL(file);
                        
                        const previewWrap = document.createElement('div');
                        previewWrap.className = 'img-upload-preview-wrap';
                        previewWrap.style.cssText = 'margin-top: 10px; border: 1px dashed var(--border-color); padding: 5px; border-radius: 8px; max-width: 150px; background: white; display: flex; align-items: center; justify-content: center; height: 150px;';
                        
                        const img = document.createElement('img');
                        img.src = previewUrl;
                        img.style.cssText = 'max-width: 100%; max-height: 100%; object-fit: contain;';
                        
                        previewWrap.appendChild(img);
                        input.parentNode.appendChild(previewWrap);
                    }
                }
            });
        });
    };

    // Run previewer initialization
    setupImagePreviews();

    // Re-initialize preview bindings dynamically
    const observerPreview = new MutationObserver(() => {
        setupImagePreviews();
    });
    observerPreview.observe(document.body, { childList: true, subtree: true });

    // Auto-inject CSRF tokens into POST forms and state-changing links (delete links)
    const injectCsrfTokens = () => {
        if (typeof CSRF_TOKEN === 'undefined') return;
        
        // 1. Inject into all POST forms
        document.querySelectorAll('form').forEach(form => {
            if (form.method && form.method.toLowerCase() === 'post') {
                if (!form.querySelector('input[name="csrf_token"]')) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'csrf_token';
                    input.value = CSRF_TOKEN;
                    form.appendChild(input);
                } else {
                    const input = form.querySelector('input[name="csrf_token"]');
                    if (input && !input.value) {
                        input.value = CSRF_TOKEN;
                    }
                }
            }
        });
        
        // 2. Append token to any delete links (including standalone delete pages or state-changing action parameters)
        document.querySelectorAll('a[href*="delete"]').forEach(link => {
            let href = link.getAttribute('href');
            if (href && !href.startsWith('javascript:') && !href.includes('csrf_token=')) {
                const connector = href.includes('?') ? '&' : '?';
                link.setAttribute('href', href + connector + 'csrf_token=' + CSRF_TOKEN);
            }
        });
    };

    // Run injection immediately
    injectCsrfTokens();

    // Re-run injection dynamically when the DOM changes (e.g. for dynamic or tabbed content)
    const observerCsrf = new MutationObserver(() => {
        injectCsrfTokens();
    });
    observerCsrf.observe(document.body, { childList: true, subtree: true });

    // Global event interceptors as failsafe backup
    if (typeof CSRF_TOKEN !== 'undefined') {
        // Intercept global form submission to ensure token exists
        document.addEventListener('submit', (e) => {
            const form = e.target;
            if (form && form.method && form.method.toLowerCase() === 'post') {
                let tokenInput = form.querySelector('input[name="csrf_token"]');
                if (!tokenInput) {
                    tokenInput = document.createElement('input');
                    tokenInput.type = 'hidden';
                    tokenInput.name = 'csrf_token';
                    tokenInput.value = CSRF_TOKEN;
                    form.appendChild(tokenInput);
                } else if (!tokenInput.value) {
                    tokenInput.value = CSRF_TOKEN;
                }
            }
        });

        // Intercept global clicks on delete links to ensure token is appended
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a');
            if (link) {
                let href = link.getAttribute('href');
                if (href && !href.startsWith('javascript:') && href.includes('delete') && !href.includes('csrf_token=')) {
                    const connector = href.includes('?') ? '&' : '?';
                    link.setAttribute('href', href + connector + 'csrf_token=' + CSRF_TOKEN);
                }
            }
        }, true); // Use capturing phase
    }
});
