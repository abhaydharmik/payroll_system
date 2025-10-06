<?php
// sidebar.php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!-- Mobile Header -->
<header class="fixed top-0 left-0 right-0 bg-white shadow-md flex items-center justify-between px-4 py-3 md:hidden z-50">
    <!-- Hamburger Button on Left -->
    <button id="sidebarToggle" class="text-gray-800 text-2xl focus:outline-none">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
    </button>
    <h1 class="text-lg font-bold text-gray-800 flex items-center ml-4">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path d="M3 13h8V3H3v10zM13 21h8V11h-8v10zM3 21h8v-6H3v6zM13 3v6h8V3h-8z"/>
        </svg>
        Admin Panel
    </h1>
</header>

<!-- Sidebar -->
<aside id="sidebar" class="fixed inset-y-0 left-0 w-64 bg-white flex flex-col transform -translate-x-full md:translate-x-0 transition-transform duration-300 z-40 shadow-lg">
    <!-- Mobile Close Button -->
    <div class="flex justify-end md:hidden p-4">
        <button id="sidebarClose" class="text-gray-800 text-2xl hover:text-red-600 focus:outline-none">
            <!-- X Icon -->
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <nav class="flex-1 px-4 py-7 mt-4 md:mt-0 space-y-2 overflow-y-auto">
        <?php
        $menuItems = [
            'dashboard.php'       => ['title' => 'Dashboard', 'icon' => '<path d="M3 13h8V3H3v10zM13 21h8V11h-8v10zM3 21h8v-6H3v6zM13 3v6h8V3h-8z"/>' ],
            'employees.php'       => ['title' => 'Employees', 'icon' => '<path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/>' ],
            'attendance.php'      => ['title' => 'Attendance', 'icon' => '<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>' ],
            'leaves.php'          => ['title' => 'Leaves', 'icon' => '<path d="M19 21H5a2 2 0 01-2-2V7a2 2 0 012-2h4l2-3h6a2 2 0 012 2v14a2 2 0 01-2 2z"/>' ],
            'generate_salary.php' => ['title' => 'Generate Salary', 'icon' => '<path d="M12 8c-1.1 0-2 .9-2 2 0 1.5 2 2 2 2s2 .5 2 2c0 1.1-.9 2-2 2m0-8V4m0 16v-4"/><circle cx="12" cy="12" r="10"/>' ],
            'salary_history.php'  => ['title' => 'Salary History', 'icon' => '<path d="M3 3v18h18"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/>' ],
            'departments.php'     => ['title' => 'Departments', 'icon' => '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>' ],
            'designations.php'    => ['title' => 'Designations', 'icon' => '<path d="M6 3h12a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V5a2 2 0 012-2z"/><path d="M6 7h12"/><path d="M6 11h12"/><path d="M6 15h12"/>' ],
        ];

        foreach ($menuItems as $file => $item):
            $isActive = ($currentPage === $file);
            $activeClass = $isActive
                ? 'bg-blue-50 text-blue-600 border border-blue-200'
                : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900';

            echo '<a href="'.$file.'" class="block py-2 px-3 rounded-lg flex items-center '.$activeClass.'">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        '.$item['icon'].'
                    </svg>'.$item['title'].'
                  </a>';
        endforeach;
        ?>
    </nav>

    <!-- Footer -->
    <div class="p-4 border-t mt-4 border-blue-700">
        <p class="text-sm">&copy; <?= date("Y"); ?> <span class="font-semibold">Payroll System</span>. All rights reserved.</p>
    </div>
</aside>

<!-- Overlay for Mobile -->
<div id="overlay" class="fixed inset-0 bg-black opacity-50 hidden z-30 md:hidden"></div>

<!-- Sidebar Toggle Script -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    const toggleBtn = document.getElementById('sidebarToggle');
    const closeBtn = document.getElementById('sidebarClose');

    // Open sidebar
    toggleBtn.addEventListener('click', () => {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');
    });

    // Close sidebar (X button)
    closeBtn.addEventListener('click', () => {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
    });

    // Click overlay to close
    overlay.addEventListener('click', () => {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
    });
});
</script>
