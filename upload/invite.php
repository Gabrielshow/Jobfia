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
	require_once("inc/pagination.inc.php");

	$ReferralLink		= SITE_URL."?ref=".$userid;
	$ReferralLink_BBC	= "[url='".$ReferralLink."']Join ".SITE_TITLE." today![/url]";
	$ReferralLink_a		= "<a href='".$ReferralLink."' target='_blank'>Join ".SITE_TITLE." today!</a>";

	if (isset($_POST['action']) && $_POST['action'] == "friend")
	{
		unset($errs);
		$errs = array();

		$uname		= $_SESSION['FirstName'];
		$fname		= array();
		$fname		= $_POST['fname'];
		$femail		= array();
		$femail		= $_POST['femail'];

		if(!($fname[1] && $femail[1]))
		{
			$errs[] = "Please enter at least one friend's first name and email address";
		}
		else
		{
			foreach ($fname as $k=>$v)
			{
				if ($femail[$k] != "" && !preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $femail[$k]))
				{
					$errs[] = "Invalid email address"." #".$k;;
				}
			}
		}

		if (count($errs) == 0)
		{		
			$etemplate = GetEmailTemplate('invite_friend');

				foreach ($fname as $k=>$v)
				{
					if (isset($v) && $v != "" && isset($femail[$k]) && $femail[$k] != "")
					{
						$friend_name = $v;
						$friend_email = $femail[$k];

						$esubject = $etemplate['email_subject'];
						$emessage = $etemplate['email_message'];

						$emessage = str_replace("{friend_name}", $friend_name, $emessage);
						$emessage = str_replace("{first_name}", $uname, $emessage);
						$emessage = str_replace("{referral_link}", $ReferralLink, $emessage);
						$to_email = $friend_name.' <'.$friend_email.'>';

						SendEmail($to_email, $esubject, $emessage, $noreply_mail = 1);
					}
				}

			header("Location: invite.php?msg=1#invitations");
			exit();
		}
		else
		{
			$allerrors = "";
			foreach ($errs as $errorname)
				$allerrors .= "<i class='fa fa-times'></i> ".$errorname."<br/>\n";
		}
	}

	///////////////  Page config  ///////////////
	$PAGE_TITLE = "Refer a Friend";
	
	$single_page = 1;

	require_once ("inc/header.inc.php");

