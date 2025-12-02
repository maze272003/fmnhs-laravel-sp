document.addEventListener('DOMContentLoaded', () => {
    // --- Select DOM elements ---
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    const desktopCollapseBtn = document.getElementById('desktop-collapse-btn');
    const contentWrapper = document.getElementById('content-wrapper');
    const navTextElements = document.querySelectorAll('.nav-text');
    const navIcons = document.querySelectorAll('.nav-icon');
    
    // --- Mobile Menu Logic ---
    function toggleMobileMenu() {
        const isClosed = sidebar.classList.contains('-translate-x-full');
        if (isClosed) {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
        } else {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        }
    }

    if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', toggleMobileMenu);
    if(overlay) overlay.addEventListener('click', toggleMobileMenu);

    // --- Desktop Collapse Logic ---
    if (desktopCollapseBtn) {
        desktopCollapseBtn.addEventListener('click', () => {
            sidebar.classList.toggle('lg:w-64');
            sidebar.classList.toggle('lg:w-20');
            contentWrapper.classList.toggle('lg:ml-64');
            contentWrapper.classList.toggle('lg:ml-20');
            navTextElements.forEach(el => el.classList.toggle('lg:hidden'));
            navIcons.forEach(icon => icon.classList.toggle('lg:mx-auto'));

            const icon = desktopCollapseBtn.querySelector('i');
            if (sidebar.classList.contains('lg:w-20')) {
                icon.classList.remove('fa-chevron-left');
                icon.classList.add('fa-chevron-right');
            } else {
                icon.classList.remove('fa-chevron-right');
                icon.classList.add('fa-chevron-left');
            }
        });
    }

    // --- Active Link Highlighting ---
    const currentUrl = window.location.href;
    const navLinks = document.querySelectorAll('.nav-link');

    navLinks.forEach(link => {
        if (link.href === currentUrl) {
            link.classList.add('bg-indigo-50', 'dark:bg-indigo-900/20', 'text-indigo-600', 'dark:text-indigo-400');
            link.classList.remove('hover:bg-gray-50', 'dark:hover:bg-slate-800', 'text-gray-700');
            const icon = link.querySelector('i');
            if(icon) {
                icon.classList.remove('text-gray-500');
                icon.classList.add('text-indigo-600', 'dark:text-indigo-400');
            }
        }
    });

    // --- FIXED LOGOUT LOGIC ---
    const logoutBtn = document.getElementById('logout-btn');

    if (logoutBtn) {
        logoutBtn.addEventListener('click', (e) => {
            e.preventDefault(); 

            // Check if SweetAlert is loaded
            if (typeof Swal === 'undefined') {
                console.error('SweetAlert2 is not loaded!');
                // Fallback if SweetAlert fails
                if(confirm("Are you sure you want to logout?")) {
                    document.getElementById('logout-form').submit();
                }
                return;
            }

            Swal.fire({
                title: 'Are you sure?',
                text: "You will be logged out of the session.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Logout'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('logout-form').submit();
                }
            });
        });
    }
});