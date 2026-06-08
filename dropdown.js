function toggleDropdown(event) {
    event.preventDefault();
    event.stopPropagation();
    const button = event.target;
    const dropdown = button.parentElement;
    const isOpen = dropdown.classList.contains('open');
    
    // Đóng tất cả dropdown khác
    document.querySelectorAll('.dropdown.open').forEach(item => {
        item.classList.remove('open');
    });
    
    // Toggle dropdown hiện tại
    if (!isOpen) {
        dropdown.classList.add('open');
    }
}

// Đóng dropdown khi click bên ngoài
document.addEventListener('click', function(event) {
    if (!event.target.closest('.dropdown')) {
        document.querySelectorAll('.dropdown.open').forEach(item => {
            item.classList.remove('open');
        });
    }
});
