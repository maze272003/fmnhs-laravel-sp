document.addEventListener('DOMContentLoaded', () => {
    // Select DOM elements
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    const desktopCollapseBtn = document.getElementById('desktop-collapse-btn');
    const contentWrapper = document.getElementById('content-wrapper');
    const navTextElements = document.querySelectorAll('.nav-text');
    const navIcons = document.querySelectorAll('.nav-icon');
    
    // --- Mobile Menu Logic ---
    function toggleMobileMenu() {
        // Toggle the transform class to slide in/out
        const isClosed = sidebar.classList.contains('-translate-x-full');
        
        if (isClosed) {
            sidebar.classList.remove('-translate-x-full'); // Open
            overlay.classList.remove('hidden');
        } else {
            sidebar.classList.add('-translate-x-full'); // Close
            overlay.classList.add('hidden');
        }
    }

    if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', toggleMobileMenu);
    if(overlay) overlay.addEventListener('click', toggleMobileMenu);

    // --- Desktop Collapse Logic ---
    if (desktopCollapseBtn) {
        desktopCollapseBtn.addEventListener('click', () => {
            // Toggle sidebar width classes
            sidebar.classList.toggle('lg:w-64');
            sidebar.classList.toggle('lg:w-20');

            // Adjust content margin
            contentWrapper.classList.toggle('lg:ml-64');
            contentWrapper.classList.toggle('lg:ml-20');

            // Toggle Text Visibility
            navTextElements.forEach(el => el.classList.toggle('lg:hidden'));
            
            // Center Icons when collapsed
            navIcons.forEach(icon => icon.classList.toggle('lg:mx-auto'));

            // Rotate/Change Arrow Icon
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
            // Add Active Classes (Indigo theme)
            link.classList.add('bg-indigo-50', 'dark:bg-indigo-900/20', 'text-indigo-600', 'dark:text-indigo-400');
            // Remove Inactive Hover Classes
            link.classList.remove('hover:bg-gray-50', 'dark:hover:bg-slate-800', 'text-gray-700');
            
            // Color the icon
            const icon = link.querySelector('i');
            if(icon) {
                icon.classList.remove('text-gray-500');
                icon.classList.add('text-indigo-600', 'dark:text-indigo-400');
            }
        }
    });

    // --- SweetAlert Logout Confirmation ---
    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', (e) => {
            e.preventDefault();
            Swal.fire({
                title: 'Ready to Leave?',
                text: "You will be logged out of the admin portal.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#4f46e5', // Indigo-600
                cancelButtonColor: '#ef4444', // Red-500
                confirmButtonText: 'Yes, Logout'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('logout-form').submit();
                }
            });
        });
    }
});