/**
 * Content Blocks Admin - JavaScript
 * Rich Text Editor và Content Blocks Management
 */

// ==================== GLOBAL VARIABLES ====================
let allBlocks = [];
let currentPageKey = '';
let customFonts = [];
let editingBlockId = null;

// Page definitions
const PAGE_OPTIONS = [
    { key: 'duc', name: 'Du học Đức', icon: '🇩🇪' },
    { key: 'nhat', name: 'Du học Nhật Bản', icon: '🇯🇵' },
    { key: 'han', name: 'Du học Hàn Quốc', icon: '🇰🇷' },
    { key: 'xkldjp', name: 'XKLĐ Nhật Bản', icon: '🇯🇵' },
    { key: 'xkldhan', name: 'XKLĐ Hàn Quốc', icon: '🇰🇷' },
    { key: 'xklddailoan', name: 'XKLĐ Đài Loan', icon: '🇹🇼' },
    { key: 'xkldchauau', name: 'XKLĐ Châu Âu', icon: '🇪🇺' },
    { key: 'huongnghiep', name: 'Hướng nghiệp', icon: '🎯' },
    { key: 'about', name: 'Về ICOGroup', icon: '🏢' },
    { key: 'contact', name: 'Liên hệ', icon: '📞' },
    { key: 'hoatdong', name: 'Hoạt động', icon: '📸' },
    { key: 'index', name: 'Trang chủ', icon: '🏠' },
];

// ==================== INITIALIZATION ====================
document.addEventListener('DOMContentLoaded', function () {
    // Load fonts on page load
    loadFonts();
});

// ==================== RICH TEXT EDITOR FUNCTIONS ====================

/**
 * Format selected text with command
 */
function formatBlockText(command, value = null) {
    document.execCommand(command, false, value);
    // Keep focus on the editor
    const editor = document.activeElement;
    if (editor && editor.classList.contains('rich-editor')) {
        editor.focus();
    }
}

/**
 * Apply text color
 */
function applyBlockTextColor(color) {
    document.execCommand('foreColor', false, color);
}

/**
 * Apply font family
 */
function applyBlockFont(fontFamily) {
    if (fontFamily) {
        document.execCommand('fontName', false, fontFamily);
    }
}

/**
 * Apply font size
 */
function applyBlockFontSize(size) {
    if (size) {
        document.execCommand('fontSize', false, size);
    }
}

/**
 * Insert link
 */
function insertBlockLink() {
    const url = prompt('Nhập URL:', 'https://');
    if (url) {
        document.execCommand('createLink', false, url);
    }
}

// ==================== FONTS MANAGEMENT ====================

/**
 * Load available fonts
 */
async function loadFonts() {
    try {
        const response = await fetch(`${API_BASE}/fonts_api.php`);
        const result = await response.json();

        if (result.status) {
            customFonts = result.custom_fonts || [];

            // Add Google Fonts to head
            if (result.google_fonts) {
                result.google_fonts.forEach(font => {
                    if (font.url) {
                        const link = document.createElement('link');
                        link.rel = 'stylesheet';
                        link.href = font.url;
                        document.head.appendChild(link);
                    }
                });
            }

            // Add custom fonts @font-face
            customFonts.forEach(font => {
                if (font.url) {
                    const style = document.createElement('style');
                    const format = font.format === 'woff2' ? 'woff2' :
                        font.format === 'woff' ? 'woff' :
                            font.format === 'otf' ? 'opentype' : 'truetype';
                    style.textContent = `
                        @font-face {
                            font-family: '${font.name}';
                            src: url('${font.url}') format('${format}');
                            font-weight: normal;
                            font-style: normal;
                        }
                    `;
                    document.head.appendChild(style);
                }
            });

            // Populate font selects
            populateFontSelects(result);
        }
    } catch (error) {
        console.error('Error loading fonts:', error);
    }
}

/**
 * Populate font select dropdowns
 */
