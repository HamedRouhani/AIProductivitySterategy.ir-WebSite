// فعال‌سازی منوی موبایل
document.addEventListener('DOMContentLoaded', function() {
    // اضافه کردن کلاس active به منوی جاری
    const currentLocation = location.pathname;
    const menuItems = document.querySelectorAll('.nav-menu a');
    menuItems.forEach(item => {
        if(item.getAttribute('href') === currentLocation.split('/').pop()) {
            item.style.fontWeight = 'bold';
        }
    });   
});