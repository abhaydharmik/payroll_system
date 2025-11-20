<?php
// employee_sidebar.php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!-- Sidebar -->
<aside id="sidebar" class="fixed h-screen inset-y-0 left-0 w-64 bg-white flex flex-col transform mobile-hidden md:translate-x-0 transition-transform duration-300 z-40 shadow-sm">

    <!-- Logo / Title -->
    <div class="p-2 md:p-4 border-b">
        <h1 class="text-xl font-bold flex items-center text-gray-800">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path d="M3 13h8V3H3v10zM13 21h8V11h-8v10zM3 21h8v-6H3v6zM13 3v6h8V3h-8z" />
            </svg>
            Employee Panel
        </h1>
    </div>

    <!-- Navigation Menu -->
    <nav class="font-medium flex-1 px-4 py-6 space-y-2 overflow-y-auto">
        <?php
        $menuItems = [
            'dashboard.php' => [
                'title' => 'Dashboard',
                'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-bar-chart3 w-5 h-5"><path d="M3 3v18h18"></path><path d="M18 17V9"></path><path d="M13 17V5"></path><path d="M8 17v-3"></path></svg>'
            ],
            'profile.php' => [
                'title' => 'My Profile',
                'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user w-5 h-5"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>'
            ],
            'attendance.php' => [
                'title' => 'My Attendance',
                'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-calendar w-5 h-5"><path d="M8 2v4"></path><path d="M16 2v4"></path><rect width="18" height="18" x="3" y="4" rx="2"></rect><path d="M3 10h18"></path></svg>'
            ],
            'leaves.php' => [
                'title' => 'My Leaves',
                'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-text w-5 h-5"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"></path><path d="M14 2v4a2 2 0 0 0 2 2h4"></path><path d="M10 9H8"></path><path d="M16 13H8"></path><path d="M16 17H8"></path></svg>'
            ],
            'salary.php' => [
                'title' => 'Salary Slips',
                'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-dollar-sign w-5 h-5"><line x1="12" x2="12" y1="2" y2="22"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>'
            ],
            'reports.php' => [
                'title' => 'My Reports',
                'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clipboard-plus-icon lucide-clipboard-plus"><rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M9 14h6"/><path d="M12 17v-6"/></svg>'
            ],
        ];

        foreach ($menuItems as $file => $item):
            $isActive = ($currentPage === $file);
            $activeClass = $isActive
                ? 'bg-blue-50 text-blue-600 border border-blue-200'
                : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900';

            echo '<a href="' . $file . '" class="block py-2 px-3 rounded-lg flex items-center ' . $activeClass . '">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        ' . $item['icon'] . '
                    </svg>
                    ' . $item['title'] . '
                  </a>';
        endforeach;
        ?>
    </nav>

    <!-- Footer -->
    <div class="p-4 border-t mt-4">
        <p class="text-sm text-gray-500">
            &copy; <?= date("Y"); ?>
            <span class="font-semibold text-gray-800">Payroll System</span>
        </p>
    </div>
</aside>