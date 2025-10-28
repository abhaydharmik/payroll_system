<?php
// sidebar.php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!-- Sidebar -->
<aside id="sidebar" class="fixed inset-y-0 left-0 w-64 bg-white flex flex-col transform mobile-hidden md:translate-x-0 transition-transform duration-300 z-40 shadow-lg">

    <!-- Logo / Title -->
    <div class="p-2 md:p-4 border-b">
        <h1 class="text-xl font-bold flex items-center text-gray-800">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path d="M3 13h8V3H3v10zM13 21h8V11h-8v10zM3 21h8v-6H3v6zM13 3v6h8V3h-8z"/>
            </svg>
            Admin Panel
        </h1>
    </div>

    <!-- Navigation Menu -->
    <nav class="font-medium flex-1 px-4 py-6 space-y-2 overflow-y-auto">
        <?php
        $menuItems = [
            'dashboard.php'       => ['title' => 'Dashboard', 'icon' => '<path d="M3 13h8V3H3v10zM13 21h8V11h-8v10zM3 21h8v-6H3v6zM13 3v6h8V3h-8z"/>' ],
            'employees.php'       => ['title' => 'Employees', 'icon' => '<path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/>' ],
            'attendance.php'      => ['title' => 'Attendance', 'icon' => '<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>' ],
            'leaves.php'          => ['title' => 'Leaves', 'icon' => '<path d="M19 21H5a2 2 0 01-2-2V7a2 2 0 012-2h4l2-3h6a2 2 0 012 2v14a2 2 0 01-2 2z"/>' ],
            'salary.php' => ['title' => 'Salary', 'icon' => '<path d="M12 8c-1.1 0-2 .9-2 2 0 1.5 2 2 2 2s2 .5 2 2c0 1.1-.9 2-2 2m0-8V4m0 16v-4"/><circle cx="12" cy="12" r="10"/>' ],
            // 'salary_history.php'  => ['title' => 'Salary History', 'icon' => '<path d="M3 3v18h18"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/>' ],
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
                    </svg>
                    '.$item['title'].'
                  </a>';
        endforeach;
        ?>
    </nav>

    <!-- Footer -->
    <div class="p-4 border-t mt-4">
        <p class="text-sm text-gray-500">&copy; <?= date("Y"); ?> <span class="font-semibold text-gray-800">Payroll System</span></p>
    </div>
</aside>