function populateFontSelects(fontData) {
    const selects = document.querySelectorAll('.font-select');

    selects.forEach(select => {
        select.innerHTML = '<option value="">-- Chọn font --</option>';

        // System fonts
        if (fontData.system_fonts && fontData.system_fonts.length > 0) {
            const systemGroup = document.createElement('optgroup');
            systemGroup.label = 'System Fonts';
            fontData.system_fonts.forEach(font => {
                const option = document.createElement('option');
                option.value = font.family;
                option.textContent = font.name;
                option.style.fontFamily = font.family;
                systemGroup.appendChild(option);
            });
            select.appendChild(systemGroup);
        }

        // Google fonts
        if (fontData.google_fonts && fontData.google_fonts.length > 0) {
            const googleGroup = document.createElement('optgroup');
            googleGroup.label = 'Google Fonts';
            fontData.google_fonts.forEach(font => {
                const option = document.createElement('option');
                option.value = font.family;
                option.textContent = font.name;
                option.style.fontFamily = font.family;
                googleGroup.appendChild(option);
            });
            select.appendChild(googleGroup);
        }

        // Custom fonts
        if (fontData.custom_fonts && fontData.custom_fonts.length > 0) {
            const customGroup = document.createElement('optgroup');
            customGroup.label = 'Custom Fonts';
            fontData.custom_fonts.forEach(font => {
                const option = document.createElement('option');
                option.value = font.family;
                option.textContent = font.name;
                option.style.fontFamily = font.family;
                customGroup.appendChild(option);
            });
            select.appendChild(customGroup);
        }
    });
}

// ==================== CONTENT BLOCKS MANAGEMENT ====================

/**
 * Load content blocks for a page
 */
async function loadContentBlocks(pageKey = '') {
    currentPageKey = pageKey;
    const container = document.getElementById('blocksContainer');

    if (!container) return;

    container.innerHTML = '<div class="loading"><div class="spinner"></div><p>Đang tải...</p></div>';

    try {
        let url = `${API_BASE}/content_blocks_api.php`;
        if (pageKey) {
            url += `?page=${encodeURIComponent(pageKey)}`;
        }

        const response = await fetch(url);
        const result = await response.json();

        if (result.status) {
            allBlocks = result.data || [];
            renderBlocks();
        } else {
            container.innerHTML = '<div class="empty-state"><span class="material-icons-outlined">error</span><h3>Lỗi tải dữ liệu</h3></div>';
        }
    } catch (error) {
        console.error('Error loading blocks:', error);
        container.innerHTML = '<div class="empty-state"><span class="material-icons-outlined">error</span><h3>Lỗi kết nối</h3></div>';
    }
}

/**
 * Render blocks grid
 */
function renderBlocks() {
    const container = document.getElementById('blocksContainer');
    if (!container) return;

    if (allBlocks.length === 0) {
        container.innerHTML = `
            <div class="blocks-empty">
                <span class="material-icons-outlined icon">widgets</span>
                <h3>Chưa có content block nào</h3>
                <p>${currentPageKey ? 'Bấm nút "Thêm Block" để tạo nội dung mới' : 'Vui lòng chọn một trang để quản lý'}</p>
                ${currentPageKey ? '<button class="btn btn-primary" onclick="openAddBlockModal()"><span class="material-icons-outlined">add</span> Thêm Block đầu tiên</button>' : ''}
            </div>
        `;
        return;
    }

    container.innerHTML = allBlocks.map(block => `
        <div class="block-card" data-id="${block.id}">
            <div class="block-card-header">
                <h4>
                    <span class="drag-handle" title="Kéo để sắp xếp">
                        <span class="material-icons-outlined">drag_indicator</span>
                    </span>
                    <span class="block-order-badge">${block.block_order}</span>
                    ${block.title ? stripHtml(block.title).substring(0, 50) + '...' : 'Block #' + block.id}
                </h4>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span class="block-type-badge">${block.block_type}</span>
                    <div class="block-actions">
                        <button class="action-btn edit" onclick="openEditBlockModal(${block.id})" title="Chỉnh sửa">
                            <span class="material-icons-outlined">edit</span>
                        </button>
                        <button class="action-btn delete" onclick="deleteBlock(${block.id})" title="Xóa">
                            <span class="material-icons-outlined">delete</span>
                        </button>
                    </div>
                </div>
            </div>
            <div class="block-card-body">
                ${block.image_url ? `<img src="${block.image_url}" class="block-preview-image" alt="">` : ''}
                ${block.title ? `<div class="block-preview-title">${block.title}</div>` : ''}
                ${block.content ? `<div class="block-preview-content">${block.content}</div>` : '<p style="color: var(--text-muted); font-style: italic;">Chưa có nội dung</p>'}
            </div>
            <div class="block-card-footer">
                <span>
                    ${block.updated_by ? `<span class="material-icons-outlined" style="font-size: 14px; vertical-align: middle;">person</span> ${block.updated_by}` : ''}
                    ${block.updated_at ? ` • ${formatDate(block.updated_at)}` : ''}
                </span>
                <span>Page: <strong>${block.page_key}</strong></span>
            </div>
        </div>
    `).join('');
}

