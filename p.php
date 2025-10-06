<!-- Mobile Header -->
<header class="fixed top-0 left-0 right-0 bg-white shadow-md flex items-center justify-between px-4 py-3 md:hidden z-50">
    <h1 class="text-lg font-bold text-gray-800 flex items-center"> <i class="fa-solid fa-chart-line mr-2"></i> Admin Panel </h1> <button id="sidebarToggle" class="text-gray-800 text-2xl focus:outline-none"> <i class="fa-solid fa-bars"></i> </button>
</header>
<!-- Sidebar -->
<aside id="sidebar" class="fixed inset-y-0 left-0 w-64 bg-white flex flex-col transform -translate-x-full md:translate-x-0 transition-transform duration-300 z-40"> <!-- <div id="hidden" class="p-6 border-b border-blue-700"> <h1 class="text-2xl font-bold flex items-center"> <i class="fa-solid fa-chart-line mr-2"></i> Admin Panel </h1> </div> -->
    <nav class="flex-1 px-4 py-7 mt-10 space-y-2 overflow-y-auto"> <a href="dashboard.php" class="block py-2 px-3 flex items-center rounded-lg bg-blue-50 text-blue-600 border border-blue-200"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-bar-chart3 w-5 h-5 mr-2">
                <path d="M3 3v18h18"></path>
                <path d="M18 17V9"></path>
                <path d="M13 17V5"></path>
                <path d="M8 17v-3"></path>
            </svg> Dashboard</a> <a href="employees.php" class="block py-2 px-3 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 flex items-center"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users w-5 h-5 mr-2">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg> Employees</a> <a href="attendance.php" class="block py-2 px-3 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 flex items-center"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-calendar w-5 h-5 mr-2">
                <path d="M8 2v4"></path>
                <path d="M16 2v4"></path>
                <rect width="18" height="18" x="3" y="4" rx="2"></rect>
                <path d="M3 10h18"></path>
            </svg> Attendance</a> <a href="leaves.php" class="block py-2 px-3 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 flex items-center"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-text w-5 h-5 mr-2">
                <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"></path>
                <path d="M14 2v4a2 2 0 0 0 2 2h4"></path>
                <path d="M10 9H8"></path>
                <path d="M16 13H8"></path>
                <path d="M16 17H8"></path>
            </svg> Leaves</a> <a href="generate_salary.php" class="block py-2 px-3 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 flex items-center"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-indian-rupee-icon lucide-indian-rupee mr-2">
                <path d="M6 3h12" />
                <path d="M6 8h12" />
                <path d="m6 13 8.5 8" />
                <path d="M6 13h3" />
                <path d="M9 13c6.667 0 6.667-10 0-10" />
            </svg> Generate Salary</a> <a href="salary_history.php" class="block py-2 px-3 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 flex items-center"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-hand-coins-icon lucide-hand-coins mr-2">
                <path d="M11 15h2a2 2 0 1 0 0-4h-3c-.6 0-1.1.2-1.4.6L3 17" />
                <path d="m7 21 1.6-1.4c.3-.4.8-.6 1.4-.6h4c1.1 0 2.1-.4 2.8-1.2l4.6-4.4a2 2 0 0 0-2.75-2.91l-4.2 3.9" />
                <path d="m2 16 6 6" />
                <circle cx="16" cy="9" r="2.9" />
                <circle cx="6" cy="5" r="3" />
            </svg> Salary History</a> <a href="departments.php" class="block py-2 px-3 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 flex items-center"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-building2-icon lucide-building-2 mr-2">
                <path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z" />
                <path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2" />
                <path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2" />
                <path d="M10 6h4" />
                <path d="M10 10h4" />
                <path d="M10 14h4" />
                <path d="M10 18h4" />
            </svg> Departments</a> <a href="designations.php" class="block py-2 px-3 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 flex items-center"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-briefcase-business-icon lucide-briefcase-business mr-2">
                <path d="M12 12h.01" />
                <path d="M16 6V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2" />
                <path d="M22 13a18.15 18.15 0 0 1-20 0" />
                <rect width="20" height="14" x="2" y="6" rx="2" />
            </svg> Designations</a> </nav>
    <div class="p-4 border-t mt-4 border-blue-700">
        <p class="text-sm">&copy; <?php echo date("Y"); ?> <span class="font-semibold">Payroll System</span>. All rights reserved.</p>
    </div>
</aside> <!-- Overlay for mobile -->
<div id="overlay" class="fixed inset-0 bg-black opacity-50 hidden z-30 md:hidden"></div>