<?php
include_once 'includes/content_helper.php';
$pageTitle = "Tìm kiếm";
$pageDescription = "Tìm kiếm tin tức và chương trình du học, xuất khẩu lao động tại ICOGroup.";
$searchQuery = htmlspecialchars($_GET['q'] ?? '');
include 'includes/header.php';
?>

<!-- PAGE BANNER -->
<section class="page-banner" style="background: linear-gradient(135deg, #2563EB, #3B82F6);">
    <h1>🔍 Tìm Kiếm</h1>
    <p>Tìm kiếm tin tức và chương trình</p>
    <div class="breadcrumb">
        <a href="index.php">Trang chủ</a>
        <span>/</span>
        <span>Tìm kiếm</span>
    </div>
</section>

<!-- SEARCH SECTION -->
<section class="section search-section">
    <div class="container">
        <!-- Search Form -->
        <div class="search-form-container">
            <form class="search-form" id="searchForm" action="" method="GET">
                <div class="search-input-wrapper">
                    <span class="search-icon">🔍</span>
                    <input type="text" 
                           id="searchInput" 
                           name="q" 
                           value="<?php echo $searchQuery; ?>" 
                           placeholder="Nhập từ khóa tìm kiếm (ví dụ: du học Nhật, XKLĐ Hàn...)"
                           autocomplete="off"
                           required
                           minlength="2">
                    <button type="submit" class="search-btn">Tìm kiếm</button>
                </div>
                <div class="search-filters">
                    <label class="filter-option">
                        <input type="radio" name="type" value="all" checked> Tất cả
                    </label>
                    <label class="filter-option">
                        <input type="radio" name="type" value="news"> Tin tức
                    </label>
                    <label class="filter-option">
                        <input type="radio" name="type" value="programs"> Chương trình
                    </label>
                </div>
            </form>
        </div>

        <!-- Search Results -->
        <div id="searchResults" class="search-results">
            <?php if (!empty($searchQuery)): ?>
                <div class="results-loading">
                    <div class="spinner"></div>
                    <p>Đang tìm kiếm...</p>
                </div>
            <?php else: ?>
                <div class="search-placeholder">
                    <div class="placeholder-icon">🔎</div>
                    <h3>Nhập từ khóa để tìm kiếm</h3>
                    <p>Bạn có thể tìm kiếm tin tức, chương trình du học, xuất khẩu lao động...</p>
                    <div class="search-suggestions">
                        <span>Gợi ý:</span>
                        <a href="?q=du học Nhật" class="suggestion-tag">Du học Nhật</a>
                        <a href="?q=XKLĐ Hàn Quốc" class="suggestion-tag">XKLĐ Hàn Quốc</a>
                        <a href="?q=học bổng" class="suggestion-tag">Học bổng</a>
                        <a href="?q=Đức" class="suggestion-tag">Du học Đức</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<style>
    .search-section {
        min-height: 60vh;
    }

    .search-form-container {
        max-width: 800px;
        margin: 0 auto 40px;
    }

    .search-form {
        background: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    }

    .search-input-wrapper {
        display: flex;
        align-items: center;
        gap: 15px;
        background: #F8FAFC;
        border: 2px solid #E2E8F0;
        border-radius: 15px;
        padding: 8px 15px;
        transition: all 0.3s ease;
    }

    .search-input-wrapper:focus-within {
        border-color: #2563EB;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
    }

    .search-icon {
        font-size: 24px;
    }

    .search-input-wrapper input {
        flex: 1;
        border: none;
        background: none;
        font-size: 16px;
        padding: 12px 0;
        outline: none;
        font-family: inherit;
    }

    .search-btn {
        background: linear-gradient(135deg, #2563EB, #3B82F6);
        color: white;
        border: none;
        padding: 14px 32px;
        border-radius: 10px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .search-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
    }

    .search-filters {
        display: flex;
        gap: 20px;
        margin-top: 15px;
        justify-content: center;
    }

    .filter-option {
        display: flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        color: #64748B;
        font-size: 14px;
        transition: color 0.2s;
    }

    .filter-option:hover {
        color: #2563EB;
    }

    .filter-option input[type="radio"] {
        accent-color: #2563EB;
    }

    /* Results */
    .search-results {
        max-width: 900px;
        margin: 0 auto;
    }

    .results-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid #E2E8F0;
    }

    .results-header h2 {
        font-size: 20px;
        color: #1E293B;
    }

    .results-count {
        color: #64748B;
        font-size: 14px;
    }

    .result-item {
        display: flex;
        gap: 20px;
        background: white;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .result-item:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .result-image {
        width: 150px;
        height: 100px;
        border-radius: 10px;
        overflow: hidden;
        flex-shrink: 0;
    }

    .result-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .result-content {
        flex: 1;
    }

    .result-type {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        margin-bottom: 8px;
    }

    .result-type.news {
        background: #DBEAFE;
        color: #2563EB;
    }

    .result-type.program {
        background: #D1FAE5;
        color: #059669;
    }

    .result-item h3 {
        font-size: 18px;
        color: #1E293B;
        margin-bottom: 8px;
        transition: color 0.2s;
    }

    .result-item:hover h3 {
        color: #2563EB;
    }

    .result-item p {
        color: #64748B;
        font-size: 14px;
        line-height: 1.6;
    }

    /* Placeholder & Loading */
    .search-placeholder, .results-loading {
        text-align: center;
        padding: 60px 20px;
        color: #64748B;
    }

    .placeholder-icon {
        font-size: 60px;
        margin-bottom: 20px;
    }

    .search-placeholder h3 {
        font-size: 24px;
        color: #1E293B;
        margin-bottom: 10px;
    }

    .search-suggestions {
        margin-top: 25px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .search-suggestions span {
        color: #94A3B8;
    }

    .suggestion-tag {
        background: #F1F5F9;
        color: #2563EB;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 14px;
        transition: all 0.2s;
    }

    .suggestion-tag:hover {
        background: #2563EB;
        color: white;
    }

    .spinner {
        width: 40px;
        height: 40px;
        border: 4px solid #E2E8F0;
        border-top-color: #2563EB;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
        margin: 0 auto 20px;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    .no-results {
        text-align: center;
        padding: 60px 20px;
    }

    .no-results-icon {
        font-size: 60px;
        margin-bottom: 20px;
    }

    .no-results h3 {
        font-size: 20px;
        color: #1E293B;
        margin-bottom: 10px;
    }

    .no-results p {
        color: #64748B;
    }

    @media (max-width: 768px) {
        .search-form {
            padding: 20px;
        }

        .search-input-wrapper {
            flex-wrap: wrap;
        }

        .search-btn {
            width: 100%;
            margin-top: 10px;
        }

        .result-item {
            flex-direction: column;
        }

        .result-image {
            width: 100%;
            height: 180px;
        }

        .search-filters {
            flex-wrap: wrap;
            gap: 10px;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchQuery = '<?php echo addslashes($searchQuery); ?>';
    
    if (searchQuery.length >= 2) {
        performSearch(searchQuery);
    }

    // Auto-search on input with debounce
    let searchTimeout;
    const searchInput = document.getElementById('searchInput');
    
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        if (this.value.length >= 2) {
            searchTimeout = setTimeout(() => {
                performSearch(this.value);
            }, 500);
        }
    });

    // Form submit
    document.getElementById('searchForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const query = searchInput.value.trim();
        if (query.length >= 2) {
            // Update URL
            window.history.pushState({}, '', '?q=' + encodeURIComponent(query));
            performSearch(query);
        }
    });
});