/**
 * Strip HTML tags
 */
function stripHtml(html) {
    const tmp = document.createElement('div');
    tmp.innerHTML = html;
    return tmp.textContent || tmp.innerText || '';
}

/**
 * Format date
 */
function formatDate(dateStr) {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    return date.toLocaleDateString('vi-VN') + ' ' + date.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
}

// ==================== MODAL FUNCTIONS ====================

/**
 * Open add block modal
 */
function openAddBlockModal() {
    if (!currentPageKey) {
        showToast('Vui lòng chọn một trang trước', 'error');
        return;
    }

    editingBlockId = null;
    document.getElementById('blockModalTitle').textContent = 'Thêm Content Block mới';
    document.getElementById('blockId').value = '';
    document.getElementById('blockPageKey').value = currentPageKey;
    document.getElementById('blockType').value = 'section';
    document.getElementById('blockOrder').value = allBlocks.length + 1;
    document.getElementById('blockTitleEditor').innerHTML = '';
    document.getElementById('blockImageUrl').value = '';
    document.getElementById('blockImagePreview').innerHTML = '<span class="placeholder"><span class="material-icons-outlined">image</span>Preview</span>';
    document.getElementById('blockContentEditor').innerHTML = '';
    document.getElementById('blockTrackingInfo').style.display = 'none';

    document.getElementById('blockModal').style.display = 'block';
}

/**
 * Open edit block modal
 */
function openEditBlockModal(id) {
    const block = allBlocks.find(b => b.id == id);
    if (!block) return;

    editingBlockId = id;
    document.getElementById('blockModalTitle').textContent = 'Chỉnh sửa Content Block';
    document.getElementById('blockId').value = block.id;
    document.getElementById('blockPageKey').value = block.page_key;
    document.getElementById('blockType').value = block.block_type || 'section';
    document.getElementById('blockOrder').value = block.block_order;
    document.getElementById('blockTitleEditor').innerHTML = block.title || '';
    document.getElementById('blockImageUrl').value = block.image_url || '';

    // Image preview
    const previewBox = document.getElementById('blockImagePreview');
    if (block.image_url) {
        previewBox.innerHTML = `<img src="${block.image_url}" alt="">`;
    } else {
        previewBox.innerHTML = '<span class="placeholder"><span class="material-icons-outlined">image</span>Preview</span>';
    }

    document.getElementById('blockContentEditor').innerHTML = block.content || '';

    // Tracking info
    if (block.updated_by || block.updated_at) {
        document.getElementById('blockTrackingInfo').style.display = 'flex';
        document.getElementById('blockUpdatedBy').textContent = block.updated_by || 'N/A';
        document.getElementById('blockUpdatedAt').textContent = formatDate(block.updated_at);
    } else {
        document.getElementById('blockTrackingInfo').style.display = 'none';
    }

    document.getElementById('blockModal').style.display = 'block';
}

/**
 * Save block
 */