?>

	<div class="hidden-xs"><ul class="mmenu"><?php $inpage = 1; require ("inc/usermenu.inc.php"); ?></ul></div>

	<h1><i class="fa fa-user-plus" style="color: #01a650"></i> Refer a Friend</h1>

	<div class="well">
			Tell your friends about <?php echo SITE_TITLE; ?>.
			<?php if (REFER_FRIEND_BONUS > 0) { ?>We'll credit your account with a <b><?php echo DisplayMoney(REFER_FRIEND_BONUS); ?></b> bonus once your friend's account balance will reach <b><?php echo DisplayMoney(REFER_FRIEND_BONUS_AFTER); ?></b>.<?php } ?>
			Use your unique referral link to refer your friends to <?php echo SITE_TITLE; ?>.
	</div>
	
	<table width="100%" align="center" border="0" cellpadding="3" cellspacing="0">
	<tr>
		<td align="left" valign="middle">
			<div class="referral_link_share pull-right">
				<a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($ReferralLink); ?>&t=<?php echo SITE_TITLE; ?>" target="_blank" title="Share on Facebook"><i class="fa fa-facebook-official fa-3x" style="color: #0072bd" aria-hidden="true"></i></a>
				<a href="http://twitter.com/intent/tweet?source=sharethiscom&text=<?php echo SITE_TITLE; ?>&url=<?php echo urlencode($ReferralLink); ?>" target="_blank" title="Share on Twitter"><i class="fa fa-twitter-square fa-3x" style="color: #00aeef" aria-hidden="true"></i></a>
				<a href="https://plus.google.com/share?url=<?php echo urlencode($ReferralLink); ?>" target="_blank" title="Google Share"><i class="fa fa-google-plus-square fa-3x" style="color: #eb434a" aria-hidden="true"></i></a>
			</div>
			<div class="referral_link">
				<h3>Your referral link:</h3>
				<input type="text" class="form-control" size="70" readonly="readonly" onfocus="this.select();" onclick="this.focus();this.select();" value="<?php echo $ReferralLink; ?>" />
				<h3>BBCode for forums:</h3>
				<input type="text" class="form-control" size="70" readonly="readonly" onfocus="this.select();" onclick="this.focus();this.select();" value="<?php echo $ReferralLink_BBC; ?>" />
				<h3>HTML for websites:</h3>
				<input type="text" class="form-control" size="70" readonly="readonly" onfocus="this.select();" onclick="this.focus();this.select();" value="<?php echo $ReferralLink_a; ?>" />
				<div style="clear: both"></div>
			</div>
		</td>
	</tr>
	</table>

	<?php if (REFER_FRIEND_BONUS > 0) { ?>
	<a name="refs"></a>
	<section>
	<div class="row">
		<div class="col-sm-3 col-xs-6 text-center"><center><span class="count" style="background:#939393;"><?php echo GetRefClicksTotal($userid); ?></span></center> <i class="fa fa-hand-pointer-o fa-lg"></i> referral link clicks</div>
		<div class="col-sm-3 col-xs-6 text-center"><center><span class="count" style="background:#6EB8C9;"><a href="#refs_list"><?php echo GetReferralsTotal($userid); ?></a></span></center> <i class="fa fa-users fa-lg" style="color: #6eb8c9"></i> referrals</div>
		<div class="col-sm-3 col-xs-6 text-center"><center><span class="count" style="background:#FFAD16;"><?php echo GetReferralsPendingBonuses($userid); ?></span></center> <i class="fa fa-money fa-lg" style="color: #ffad16"></i> referrals pending earnings</div>
		<div class="col-sm-3 col-xs-6 text-center"><center><span class="count" style="background:#7AD108;"><?php echo GetReferralsPaidBonuses($userid); ?></span></center> <i class="fa fa-money fa-lg" style="color:#7AD108"></i> referrals paid earnings</div>
	</div>
	</section>
	<hr>
	<?php } ?>

	<a name="invitations"></a>
	<h1 class="text-center"><i class="fa fa-send-o"></i> Send Invitations</h1>

	<?php if (REFER_FRIEND_BONUS > 0) { ?>
	<table width="100%" align="center" border="0" cellpadding="3" cellspacing="0">
	<tr>
		<td align="center" valign="top">
			Refer your friends and get <b><?php echo DisplayMoney(REFER_FRIEND_BONUS); ?></b>! It's easy. Enter up to 5 email addresses of your friends.<br/> Each friend will receive link to join us and you will receive <b><?php echo DisplayMoney(REFER_FRIEND_BONUS); ?></b> per each referred member.<br/><br/>
		</td>
	</tr>
	</table>
	<?php } ?>

	<div style="width: 70%; background: #F9F9F9; border-radius: 10px; padding: 10px; margin: 0 auto;">
	<form action="invite.php#invitations" method="post">
	<table align="center" border="0" cellpadding="3" cellspacing="0">
		<?php if (isset($_GET['msg']) and $_GET['msg'] == 1) { ?>
			<div class="alert alert-success alert-dismissible fade in">
				<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
				<i class="fa fa-check-circle-o fa-lg"></i> Message has been sent to your friends.
			</div>
		<?php } ?>
          
			<?php if (isset($allerrors) and $allerrors != "") { ?>
				<div class="alert alert-danger"><?php echo $allerrors; ?></div>
			<?php } ?>

		  <?php for ($i=1; $i<=5; $i++) { ?>
          <tr>
			<td colspan="2" align="left" valign="top">
				<table width="100%" cellpadding="0" cellspacing="1" border="0">
                    <tr>
						<td align="left" valign="top">Friend #<?php echo $i; ?> First Name: <?php if ($i == 1) { ?><span class="req">* </span><?php } ?><br/>
							<input type="text" name="fname[<?php echo $i; ?>]" class="form-control" value="<?php echo $fname[$i]; ?>" size="37" />
						</td>
						<td width="15">&nbsp;</td>
						<td align="left" valign="top">Friend #<?php echo $i; ?> Email Address: <?php if ($i == 1) { ?><span class="req">* </span><?php } ?><br/>
							<input type="text" name="femail[<?php echo $i; ?>]" class="form-control" value="<?php echo $femail[$i]; ?>" size="37" />
						</td>
					</tr>
				</table>
			</td>
          </tr>
		  <?php } ?>
          <tr>
			<td colspan="2" align="center" valign="middle">
				<br>
				<input type="hidden" name="action" id="action" value="friend" />
				<button type="submit" class="btn btn-success btn-lg" name="Send" id="Send">Send Invitations</button>
			</td>
          </tr>
	</table>
	</form>
	</div>
	

	<a name="refs_list"></a>
	<br>
	<h1><i class="fa fa-users"></i> My Referrals</h1>
	
	<?php

		$results_per_page = 10;
		$cc = 0;

		////////////////// filter  //////////////////////
		if (isset($_GET['column']) && $_GET['column'] != "")
		{
			switch ($_GET['column'])
			{
				case "fname": $rrorder = "fname"; break;
				case "country": $rrorder = "country_id"; break;
				case "balance": $rrorder = "balance"; break;
				case "created": $rrorder = "created"; break;
				default: $rrorder = "created"; break;
			}
		}
		else
		{
			$rrorder = "created";
		}

		if (isset($_GET['order']) && $_GET['order'] != "")
		{
			switch ($_GET['order'])
			{
				case "asc": $rorder = "asc"; break;
				case "desc": $rorder = "desc"; break;
				default: $rorder = "desc"; break;
			}
		}
		else
		{
			$rorder = "desc";
		}
		//////////////////////////////////////////////////

		if (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0) { $page = (int)$_GET['page']; } else { $page = 1; }
		$from = ($page-1)*$results_per_page;

		$refs_query = "SELECT *, DATE_FORMAT(created, '%d %b %Y %h:%i %p') AS signup_date FROM jobfia_users WHERE ref_id='$userid' AND status='active' ORDER BY $rrorder $rorder LIMIT $from, $results_per_page";
		$total_refs_result = smart_mysql_query("SELECT * FROM jobfia_users WHERE ref_id='$userid' AND status='active'");
		$total_refs = mysqli_num_rows($total_refs_result);

		$refs_result = smart_mysql_query($refs_query);
		$total_refs_on_page = mysqli_num_rows($refs_result);

		if ($total_refs > 0)
		{
	?>
			<!--
			<form action="#refs_list" id="form1" name="form1" method="get">
			<div class="row browse_top">
				<div class="sortby col-sm-4">
					<span>Sort by:</span>
					<select name="column" id="column" onChange="document.form1.submit()" class="form-control">
						<option value="created" <?php if ($_GET['column'] == "added") echo "created"; ?>>Signup Date</option>
						<option value="fname" <?php if ($_GET['column'] == "fname") echo "selected"; ?>>Name</option>
						<option value="country" <?php if ($_GET['column'] == "country") echo "selected"; ?>>Country</option>
						<option value="balance" <?php if ($_GET['column'] == "balance") echo "selected"; ?>>Balance</option>
					</select>
				</div>
				<div class="sortby col-sm-4">
					<select name="order" id="order" onChange="document.form1.submit()" class="form-control">
						<option value="desc" <?php if ($_GET['order'] == "desc") echo "selected"; ?>>Descending</option>
						<option value="asc" <?php if ($_GET['order'] == "asc") echo "selected"; ?>>Ascending</option>
					</select>
				</div>
			<div class="col-sm-4 results text-right">
				Showing <?php echo ($from + 1); ?> - <?php echo min($from + $total_refs_on_page, $total_refs); ?> of <?php echo $total_refs; ?>
			</div>
			</div>
			</form>
			-->

			<div class="table-responsive">
            <table align="center" class="table table-striped table-hover brd" width="100%" border="0" cellspacing="0" cellpadding="3">
			<tr>
				<th width="10%">&nbsp;</th>
				<th width="30%">Name</th>
				<th width="25%"><i class="fa fa-globe fa-lg"></i> Country</th>
				<th width="15%">Balance</th>
				<th width="25%">Signup Date</th>
			</tr>
			<?php while ($refs_row = mysqli_fetch_array($refs_result)) { $cc++; ?>
			<tr class="<?php if (($cc%2) == 0) echo "row_even"; else echo "row_odd"; ?>">
				<td align="center" valign="middle">
					<img src="<?php echo substr(SITE_URL, 0, -1).AVATARS_URL.$refs_row['avatar']; ?>" width="45" height="45" class="img-responsive" border="0" style="border-radius: 20%" />
					<?php if (ifUserOnline($refs_row['user_id'])) { ?><i class="fa fa-dot-circle-o Blink" style="color: #72c02c"></i> <span class="online">online</span><?php } ?></td>
				<td align="left" valign="middle"><a href="<?php echo SITE_URL; ?>user_profile.php?id=<?php echo $refs_row['user_id']; ?>"><h4><?php echo $refs_row['username']; ?></h4></a><?php echo $refs_row['fname']." ".substr($refs_row['lname'], 0, 1)."."; ?></td>
				<td align="left" valign="middle"><?php echo GetCountry($refs_row['country_id']); ?></td>
				<td align="left" valign="middle"><?php echo DisplayMoney($refs_row['balance']); ?></td>
				<td align="left" valign="middle"><?php echo $refs_row['signup_date']; ?></td>
			</tr>
			<?php } ?>
			</table>
			</div>

			<?php echo ShowPagination("users",$results_per_page,"invite.php?column=$rrorder&order=$rorder&", "WHERE ref_id='".(int)$userid."' AND status='active'"); ?>
		
		<?php }else{ ?>
			<div class="alert alert-info"><p>You have not received any referrals at this time.</p></div>
		<?php } ?>


<?php require_once ("inc/footer.inc.php"); ?>