<?php

// Database connection configuration
$servername = "localhost";
$username = "your_username";
$password = "your_password";
$dbname = "your_database";

// Create connection
$connection = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Function to verify task completion
function verifyTaskCompletion($taskId)
{
    global $connection;

    // Fetch the task details from the database
    $sql = "SELECT * FROM tasks WHERE id = ?";
    $statement = $connection->prepare($sql);
    $statement->bind_param("i", $taskId);
    $statement->execute();
    $result = $statement->get_result();
    $task = $result->fetch_assoc();
    $statement->close();

    // Get video statistics from YouTube API
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

        return true;
    } else {
        return false;
    }
}

// Helper function to extract video ID from YouTube URL
function getVideoIdFromUrl($videoUrl)
{
    $urlParts = parse_url($videoUrl);
    parse_str($urlParts['query'], $params);
    return $params['v'];
}

// Helper function to get video statistics using YouTube API
function getVideoStatistics($videoId)
{
    $apiKey = "your_youtube_api_key";
    $apiUrl = "https://www.googleapis.com/youtube/v3/videos?part=statistics&id=" . $videoId . "&key=" . $apiKey;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $responseData = json_decode($response, true);

    if (isset($responseData['items'][0]['statistics'])) {
        $videoStats = $responseData['items'][0]['statistics'];
        return $videoStats;
    }

    return false;
}

// Usage example
$taskId = 1;

if (verifyTaskCompletion($taskId)) {
    echo "Task is completed successfully.";
} else {
    echo "Task is not completed yet.";
}

?>