async function saveBlock() {
    const id = document.getElementById('blockId').value;
    const pageKey = document.getElementById('blockPageKey').value;
    const blockType = document.getElementById('blockType').value;
    const blockOrder = parseInt(document.getElementById('blockOrder').value) || 0;
    const title = document.getElementById('blockTitleEditor').innerHTML;
    const imageUrl = document.getElementById('blockImageUrl').value;
    const content = document.getElementById('blockContentEditor').innerHTML;

    if (!pageKey) {
        showToast('Vui lòng chọn trang', 'error');
        return;
    }

    const data = {
        page_key: pageKey,
        block_type: blockType,
        block_order: blockOrder,
        title: title,
        image_url: imageUrl,
        content: content
    };

    if (id) {
        data.id = parseInt(id);
    }

    try {
        const response = await fetch(`${API_BASE}/content_blocks_api.php`, {
            method: id ? 'PUT' : 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CSRF_TOKEN
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.status) {
            closeModal('blockModal');
            showToast(id ? 'Cập nhật thành công!' : 'Tạo block thành công!', 'success');
            loadContentBlocks(currentPageKey);
        } else {
            showToast(result.message || 'Lỗi lưu block', 'error');
        }
    } catch (error) {
        console.error('Error saving block:', error);
        showToast('Lỗi kết nối', 'error');
    }
}

/**
 * Delete block
 */
async function deleteBlock(id) {
    if (!confirm('Bạn có chắc muốn xóa block này?')) return;

    try {
        const response = await fetch(`${API_BASE}/content_blocks_api.php?id=${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-Token': CSRF_TOKEN
            }
        });

        const result = await response.json();

        if (result.status) {
            showToast('Xóa thành công!', 'success');
            loadContentBlocks(currentPageKey);
        } else {
            showToast(result.message || 'Lỗi xóa block', 'error');
        }
    } catch (error) {
        console.error('Error deleting block:', error);
        showToast('Lỗi kết nối', 'error');
    }
}

// ==================== IMAGE UPLOAD ====================

/**
 * Preview block image from URL
 */
function previewBlockImageUrl() {
    const url = document.getElementById('blockImageUrl').value;
    const previewBox = document.getElementById('blockImagePreview');

    if (url) {
        previewBox.innerHTML = `<img src="${url}" alt="" onerror="this.parentElement.innerHTML='<span class=\\'placeholder\\'><span class=\\'material-icons-outlined\\'>broken_image</span>Lỗi URL</span>'">`;
    } else {
        previewBox.innerHTML = '<span class="placeholder"><span class="material-icons-outlined">image</span>Preview</span>';
    }
}

/**
 * Upload block image
 */
async function uploadBlockImage(input) {
    if (!input.files || !input.files[0]) return;

    const formData = new FormData();
    formData.append('image', input.files[0]);

    try {
        showToast('Đang tải lên...', 'success');

        const response = await fetch(`${API_BASE}/upload_image.php`, {
            method: 'POST',
            headers: {
                'X-CSRF-Token': CSRF_TOKEN
            },
            body: formData
        });

        const result = await response.json();

        if (result.status && result.url) {
            document.getElementById('blockImageUrl').value = result.url;
            previewBlockImageUrl();
            showToast('Upload thành công!', 'success');
        } else {
            showToast(result.message || 'Lỗi upload', 'error');
        }
    } catch (error) {
        console.error('Error uploading:', error);
        showToast('Lỗi kết nối', 'error');
    }

    input.value = '';
}

// ==================== EDITOR TOOLBAR HTML ====================

/**
 * Generate editor toolbar HTML
 */
function getEditorToolbarHTML(editorId) {
    return `
        <div class="editor-toolbar">
            <div class="toolbar-group">
                <button type="button" onclick="formatBlockText('bold')" title="In đậm (Ctrl+B)">
                    <span class="material-icons-outlined">format_bold</span>
                </button>
                <button type="button" onclick="formatBlockText('italic')" title="In nghiêng (Ctrl+I)">
                    <span class="material-icons-outlined">format_italic</span>
                </button>
                <button type="button" onclick="formatBlockText('underline')" title="Gạch chân (Ctrl+U)">
                    <span class="material-icons-outlined">format_underlined</span>
                </button>
                <button type="button" onclick="formatBlockText('strikeThrough')" title="Gạch ngang">
                    <span class="material-icons-outlined">strikethrough_s</span>
                </button>
            </div>
            
            <div class="toolbar-divider"></div>
            
            <div class="color-picker-wrapper">
                <input type="color" id="${editorId}Color" value="#000000" onchange="applyBlockTextColor(this.value)" title="Màu chữ">
                <div class="color-picker-preview" onclick="document.getElementById('${editorId}Color').click()">
                    <span class="material-icons-outlined">format_color_text</span>
                </div>
            </div>
            
            <select class="font-select" onchange="applyBlockFont(this.value)" title="Font chữ">
                <option value="">-- Font --</option>
            </select>
            
            <select class="font-size-select" onchange="applyBlockFontSize(this.value)" title="Cỡ chữ">
                <option value="">Cỡ</option>
                <option value="1">Rất nhỏ</option>
                <option value="2">Nhỏ</option>
                <option value="3">Vừa</option>
                <option value="4">TB</option>
                <option value="5">Lớn</option>
                <option value="6">Rất lớn</option>
                <option value="7">Cực lớn</option>
            </select>
            
            <div class="toolbar-divider"></div>
            
            <div class="toolbar-group">
                <button type="button" onclick="formatBlockText('justifyLeft')" title="Căn trái">
                    <span class="material-icons-outlined">format_align_left</span>
                </button>
                <button type="button" onclick="formatBlockText('justifyCenter')" title="Căn giữa">
                    <span class="material-icons-outlined">format_align_center</span>
                </button>
                <button type="button" onclick="formatBlockText('justifyRight')" title="Căn phải">
                    <span class="material-icons-outlined">format_align_right</span>
                </button>
            </div>
            
            <div class="toolbar-divider"></div>
            
            <button type="button" onclick="insertBlockLink()" title="Chèn liên kết">
                <span class="material-icons-outlined">link</span>
            </button>
        </div>
    `;
}
