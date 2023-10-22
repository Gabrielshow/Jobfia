<?php
// Include the database connection and error handling functions
require_once 'db_connect.php';
require_once 'error_handling.php';

// Fetch the user's tasks from the database
$userId = $_SESSION['user_id'];
$sql = "SELECT * FROM tasks WHERE user_id = ?";
$statement = $connection->prepare($sql);
$statement->bind_param("i", $userId);
$statement->execute();
$result = $statement->get_result();
$tasks = $result->fetch_all(MYSQLI_ASSOC);
$statement->close();
?>

<!-- Display the user's tasks and associated points on the page -->
<table>
   <thead>
      <tr>
         <th>Channel Name</th>
         <th>Video URL</th>
         <th>Task Type</th>
         <th>Views Goal</th>
         <th>Likes Goal</th>
         <th>Comments Goal</th>
         <th>Status</th>
      </tr>
   </thead>
   <tbody>
      <?php foreach ($tasks as $task): ?>
         <tr>
            <td><?php echo $task['channel_name']; ?></td>
            <td><?php echo $task['video_url']; ?></td>
            <td><?php echo $task['task_type']; ?></td>
            <td><?php echo $task['views_goal']; ?></td>
            <td><?php echo $task['likes_goal']; ?></td>
            <td><?php echo $task['comments_goal']; ?></td>
            <td><?php echo $task['status']; ?></td>
         </tr>
      <?php endforeach; ?>
   </tbody>
</table>
