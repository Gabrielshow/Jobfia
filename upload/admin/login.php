<?php
/*******************************************************************\
 * Jobfia v3.1
 * http://www.jobfia.com
 *
 * Copyright (c) 2015-2018 Jobfia Software. All rights reserved.
 * ------------ Jobfia IS NOT FREE SOFTWARE --------------
\*******************************************************************/

	session_start();
	require_once("../inc/config.inc.php");


	if (isset($_SESSION['adm']['id']) && is_numeric($_SESSION['adm']['id']))
	{
		header("Location: index.php");
		exit();
	}


	if (isset($_POST['action']) && $_POST['action'] == "login")
	{
		$username	= mysqli_real_escape_string($conn, getPostParameter('username'));
		$pwd		= mysqli_real_escape_string($conn, getPostParameter('password'));
		$iword		= substr(GetSetting('iword'), 0, -3);
		$ip			= mysqli_real_escape_string($conn, getenv("REMOTE_ADDR"));

		if (!($username && $pwd))
		{
			$errormsg = "Please enter username and password";
		}
		else
		{
			$sql = "SELECT * FROM jobfia_settings WHERE setting_key='word' AND setting_value='".PasswordEncryption($pwd.$iword)."' LIMIT 1";
			$result = smart_mysql_query($sql);

			if ((mysqli_num_rows($result) != 0) && ($username == 'admin'))
			{
				$row = mysqli_fetch_array($result);

				if (!session_id()) session_start();
				$_SESSION['adm']['id'] = $row['setting_id'];
		
				header("Location: index.php");
				exit();
			}
			else
			{
				header("Location: login.php?msg=1");
				exit();
			}
		}
	}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Log in | Jobfia Admin Panel</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">	
	<meta name="author" content="Jobfia.com" />
	<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css" />
	<link rel="stylesheet" type="text/css" href="css/login.css" />
    <link rel="stylesheet" href="css/font-awesome.min.css">
	<link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">
	<link rel="shortcut icon" href="/favicon.ico" />
	<link rel="icon" type="image/ico" href="/favicon.ico" />
</head>
<body>

<br>
<p class="text-center">
		<center><a href="http://www.jobfia.com" target="_blank" ><img src="images/jobfia_logo.png" alt="Jobfia" title="Jobfia" border="0" /></a></center>
</p>
<br>

<table width="300" align="center" cellpadding="5" cellspacing="0" border="0" align="center">
<tr>
	<td height="250" valign="top" align="left">
      
       <h2 class="text-center"><i class="fa fa-lock fa-lg" style="color: #5cb85c"></i> Admin Panel</h2>

		<?php if (isset($errormsg) || isset($_GET['msg'])) { ?>
		<div class="alert alert-danger">
			<i class="fa fa-times"></i> 
			<?php if (isset($errormsg) && $errormsg != "") {  echo $errormsg; } ?>
			<?php if ($_GET['msg'] == 1) { echo "Wrong username or password"; } ?>
		</div>
		<?php } ?>

		<form action="login.php" method="post">
		<div class="login_box">
          <div class="form-group">
            <label>Username</label>
            <div class="input-group">
	            <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
            	<input type="text" class="form-control" name="username" value="" />
            </div>
          </div>          
           <div class="form-group">
            <label>Password</label>
            <div class="input-group">
	            <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>            
				<input type="password" class="form-control" name="password" value="" />
            </div>
          </div>
           <div class="form-group">
		  		<input type="hidden" name="action" value="login" />
				<input type="submit" class="btn btn-success btn-block" name="login" id="login" value="Log in" />
          </div>
		</div>
      </form>
	 <!--<br><p align="center"><span style="color: #AFAFAF;">&copy; Powered by <a href="http://jobfia.com" target="_blank" style="color:#AFAFAF;">Jobfia</a> v3.1</span></p>-->
	</td>
</tr>
</table>


	<script type="text/javascript" src="js/bootstrap.min.js"></script>
	<script type="text/javascript" src="js/jquery.min.js"></script>

</body>
</html>