<!-- فایل: footer.php -->
 
<!-- ======================================== -->
<!-- بستن main-wrapper و فوتر -->
<!-- ======================================== -->
    </main>
    
    <footer class="footer" role="contentinfo">
        <div class="footer-content">
            <div class="footer-links" style="margin-bottom: 1rem;">
                <a href="/about" style="color: white; text-decoration: none; margin: 0 10px; opacity: 0.8;">درباره ما</a>
                <a href="/contact" style="color: white; text-decoration: none; margin: 0 10px; opacity: 0.8;">تماس با ما</a>
                <a href="/privacy" style="color: white; text-decoration: none; margin: 0 10px; opacity: 0.8;">حریم خصوصی</a>
                <a href="/terms" style="color: white; text-decoration: none; margin: 0 10px; opacity: 0.8;">شرایط استفاده</a>
            </div>
            
            <p>&copy; <?= date('Y') ?> <a href="/" style="color: #667eea; text-decoration: none;">AI Productivity Strategy</a>. تمامی حقوق محفوظ است.</p>
            
            <div class="social-links">
                <a href="https://linkedin.com/company/yourcompany" target="_blank" rel="noopener noreferrer" aria-label="لینکدین">
                    <i class="fab fa-linkedin" aria-hidden="true"></i>
                </a>
                <a href="https://twitter.com/yourhandle" target="_blank" rel="noopener noreferrer" aria-label="توییتر">
                    <i class="fab fa-twitter" aria-hidden="true"></i>
                </a>
                <a href="https://telegram.me/yourchannel" target="_blank" rel="noopener noreferrer" aria-label="تلگرام">
                    <i class="fab fa-telegram" aria-hidden="true"></i>
                </a>
                <a href="https://instagram.com/yourprofile" target="_blank" rel="noopener noreferrer" aria-label="اینستاگرام">
                    <i class="fab fa-instagram" aria-hidden="true"></i>
                </a>
                <a href="https://youtube.com/yourchannel" target="_blank" rel="noopener noreferrer" aria-label="یوتیوب">
                    <i class="fab fa-youtube" aria-hidden="true"></i>
                </a>
            </div>
        </div>
    </footer>
    
    <!-- ======================================== -->
    <!-- اسکریپت‌ها (بارگذاری در انتها) -->
    <!-- ======================================== -->
    
    <!-- jQuery (اختیاری - از CDN) -->
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script> -->
    
    <!-- اسکریپت اصلی با نسخه‌گذاری -->
    <script src="/assets/js/main.js?v=<?= filemtime(__DIR__ . '/assets/js/main.js') ?>" defer></script>
    
    <!-- ======================================== -->
    <!-- Google Analytics -->
    <!-- ======================================== -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-YOUR_ID"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-YOUR_ID', {
            'page_title': '<?= htmlspecialchars($page_title ?? '') ?>',
            'page_location': '<?= htmlspecialchars($canonical_url ?? '') ?>'
        });
    </script>
    
    <!-- ======================================== -->
    <!-- اسکریپت‌های داخلی برای ناوبری -->
    <!-- ======================================== -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // منوی همبرگری برای موبایل
            const menuToggle = document.getElementById('menuToggle');
            const navMenu = document.getElementById('navMenu');
            
            if (menuToggle && navMenu) {
                menuToggle.addEventListener('click', function() {
                    const isActive = navMenu.classList.toggle('active');
                    this.setAttribute('aria-expanded', isActive);
                    
                    const icon = this.querySelector('i');
                    if (isActive) {
                        icon.className = 'fas fa-times';
                        this.setAttribute('aria-label', 'بستن منو');
                    } else {
                        icon.className = 'fas fa-bars';
                        this.setAttribute('aria-label', 'باز کردن منو');
                    }
                });
            }
            
            // بستن منو با کلیک روی لینک (در موبایل)
            const navLinks = document.querySelectorAll('.nav-menu a');
            navLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth <= 768) {
                        const menu = document.getElementById('navMenu');
                        const toggle = document.getElementById('menuToggle');
                        if (menu && menu.classList.contains('active')) {
                            menu.classList.remove('active');
                            if (toggle) {
                                toggle.setAttribute('aria-expanded', 'false');
                                const icon = toggle.querySelector('i');
                                if (icon) {
                                    icon.className = 'fas fa-bars';
                                }
                            }
                        }
                    }
                });
            });
        });
    </script>
    
    <!-- ======================================== -->
    <!-- تزریق اسکریپت‌های اضافی در صورت نیاز -->
    <!-- ======================================== -->
    <?php if (isset($additional_scripts) && is_array($additional_scripts)): ?>
        <?php foreach ($additional_scripts as $script): ?>
            <script src="<?= htmlspecialchars($script) ?>" defer></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
</body>
</html>