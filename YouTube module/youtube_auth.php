session_start();

require_once 'google/autoload.php';

$client = new Google_Client();
$client->setAuthConfig('YOUR_CLIENT_SECRET_FILE');
$client->setRedirectUri('YOUR_REDIRECT_URI');
$client->addScope(Google_Service_YouTube::YOUTUBE_READONLY);
$youtube = new Google_Service_YouTube($client);

if (!isset($_GET['code'])) {
   $authUrl = $client->createAuthUrl();
   header("Location: " . $authUrl);
   exit();
} else {
   $client->fetchAccessTokenWithAuthCode($_GET['code']);
   $_SESSION['youtube_access_token'] = $client->getAccessToken();
   header("Location: complete_task.php");
   exit();
}
