<?php
/*******************************************************************\
 * Jobfia v3.1
 * http://www.jobfia.com
 *
 * Copyright (c) 2015-2018 Jobfia Software. All rights reserved.
 * ------------ Jobfia IS NOT FREE SOFTWARE --------------
\*******************************************************************/

	session_start();
	require_once("inc/auth.inc.php");
	require_once("inc/config.inc.php");


	if (isset($_GET['id']) && is_numeric($_GET['id']))
	{
		unset($errs);
		$errs = array();

		$worker_id = (int)$_GET['id'];

		if (empty($_SESSION['hire_workers'])) $_SESSION['hire_workers'] = array();

		$check_user = mysqli_num_rows(smart_mysql_query("SELECT * FROM jobfia_users WHERE user_id='$worker_id' AND user_id!='".(int)$userid."' AND worker='1' AND status='active' LIMIT 1"));
		if ($check_user != 0)
		{
			if (!@in_array($worker_id, $_SESSION['hire_workers']))
			{
				$_SESSION['hire_workers'][]	= $worker_id;
				$_SESSION['hire_workers']	= array_map('intval', $_SESSION['hire_workers']);
				$_SESSION['hire_workers']	= array_unique($_SESSION['hire_workers']);
			}
		}
	}


	if (isset($_POST['action']) && $_POST['action'] == "hire")
	{
		unset($errs);
		$errs = array();

		$workers	= array();
		$workers	= $_POST['worker_id'];

		$job_id		= (int)getPostParameter('job_id');
		$comment	= mysqli_real_escape_string($conn, nl2br(getPostParameter('comment')));
		$comment	= substr($comment, 0, 500);

		if (!($job_id))
		{
			$errs[] = "Please select your job";
		}
		elseif (count($workers) == 0)
		{
			$errs[] = "Please select workers for hire";
		}

		if (count($errs) == 0)
		{
			foreach ($workers as $worker_id)
			{
				smart_mysql_query("INSERT INTO jobfia_worker_invites SET job_id='$job_id', employer_id='$userid', comment='$comment', worker_id='".(int)$worker_id."', status='active', invited=NOW()"); //viewed='0'
			}
			
			// send invitation email to workers //
			SendJobInvitations($job_id);

			unset($_SESSION['hire_workers']);

			header("Location: hire.php?msg=sent");
			exit();
		}
		else
		{
			$allerrors = "";
			foreach ($errs as $errorname)
				$allerrors .= "&#155; ".$errorname."<br/>\n";
		}
	}

	$livejobs_query = "SELECT *, DATE_FORMAT(added, '%d %b %Y') AS date_added FROM jobfia_jobs WHERE user_id='$userid' AND status='active' ORDER BY title DESC";
	$livejobs_result = smart_mysql_query($livejobs_query);
	$livejobs_total = mysqli_num_rows($livejobs_result);


	///////////////  Page config  ///////////////
	$PAGE_TITLE = "Hire Workers";
	
	$single_page = 1;

	require_once ("inc/header.inc.php");

