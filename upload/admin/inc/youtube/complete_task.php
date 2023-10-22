// Fetch the task details from the database
$sql = "SELECT * FROM tasks WHERE id = ?";
$statement = $connection->prepare($sql);
$statement->bind_param("i", $taskId);
$statement->execute();
$result = $statement->get_result();
$task = $result->fetch_assoc();
$statement->close();

// Get video statistics using YouTube API
$videoId = getVideoIdFromUrl($task['video_url']);
$videoStats = getVideoStatistics($videoId);

// Compare the task details to the video statistics
$isCompleted = true;
if ($task['views_goal'] > $videoStats['viewCount']) {
   $isCompleted = false;
}
if ($task['likes_goal'] > $videoStats['likeCount']) {
   $isCompleted = false;
}
if ($task['comments_goal'] > $videoStats['commentCount']) {
   $isCompleted = false;
}

if ($isCompleted) {
   // Update the task status if the task is completed successfully
   $sql = "UPDATE tasks SET status = 'Completed' WHERE id = ?";
   $statement = $connection->prepare($sql);
   $statement->bind_param("i", $taskId);
   $statement->execute();
   $statement->close();

   // Redirect the user to another page or show a success message
   header("Location: dashboard.php");
   exit();
} else {
   // Display an error message or take appropriate action
   displayError("Task not completed yet");
}
function getVideoIdFromUrl($videoUrl) {
   $urlParts = parse_url($videoUrl);
   parse_str($urlParts['query'], $params);
   return $params['v'];
}

function getVideoStatistics($videoId) {
   $client = new Google_Client();
   $client->setAuthConfig('YOUR_CLIENT_SECRET_FILE');
   $client->setAccessToken($_SESSION['youtube_access_token']);
   $youtube = new Google_Service_YouTube($client);

   $videoResponse = $youtube->videos->listVideos('statistics', ['id' => $videoId]);
   $videoData = $videoResponse->getItems()[0]->getStatistics();

   $videoStats = [
      'viewCount' => $videoData->getViewCount(),
      'likeCount' => $videoData->getLikeCount(),
      'commentCount' => $videoData->getCommentCount()
   ];

   return $videoStats;
}
