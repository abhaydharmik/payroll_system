<?php
// $pageTitle must already be defined in the page before including this

$currentPage = basename($_SERVER['PHP_SELF']);

$breadcrumbs = [
    "dashboard.php" => ["Dashboard"],
    "employees.php" => ["Dashboard", "Employees"],
    "edit_employee.php" => ["Dashboard", "Employees", "Edit Employee"],
    "performance.php" => ["Dashboard", "Employees", "Performance Review"],
    "add_employee.php" => ["Dashboard", "Employees", "Add Employee"],
];
?>
<nav class="text-sm text-gray-600 mt-4 mb-4">
    <ol class="flex items-center space-x-1">
        <?php foreach ($breadcrumbs[$currentPage] as $index => $crumb): ?>
            <li class="flex items-center">
                <?php if ($index < count($breadcrumbs[$currentPage]) - 1): ?>
                    <a href="<?= ($crumb == 'Dashboard') ? 'dashboard.php' : 'employees.php' ?>" class="hover:text-blue-600"><?= $crumb ?></a>
                    <span class="mx-2"> > </span>
                <?php else: ?>
                    <span class="text-gray-900 font-semibold"><?= $crumb ?></span>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ol>
</nav>
