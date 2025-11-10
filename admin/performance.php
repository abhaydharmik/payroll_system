<?php
require '../config.php';
require '../includes/auth.php';
checkRole('admin');

$emp = $_SESSION['user'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $punctuality = $_POST['punctuality'];
  $teamwork = $_POST['teamwork'];
  $productivity = $_POST['productivity'];
  $quality = $_POST['quality_of_work'];
  $initiative = $_POST['initiative'];
  $remarks = $_POST['remarks'];
  $user_id = $_POST['user'];
  $evaluator = $emp['name'];
  $review_date = date('Y-m-d');

  $total_score = $punctuality + $teamwork + $productivity + $quality + $initiative;
  $rating = ($total_score / 25) * 5;

  $stmt = $conn->prepare("INSERT INTO performance 
      (user_id, evaluator, review_date, punctuality, teamwork, productivity, quality_of_work, initiative, remarks, total_score, rating)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
  $stmt->bind_param("issiiiiisid", $user_id, $evaluator, $review_date, $punctuality, $teamwork, $productivity, $quality, $initiative, $remarks, $total_score, $rating);

  if ($stmt->execute()) {
    $message = "<p class='text-green-600 font-semibold bg-green-50 border border-green-200 px-4 py-2 rounded-lg mb-4'>✅ Performance record saved successfully.</p>";
  } else {
    $message = "<p class='text-red-600 font-semibold bg-red-50 border border-red-200 px-4 py-2 rounded-lg mb-4'>❌ Error saving performance record.</p>";
  }
}

$pageTitle = 'Performance Evaluation';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?> | Admin Panel</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 text-gray-800">

  <!-- Sidebar -->
  <?php include_once '../includes/sidebar.php'; ?>
  <div id="overlay" class="fixed inset-0 bg-black opacity-50 hidden z-30 md:hidden"></div>

  <!-- Main content -->
  <div class="flex-1 flex flex-col min-h-screen md:ml-64">
    <!-- Shared Header -->
    <?php include_once '../includes/header.php'; ?>

    <!-- Page Content -->
    <main class="flex-1 pt-24 px-4 md:px-10 pb-8">
      <?php include '../includes/breadcrumb.php'; ?>

      <div class="mx-auto bg-white rounded-2xl shadow-lg p-8 border border-gray-100">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-2xl font-semibold text-gray-800 flex items-center gap-2">
            <i class="fa-solid fa-chart-line text-blue-600"></i>
            Employee Performance Review
          </h2>
        </div>

        <?= $message ?>

        <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Select Employee -->
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-2">Select Employee</label>
            <select name="user" required class="w-full border py-2 px-2 rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 transition">
              <option value="">Choose Employee</option>
              <?php
              $res = $conn->query("SELECT id, name FROM users WHERE role='employee'");
              while ($row = $res->fetch_assoc()) {
                echo "<option value='{$row['id']}'>{$row['name']}</option>";
              }
              ?>
            </select>
          </div>

          <!-- Criteria Fields -->
          <?php
          $criteria = [
            'punctuality' => 'Punctuality',
            'teamwork' => 'Teamwork',
            'productivity' => 'Productivity',
            'quality_of_work' => 'Quality of Work',
            'initiative' => 'Initiative'
          ];
          foreach ($criteria as $name => $label): ?>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2"><?= $label ?> (1–5)</label>
              <input type="number" name="<?= $name ?>" min="1" max="5" required
                class="w-full border rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 transition px-3 py-2 shadow-sm">
            </div>
          <?php endforeach; ?>

          <!-- Remarks -->
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-2">Remarks</label>
            <textarea name="remarks" rows="3"
              class="w-full rounded-lg border focus:ring-blue-500 focus:border-blue-500 transition px-3 py-2 shadow-sm resize-none"
              placeholder="Write your remarks about the employee’s performance..."></textarea>
          </div>

          <!-- Submit -->
          <div class="md:col-span-2 flex justify-end">
            <button type="submit"
              class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg font-semibold shadow-md transition flex items-center gap-2">
              <i class="fa-solid fa-floppy-disk"></i> Save Review
            </button>
          </div>
        </form>
      </div>
    </main>
  </div>

</body>

</html>