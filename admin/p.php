<?php
require '../config.php';
require '../includes/auth.php';
checkRole('admin');

$emp = $_SESSION['user'];

$sql = "SELECT s.id, u.name, s.month, s.basic, s.overtime_hours, s.overtime_rate, s.deductions, s.total, s.generated_at
        FROM salaries s 
        JOIN users u ON s.user_id=u.id 
        ORDER BY s.generated_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="p-4">

 <?php include_once '../includes/sidebar.php'; ?>

  <div class="overflow-x-auto shadow-sm rounded-lg border border-gray-200">
    <table class="min-w-max table-auto border-collapse">
      <thead class="bg-indigo-600 text-white">
        <tr>
          <th class="px-4 py-3 text-left">ID</th>
          <th class="px-4 py-3 text-left">Employee</th>
          <th class="px-4 py-3 text-left">Month</th>
          <th class="px-4 py-3 text-left">Basic</th>
          <th class="px-4 py-3 text-left">Overtime</th>
          <th class="px-4 py-3 text-left">Deductions</th>
          <th class="px-4 py-3 text-left">Total</th>
          <th class="px-4 py-3 text-left">Generated</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-200">
        <?php if ($result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr class="hover:bg-gray-50 transition">
              <td class="px-4 py-3"><?= $row['id'] ?></td>
              <td class="px-4 py-3 font-medium text-gray-800"><?= htmlspecialchars($row['name']) ?></td>
              <td class="px-4 py-3"><?= htmlspecialchars($row['month']) ?></td>
              <td class="px-4 py-3 text-gray-800">₹<?= number_format($row['basic'], 2) ?></td>
              <td class="px-4 py-3 text-gray-700">
                <?= $row['overtime_hours'] ?> hrs
                <div class="text-sm text-gray-500">@ ₹<?= number_format($row['overtime_rate'], 2) ?></div>
              </td>
              <td class="px-4 py-3 text-red-600 font-medium">-₹<?= number_format($row['deductions'], 2) ?></td>
              <td class="px-4 py-3 text-green-600 font-semibold">₹<?= number_format($row['total'], 2) ?></td>
              <td class="px-4 py-3 text-sm text-gray-500"><?= date("d M Y, h:i A", strtotime($row['generated_at'])) ?></td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="8" class="text-center py-6 text-gray-500">No salary records found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <?php
  $data = [
    ['name' => 'John Doe', 'email' => 'john@example.com', 'role' => 'Admin'],
    ['name' => 'Jane Smith', 'email' => 'jane@example.com', 'role' => 'Editor'],
    ['name' => 'Peter Jones', 'email' => 'peter@example.com', 'role' => 'Viewer'],
  ];
  ?>

  <div class="overflow-x-auto">
    <table class="min-w-full bg-white border border-gray-200">
      <thead>
        <tr>
          <th class="py-2 px-4 border-b border-gray-200 bg-gray-100 text-left text-sm font-semibold text-gray-600">Name</th>
          <th class="py-2 px-4 border-b border-gray-200 bg-gray-100 text-left text-sm font-semibold text-gray-600">Email</th>
          <th class="py-2 px-4 border-b border-gray-200 bg-gray-100 text-left text-sm font-semibold text-gray-600">Role</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($data as $row): ?>
          <tr>
            <td class="py-2 px-4 border-b border-gray-200"><?php echo htmlspecialchars($row['name']); ?></td>
            <td class="py-2 px-4 border-b border-gray-200"><?php echo htmlspecialchars($row['email']); ?></td>
            <td class="py-2 px-4 border-b border-gray-200"><?php echo htmlspecialchars($row['role']); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  Edit
  <div class="relative flex flex-col w-full h-full overflow-scroll text-gray-700 bg-white shadow-md rounded-lg bg-clip-border">
    <table class="w-full text-left table-auto min-w-max text-slate-800">
      <thead>
        <tr class="text-slate-500 border-b border-slate-300 bg-slate-50">
          <th class="p-4">
            <p class="text-sm leading-none font-normal">
              Project Name
            </p>
          </th>
          <th class="p-4">
            <p class="text-sm leading-none font-normal">
              Start Date
            </p>
          </th>
          <th class="p-4">
            <p class="text-sm leading-none font-normal">
              End Date
            </p>
          </th>
          <th class="p-4">
            <p class="text-sm leading-none font-normal">
              Owner
            </p>
          </th>
          <th class="p-4">
            <p class="text-sm leading-none font-normal">
              Budget
            </p>
          </th>
          <th class="p-4">
            <p></p>
          </th>
        </tr>
      </thead>
      <tbody>
        <tr class="hover:bg-slate-50">
          <td class="p-4">
            <p class="text-sm font-bold">
              Project Alpha
            </p>
          </td>
          <td class="p-4">
            <p class="text-sm">
              01/01/2024
            </p>
          </td>
          <td class="p-4">
            <p class="text-sm">
              30/06/2024
            </p>
          </td>
          <td class="p-4">
            <p class="text-sm">
              John Michael
            </p>
          </td>
          <td class="p-4">
            <p class="text-sm">
              $50,000
            </p>
          </td>
          <td class="p-4">
            <a href="#" class="text-sm font-semibold ">
              Edit
            </a>
          </td>
        </tr>
        <tr class="hover:bg-slate-50">
          <td class="p-4">
            <p class="text-sm font-bold">
              Beta Campaign
            </p>
          </td>
          <td class="p-4">
            <p class="text-sm">
              15/02/2024
            </p>
          </td>
          <td class="p-4">
            <p class="text-sm">
              15/08/2024
            </p>
          </td>
          <td class="p-4">
            <p class="text-sm">
              Alexa Liras
            </p>
          </td>
          <td class="p-4">
            <p class="text-sm">
              $75,000
            </p>
          </td>
          <td class="p-4">
            <a href="#" class="text-sm font-semibold ">
              Edit
            </a>
          </td>
        </tr>
        <tr class="hover:bg-slate-50">
          <td class="p-4">
            <p class="text-sm font-bold">
              Campaign Delta
            </p>
          </td>
          <td class="p-4">
            <p class="text-sm">
              01/03/2024
            </p>
          </td>
          <td class="p-4">
            <p class="text-sm">
              01/09/2024
            </p>
          </td>
          <td class="p-4">
            <p class="text-sm">
              Laurent Perrier
            </p>
          </td>
          <td class="p-4">
            <p class="text-sm">
              $60,000
            </p>
          </td>
          <td class="p-4">
            <a href="#" class="text-sm font-semibold ">
              Edit
            </a>
          </td>
        </tr>
        <tr class="hover:bg-slate-50">
          <td class="p-4">
            <p class="text-sm font-bold">
              Gamma Outreach
            </p>
          </td>
          <td class="p-4">
            <p class="text-sm">
              10/04/2024
            </p>
          </td>
          <td class="p-4">
            <p class="text-sm">
              10/10/2024
            </p>
          </td>
          <td class="p-4">
            <p class="text-sm">
              Michael Levi
            </p>
          </td>
          <td class="p-4">
            <p class="text-sm">
              $80,000
            </p>
          </td>
          <td class="p-4">
            <a href="#" class="text-sm font-semibold ">
              Edit
            </a>
          </td>
        </tr>
        <tr class="hover:bg-slate-50">
          <td class="p-4">
            <p class="text-sm font-bold">
              Omega Strategy
            </p>
          </td>
          <td class="p-4">
            <p class="text-sm">
              01/05/2024
            </p>
          </td>
          <td class="p-4">
            <p class="text-sm">
              01/11/2024
            </p>
          </td>
          <td class="p-4">
            <p class="text-sm">
              Richard Gran
            </p>
          </td>
          <td class="p-4">
            <p class="text-sm">
              $100,000
            </p>
          </td>
          <td class="p-4">
            <a href="#" class="text-sm font-semibold ">
              Edit
            </a>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
  <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">

    <div>
      <h2 className="text-2xl font-bold text-gray-900">Employees</h2>
      <p className="text-gray-600">Manage your workforce</p>
    </div>
    <button className="bg-blue-600 text-white px-4 py-2 rounded-xl hover:bg-blue-700 transition-all flex items-center space-x-2">
      <Plus className="w-4 h-4" />
      <span>Add Employee</span>
    </button>
  </div>
  <!-- Responsive Table Container -->
  <div class="overflow-x-auto bg-white shadow-md rounded-lg">
    <table class="min-w-max w-full border-collapse table-auto">
      <thead class="bg-blue-600 text-white text-sm">
        <tr>
          <th class="px-3 py-2 text-left">No</th>
          <th class="px-3 py-2 text-left">Employee</th>
          <th class="px-3 py-2 text-left hidden sm:table-cell">Reason</th>
          <th class="px-3 py-2 text-left">Applied At</th>
          <th class="px-3 py-2 text-left">Status</th>
          <th class="px-3 py-2 text-left">Action</th>
        </tr>
      </thead>
      <tbody class="text-sm">
        <?php if ($result && $result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr class="border-b hover:bg-gray-50">
              <td class="px-3 py-2 font-medium"><?= $row['id'] ?></td>
              <td class="px-3 py-2"><?= htmlspecialchars($row['name']) ?></td>
              <td class="px-3 py-2 hidden sm:table-cell truncate max-w-xs"><?= htmlspecialchars($row['reason']) ?></td>
              <td class="px-3 py-2 text-xs md:text-sm whitespace-nowrap"><?= $row['applied_at'] ?></td>
              <td class="px-3 py-2 whitespace-nowrap">
                <?php if ($row['status'] === 'Pending'): ?>
                  <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-xs font-medium">Pending</span>
                <?php elseif ($row['status'] === 'Approved'): ?>
                  <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-medium">Approved</span>
                <?php else: ?>
                  <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-medium">Rejected</span>
                <?php endif; ?>
              </td>
              <td class="px-3 py-2 whitespace-nowrap flex flex-col sm:flex-row gap-1 sm:gap-2">
                <?php if ($row['status'] === 'Pending'): ?>
                  <a href="?id=<?= $row['id'] ?>&action=approve"
                    class="bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded text-xs md:text-sm flex items-center justify-center sm:justify-start">
                    ✅ <span class="hidden md:inline ml-1">Approve</span>
                  </a>
                  <a href="?id=<?= $row['id'] ?>&action=reject"
                    class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-xs md:text-sm flex items-center justify-center sm:justify-start">
                    ❌ <span class="hidden md:inline ml-1">Reject</span>
                  </a>
                <?php else: ?>
                  <span class="text-gray-500 italic text-xs md:text-sm text-center">Completed</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="6" class="text-center text-gray-500 py-4">No leave requests found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

</body>

</html>