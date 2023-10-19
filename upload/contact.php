<?php
/*******************************************************************\
 * Jobfia v3.1
 * http://www.jobfia.com
 *
 * Copyright (c) 2015-2018 Jobfia Software. All rights reserved.
 * ------------ Jobfia IS NOT FREE SOFTWARE --------------
\*******************************************************************/

	session_start();
	require_once("inc/config.inc.php");

	$content = GetContent('contact');

	if (isset($_SESSION['userid']) && is_numeric($_SESSION['userid']))
	{
		header ("Location: mysupport.php");
		exit();
	}


	if (isset($_POST['action']) && $_POST['action'] == 'contact')
	{
		unset($errs);
		$errs = array();

		$fname			= trim($_POST['fname']);
		$email			= trim($_POST['email']);
		$email_subject	= trim($_POST['email_subject']);
		$umessage		= nl2br(trim($_POST['umessage']));

		if (!($fname && $email && $email_subject && $umessage))
		{
			$errs[] = "Please fill in all fields";
		}
		else
		{
			if (isset($email) && $email !="" && !preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $email))
			{
				$errs[] = "Please enter a valid email address";
			}
		}

		if (count($errs) == 0)
		{
			$from = 'From: '.$fname.' <'.$email.'>';
			SendEmail(SITE_MAIL, $subject, $umessage, $noreply_mail = 1, $from);
				
			header("Location: contact.php?msg=1");
			exit();
		}
		else
		{
			$allerrors = "";
			foreach ($errs as $errorname)
				$allerrors .= $errorname."<br/>";
		}
	}


	///////////////  Page config  ///////////////
	$PAGE_TITLE = $content['title'];
	
	$single_page = 1;

	require_once ("inc/header.inc.php");
	
?>

	<h1><?php echo $content['title']; ?></h1>
	<p><?php echo $content['text']; ?></p>

	<div class="container">
	<div class="row">
	<div class="col-sm-6">
		
	<?php if (isset($_GET['msg']) && $_GET['msg'] == 1) { ?>
		<div class="alert alert-success">Thank you! Your message has been sent. We will be in contact soon.</div>
	<?php }?>

	<?php if (isset($allerrors) && $allerrors != "") { ?>
		<div class="alert alert-danger"><i class="fa fa-times"></i> <?php echo $allerrors; ?></div>
	<?php } ?>		
		
	<form action="" method="post">
		<div class="form-group row">
            <label>Name:</label>
			<input name="fname" class="form-control" type="text" value="<?php echo getPostParameter('fname'); ?>" size="32" />
		</div>
		<div class="form-group row">
			<label>Email:</label>
			<input name="email" class="form-control" type="text" value="<?php echo getPostParameter('email'); ?>" size="32" />
		</div>
		<div class="form-group row">
			<label>Subject:</label>
			<input name="email_subject" class="form-control" type="text" value="<?php echo getPostParameter('email_subject'); ?>" size="32" />
		</div>
		<div class="form-group row">
			<label>Message:</label>
			<textarea rows="8" cols="60" class="form-control" name="umessage"><?php echo getPostParameter('umessage'); ?></textarea>
		</div>
		<div class="form-group row">
			<label>&nbsp;</label>
				<input type="hidden" name="action" id="action" value="contact" />
				<input type="submit" class="btn btn-success btn-lg" name="Submit" value="Send message" />
		</div>
	</form>
	
	</div>
	</div>
	</div>

<?php require_once ("inc/footer.inc.php"); ?>