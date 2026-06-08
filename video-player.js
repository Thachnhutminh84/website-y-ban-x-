// Video Player JavaScript
let currentVideoId = null;

// Mở modal và phát video YouTube
function playVideo(videoId, youtubeId) {
    const modal = document.getElementById('videoModal');
    const player = document.getElementById('videoPlayer');
    
    // Tạo iframe YouTube
    player.innerHTML = `
        <iframe 
            src="https://www.youtube.com/embed/${youtubeId}?autoplay=1&rel=0&modestbranding=1" 
            frameborder="0" 
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
            allowfullscreen>
        </iframe>
    `;
    
    // Hiển thị modal
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
    currentVideoId = videoId;
    
    // Cập nhật lượt xem
    updateVideoViews(videoId);
    
    // Lấy thông tin video để hiển thị
    loadVideoInfo(videoId);
}

// Phát video local
function playLocalVideo(videoId, videoUrl) {
    const modal = document.getElementById('videoModal');
    const player = document.getElementById('videoPlayer');
    
    // Tạo video element
    player.innerHTML = `
        <video controls autoplay>
            <source src="${videoUrl}" type="video/mp4">
            <source src="${videoUrl}" type="video/webm">
            <source src="${videoUrl}" type="video/ogg">
            Trình duyệt của bạn không hỗ trợ video HTML5.
        </video>
    `;
    
    // Hiển thị modal
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
    currentVideoId = videoId;
    
    // Cập nhật lượt xem
    updateVideoViews(videoId);
    
    // Lấy thông tin video để hiển thị
    loadVideoInfo(videoId);
}

// Đóng modal video
function closeVideoModal() {
    const modal = document.getElementById('videoModal');
    const player = document.getElementById('videoPlayer');
    
    // Dừng video
    player.innerHTML = '';
    
    // Ẩn modal
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
    currentVideoId = null;
}

// Cập nhật lượt xem video
function updateVideoViews(videoId) {
    fetch('api-update-video-views.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ video_id: videoId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Cập nhật số lượt xem trên giao diện
            const viewElement = document.querySelector(`[data-video-id="${videoId}"] .view-count`);
            if (viewElement && data.views) {
                viewElement.textContent = `👁 ${data.views.toLocaleString()} lượt xem`;
            }
        }
    })
    .catch(error => {
        console.log('Không thể cập nhật lượt xem:', error);
    });
}

// Lấy thông tin video để hiển thị trong modal
function loadVideoInfo(videoId) {
    fetch(`api-get-video-info.php?id=${videoId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success && data.video) {
            document.getElementById('modalVideoTitle').textContent = data.video.title;
            document.getElementById('modalVideoDescription').textContent = data.video.description || '';
        }
    })
    .catch(error => {
        console.log('Không thể tải thông tin video:', error);
    });
}

// Xử lý phím ESC để đóng modal
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape' && currentVideoId) {
        closeVideoModal();
    }
});

// Lazy loading cho thumbnail
document.addEventListener('DOMContentLoaded', function() {
    const thumbnails = document.querySelectorAll('.video-thumbnail img');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.src; // Trigger loading
                observer.unobserve(img);
            }
        });
    });
    
    thumbnails.forEach(img => {
        imageObserver.observe(img);
    });
});

// Smooth scroll cho filter tabs
function scrollToVideos() {
    document.querySelector('.video-grid').scrollIntoView({
        behavior: 'smooth'
    });
}

// Auto-resize video modal dựa trên kích thước màn hình
function resizeVideoModal() {
    const modal = document.querySelector('.video-modal-content');
    const player = document.getElementById('videoPlayer');
    
    if (window.innerWidth <= 768) {
        player.style.height = '250px';
    } else if (window.innerWidth <= 1024) {
        player.style.height = '400px';
    } else {
        player.style.height = '500px';
    }
}

window.addEventListener('resize', resizeVideoModal);

// Preload video thumbnails khi hover
document.addEventListener('DOMContentLoaded', function() {
    const videoCards = document.querySelectorAll('.video-card');
    
    videoCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            const img = this.querySelector('.video-thumbnail img');
            if (img && !img.dataset.preloaded) {
                // Preload higher quality thumbnail
                const highQualityUrl = img.src.replace('hqdefault', 'maxresdefault');
                const preloadImg = new Image();
                preloadImg.onload = function() {
                    img.src = highQualityUrl;
                    img.dataset.preloaded = 'true';
                };
                preloadImg.src = highQualityUrl;
            }
        });
    });
});

// Share video function
function shareVideo(videoId, title) {
    if (navigator.share) {
        navigator.share({
            title: title,
            url: `${window.location.origin}/video.php?id=${videoId}`
        });
    } else {
        // Fallback: copy to clipboard
        const url = `${window.location.origin}/video.php?id=${videoId}`;
        navigator.clipboard.writeText(url).then(() => {
            alert('Đã sao chép link video vào clipboard!');
        });
    }
}

// Video search functionality
function searchVideos(query) {
    const videoCards = document.querySelectorAll('.video-card');
    const searchQuery = query.toLowerCase();
    
    videoCards.forEach(card => {
        const title = card.querySelector('.video-title').textContent.toLowerCase();
        const description = card.querySelector('.video-description')?.textContent.toLowerCase() || '';
        
        if (title.includes(searchQuery) || description.includes(searchQuery)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

// Initialize video page
document.addEventListener('DOMContentLoaded', function() {
    // Add data attributes for easier manipulation
    const videoCards = document.querySelectorAll('.video-card');
    videoCards.forEach((card, index) => {
        const playButton = card.querySelector('.play-button');
        if (playButton) {
            card.dataset.videoIndex = index;
        }
    });
    
    // Initialize modal
    const modal = document.getElementById('videoModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeVideoModal();
            }
        });
    }
    
    console.log('Video page initialized successfully');
});