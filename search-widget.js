// Live Search Widget for News
class NewsSearchWidget {
    constructor(inputSelector, resultsSelector) {
        this.input = document.querySelector(inputSelector);
        this.results = document.querySelector(resultsSelector);
        this.debounceTimer = null;
        this.currentQuery = '';
        
        if (this.input) {
            this.init();
        }
    }
    
    init() {
        // Tạo container kết quả nếu chưa có
        if (!this.results) {
            this.results = document.createElement('div');
            this.results.className = 'search-results';
            this.input.parentNode.appendChild(this.results);
        }
        
        // Event listeners
        this.input.addEventListener('input', (e) => this.handleInput(e));
        this.input.addEventListener('focus', (e) => this.handleFocus(e));
        this.input.addEventListener('blur', (e) => this.handleBlur(e));
        
        // Click outside to close
        document.addEventListener('click', (e) => {
            if (!this.input.contains(e.target) && !this.results.contains(e.target)) {
                this.hideResults();
            }
        });
    }
    
    handleInput(e) {
        const query = e.target.value.trim();
        
        clearTimeout(this.debounceTimer);
        
        if (query.length < 2) {
            this.hideResults();
            return;
        }
        
        this.debounceTimer = setTimeout(() => {
            this.search(query);
        }, 300);
    }
    
    handleFocus(e) {
        const query = e.target.value.trim();
        if (query.length >= 2) {
            this.search(query);
        }
    }
    
    handleBlur(e) {
        // Delay hiding to allow clicking on results
        setTimeout(() => {
            if (!this.results.matches(':hover')) {
                this.hideResults();
            }
        }, 150);
    }
    
    async search(query) {
        if (query === this.currentQuery) return;
        
        this.currentQuery = query;
        this.showLoading();
        
        try {
            const response = await fetch(`api-search.php?q=${encodeURIComponent(query)}&limit=8`);
            const data = await response.json();
            
            if (data.success) {
                this.showResults(data.data, query);
            } else {
                this.showError(data.message);
            }
        } catch (error) {
            console.error('Search error:', error);
            this.showError('Có lỗi xảy ra khi tìm kiếm');
        }
    }
    
    showLoading() {
        this.results.innerHTML = `
            <div class="search-loading">
                <div class="loading-spinner"></div>
                <span>Đang tìm kiếm...</span>
            </div>
        `;
        this.results.classList.add('show');
    }
    
    showResults(news, query) {
        if (news.length === 0) {
            this.results.innerHTML = `
                <div class="search-empty">
                    <span>Không tìm thấy kết quả cho "${query}"</span>
                </div>
            `;
        } else {
            const resultsHTML = news.map(item => `
                <a href="${item.url}" class="search-result-item">
                    <div class="result-image">
                        <img src="${item.image}" alt="${item.title}" onerror="this.src='images/news-default.jpg'">
                    </div>
                    <div class="result-content">
                        <h4>${this.highlightText(item.title, query)}</h4>
                        <p>${this.highlightText(item.summary, query)}</p>
                        <div class="result-meta">
                            <span class="result-category">${item.category_name}</span>
                            <span class="result-date">${this.formatDate(item.published_at)}</span>
                        </div>
                    </div>
                </a>
            `).join('');
            
            this.results.innerHTML = `
                <div class="search-results-header">
                    <span>Kết quả tìm kiếm cho "${query}"</span>
                    <a href="tin-tuc.php?keyword=${encodeURIComponent(query)}" class="view-all">Xem tất cả</a>
                </div>
                <div class="search-results-list">
                    ${resultsHTML}
                </div>
            `;
        }
        
        this.results.classList.add('show');
    }
    
    showError(message) {
        this.results.innerHTML = `
            <div class="search-error">
                <span>❌ ${message}</span>
            </div>
        `;
        this.results.classList.add('show');
    }
    
    hideResults() {
        this.results.classList.remove('show');
        this.currentQuery = '';
    }
    
    highlightText(text, query) {
        if (!query) return text;
        
        const regex = new RegExp(`(${query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
        return text.replace(regex, '<mark>$1</mark>');
    }
    
    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('vi-VN');
    }
}

// CSS Styles
const searchStyles = `
.search-results {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #ddd;
    border-top: none;
    border-radius: 0 0 8px 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    max-height: 400px;
    overflow-y: auto;
    z-index: 1000;
    display: none;
}

.search-results.show {
    display: block;
}

.search-results-header {
    padding: 12px 15px;
    background: #f8f9fa;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.9rem;
}

.search-results-header span {
    color: #666;
    font-weight: 500;
}

.view-all {
    color: #3498db;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.85rem;
}

.view-all:hover {
    text-decoration: underline;
}

.search-result-item {
    display: flex;
    padding: 12px 15px;
    border-bottom: 1px solid #f0f0f0;
    text-decoration: none;
    color: inherit;
    transition: background-color 0.2s;
}

.search-result-item:hover {
    background: #f8f9fa;
}

.search-result-item:last-child {
    border-bottom: none;
}

.result-image {
    width: 60px;
    height: 45px;
    margin-right: 12px;
    flex-shrink: 0;
}

.result-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 4px;
}

.result-content {
    flex: 1;
    min-width: 0;
}

.result-content h4 {
    margin: 0 0 4px 0;
    font-size: 0.9rem;
    font-weight: 600;
    color: #2c3e50;
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.result-content p {
    margin: 0 0 6px 0;
    font-size: 0.8rem;
    color: #666;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.result-meta {
    display: flex;
    gap: 10px;
    font-size: 0.75rem;
    color: #999;
}

.result-category {
    background: #e9ecef;
    padding: 2px 6px;
    border-radius: 10px;
}

.search-loading,
.search-empty,
.search-error {
    padding: 20px;
    text-align: center;
    color: #666;
}

.search-loading {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.loading-spinner {
    width: 16px;
    height: 16px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid #3498db;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

mark {
    background: #fff3cd;
    padding: 1px 2px;
    border-radius: 2px;
}

/* Make search input container relative */
.search-input-container {
    position: relative;
}
`;

// Add styles to head
const styleSheet = document.createElement('style');
styleSheet.textContent = searchStyles;
document.head.appendChild(styleSheet);

// Auto-initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize for main search input
    const mainSearch = document.querySelector('#keyword');
    if (mainSearch) {
        // Wrap input in container if not already
        if (!mainSearch.parentNode.classList.contains('search-input-container')) {
            const container = document.createElement('div');
            container.className = 'search-input-container';
            mainSearch.parentNode.insertBefore(container, mainSearch);
            container.appendChild(mainSearch);
        }
        
        new NewsSearchWidget('#keyword', '.search-results');
    }
});