async function performSearch(query) {
    const resultsContainer = document.getElementById('searchResults');
    const selectedType = document.querySelector('input[name="type"]:checked')?.value || 'all';
    
    // Show loading
    resultsContainer.innerHTML = `
        <div class="results-loading">
            <div class="spinner"></div>
            <p>Đang tìm kiếm...</p>
        </div>
    `;

    try {
        const response = await fetch(`../backend_api/search_api.php?q=${encodeURIComponent(query)}&type=${selectedType}`);
        const result = await response.json();

        if (result.status && result.data.length > 0) {
            let html = `
                <div class="results-header">
                    <h2>Kết quả tìm kiếm cho "${query}"</h2>
                    <span class="results-count">${result.total} kết quả</span>
                </div>
            `;

            result.data.forEach(item => {
                const typeLabel = item.result_type === 'news' ? 'Tin tức' : 'Chương trình';
                const typeClass = item.result_type;
                const imageUrl = item.image_url || 'https://via.placeholder.com/150x100?text=No+Image';
                
                html += `
                    <div class="result-item" onclick="window.location='${item.url}'">
                        <div class="result-image">
                            <img src="${imageUrl}" alt="${item.title}">
                        </div>
                        <div class="result-content">
                            <span class="result-type ${typeClass}">${typeLabel}</span>
                            <h3>${item.title}</h3>
                            <p>${item.summary}</p>
                        </div>
                    </div>
                `;
            });

            resultsContainer.innerHTML = html;
        } else {
            resultsContainer.innerHTML = `
                <div class="no-results">
                    <div class="no-results-icon">😔</div>
                    <h3>Không tìm thấy kết quả</h3>
                    <p>Không có kết quả nào phù hợp với "${query}". Vui lòng thử từ khóa khác.</p>
                </div>
            `;
        }
    } catch (error) {
        resultsContainer.innerHTML = `
            <div class="no-results">
                <div class="no-results-icon">⚠️</div>
                <h3>Có lỗi xảy ra</h3>
                <p>Không thể thực hiện tìm kiếm. Vui lòng thử lại sau.</p>
            </div>
        `;
    }
}
</script>

<?php include 'includes/footer.php'; ?>
