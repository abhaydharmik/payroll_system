<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set default page title if not already defined
$pageTitle = isset($pageTitle) ? $pageTitle : 'Dashboard';

// Example: replace with your session or DB data
// $emp = [
//     'name' => $_SESSION['name'] ?? 'Admin'
// ];
?>

<!-- Header -->
<header class="fixed top-0 left-0 right-0 md:left-64 bg-white shadow flex justify-between items-center px-4 py-3 z-40">
    <!-- Left: Title -->
    <div class="flex items-center space-x-3">
        <button id="sidebarToggle" class="md:hidden text-gray-700 focus:outline-none">
            <i class="fa-solid fa-bars text-xl"></i>
        </button>
        <h1 class="text-lg font-semibold text-gray-700">
            <?= htmlspecialchars($pageTitle) ?>
        </h1>
    </div>

    <!-- Right: User + Logout -->
    <div class="flex items-center space-x-2">
        <div class="flex items-center space-x-2 mr-2">
            <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
                <span class="text-sm font-medium text-white">
                    <?= strtoupper(substr($emp['name'], 0, 1)) ?>
                </span>
            </div>
            <p class="text-gray-900 text-sm font-medium max-[350px]:hidden">
                <?= htmlspecialchars($emp['name']); ?>
            </p>
        </div>
        <a href="../logout.php" class="text-red-600 hover:text-red-800" title="Logout">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                <polyline points="16 17 21 12 16 7" />
                <line x1="21" y1="12" x2="9" y2="12" />
            </svg>
        </a>
    </div>
</header>