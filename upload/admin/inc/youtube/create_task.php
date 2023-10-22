<?php
// Include the database connection and error handling functions
require_once 'db_connect.php';
require_once 'error_handling.php';

require_once 'google/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   $userId = $_SESSION['user_id'];
   $channelName = $_POST['channel_name'];
   $videoUrl = $_POST['video_url'];
   $taskType = $_POST['task_type'];
   $viewsGoal = $_POST['views_goal'];
   $likesGoal = $_POST['likes_goal'];
   $commentsGoal = $_POST['comments_goal'];

   // Prepare and execute the SQL query to insert the task into the database
   $sql = "INSERT INTO tasks (user_id, channel_name, video_url, task_type, views_goal, likes_goal, comments_goal) 
           VALUES (?, ?, ?, ?, ?, ?, ?)";
   $statement = $connection->prepare($sql);
   $statement->bind_param("issiiii", $userId, $channelName, $videoUrl, $taskType, $viewsGoal, $likesGoal, $commentsGoal);
   $statement->execute();
   $statement->close();

   // Redirect the user to another page or show a success message
   header("Location: dashboard.php");
   exit();
}
?>

<!-- Create a form for users to input the task details -->
<form method="POST" action="create_task.php">
   <input type="text" name="channel_name" placeholder="Channel Name"><br>
   <input type="text" name="video_url" placeholder="Video URL"><br>
   <input type="text" name="task_type" placeholder="Task Type"><br>
   <input type="number" name="views_goal" placeholder="Views Goal"><br>
   <input type="number" name="likes_goal" placeholder="Likes Goal"><br>
   <input type="number" name="comments_goal" placeholder="Comments Goal"><br>
   <button type="submit">Create Task</button>
</form>