?>
		<div class="hidden-xs"><ul class="mmenu"><?php $inpage = 1; require ("inc/usermenu.inc.php"); ?></ul></div>

		<h1><i class="fa fa-handshake-o" style="color: #01a650"></i> Hire Workers</h1>

		<?php if (isset($_GET['msg']) && $_GET['msg'] == "sent") { ?>
			<div class="alert alert-success">
				<i class="fa fa-check-circle-o fa-lg"></i> Invitations have been sent to workers!
			</div>
			<p align="center"><a class="btn btn-default" href="<?php echo SITE_URL; ?>myjobs.php">Go to My Jobs &#155;</a></p>
		<?php }elseif (@$worker_id == $userid) { ?>
			<div class="alert alert-warning">Sorry, you can not hire yourself.</div>
			<p align="center"><a class="btn btn-default" href="#" onclick="history.go(-1);return false;"><i class="fa fa-chevron-left"></i> Go Back</a></p>
		<?php }else{ ?>

	<?php if ($livejobs_total == 0) { ?>
		<div class="alert alert-info">You do not have active job at this time. Please <a href="<?php echo SITE_URL; ?>job_create.php">post a job</a> first.</div>
	<?php }elseif (count($_SESSION['hire_workers']) == 0) { ?>
		<div class="alert alert-info">Please <a href="<?php echo SITE_URL; ?>workers.php">add some workers to hire</a> first.</div>
	<?php }else{ ?>

		<?php if (isset($allerrors)) { ?>
			<div class="alert alert-danger"><?php echo $allerrors; ?></div>
		<?php } ?>
		
		<div class="row" style="background: #fcfcfc; border: 1px solid #F5F5F5; border-radius: 10px; padding: 10px;">
		<div class="col-md-7">

			<form action="" method="post">
	        <table width="100%" align="center" cellpadding="3" cellspacing="5" border="0">
			<tr>
				<td align="left" valign="middle">
					<h4><i class="fa fa-briefcase"></i> Job</h4>
					<?php if ($livejobs_total > 0) { ?>
						<select name="job_id" id="job_id" class="form-control" required>
						<option value="">--- Select Job ---</option>
						<?php while ($livejobs_row = mysqli_fetch_array($livejobs_result)) { ?>
							<option value="<?php echo $livejobs_row['job_id']; ?>" <?php if (@$job_id == $livejobs_row['job_id']) echo "selected='selected'"; ?>><?php echo $livejobs_row['title']; ?> (<?php echo DisplayMoney($livejobs_row['price']); ?>)</option>
						<?php } ?>
						</select>
					<?php } ?>
				</td>
			</tr>
			<tr>
				<td valign="middle" align="left">
					<h4><i class="fa fa-users"></i> Workers to hire</h4>
					<div class="scrollbox">
					<?php
						$hire_workers = array();
						$hire_workers = $_SESSION['hire_workers'];
	
						if (count($hire_workers) > 0)
						{
							$res = smart_mysql_query("SELECT * FROM jobfia_users WHERE user_id IN (".implode($hire_workers, ",").") AND user_id!='$userid' AND worker='1' AND status='active'");
							if (mysqli_num_rows($res) > 0)
							{
								while ($row = mysqli_fetch_array($res))
								{
									$cc++;
									if (($cc%2) == 0)
										echo "<div class=\"even\"><input type=\"checkbox\" name=\"worker_id[]\" value=\"".(int)$row['user_id']."\" ".$checked." checked=\"checked\"><i class='fa fa-user-circle-o' style='color: #6eb8c9'></i> ".$row['fname']." ".substr($row['lname'], 0, 1).". (".$row['username'].")"."</div>";
									else
										echo "<div class=\"odd\"><input type=\"checkbox\" name=\"worker_id[]\" value=\"".(int)$row['user_id']."\" ".$checked." checked=\"checked\"><i class='fa fa-user-circle-o' style='color: #6eb8c9'></i> ".$row['fname']." ".substr($row['lname'], 0, 1).". (".$row['username'].")"."</div>";
								}
							}
						}
					?>
					</div>
					<?php if (count($hire_workers) > 0) { ?><a class="badge" style="background: #2995c4" href="<?php echo SITE_URL; ?>workers.php">+ Add more workers</a><br><?php } ?>
				</td>
				</tr>
			</tr>
			<tr>
				<td valign="middle" align="left">
					<h4><i class="fa fa-comment-o"></i> Message to workers</b> <small>(optional)</small></h4>
					<textarea rows="6" cols="45" class="form-control" name="comment"><?php echo getPostParameter('comment'); ?></textarea>
				</td>
			</tr>
			<tr>
				<td align="left" valign="top">
					<br>
					<input type="hidden" name="action" id="action" value="hire" />
					<input type="submit" class="btn btn-success btn-lg" name="hire" id="hire" value="Hire Workers" />
				</td>
			</tr>
	        </table>
			</form>
		
		</div>
		</div>

	<?php } ?>

	<?php } ?>

	

<?php require_once ("inc/footer.inc.php"); ?>