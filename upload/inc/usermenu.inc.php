
				<!--<ul class="dropdown-menu">-->
					<li><a href="<?php echo SITE_URL; ?>myaccount.php"><i class="fa fa-user-circle"></i> Dashboard</a></li>
					<li><a href="<?php echo SITE_URL; ?>mypayments.php"><i class="fa fa-money"></i> My Payments</a></li>
					<li><a href="<?php echo SITE_URL; ?>deposit.php"><i class="fa fa-upload"></i> Deposit Funds</a></li>
					<li><a href="<?php echo SITE_URL; ?>withdraw.php"><i class="fa fa-download"></i> Withdraw Funds</a></li>
					<li><a href="<?php echo SITE_URL; ?>mysupport.php"><i class="fa fa-headphones"></i> Support <?php if (GetMemberMessagesTotal() > 0) { ?><span class="label label-info"><?php echo GetMemberMessagesTotal(); ?></span><?php } ?></a></li>
					<li><a href="<?php echo SITE_URL; ?>invite.php"><i class="fa fa-paper-plane-o"></i> Refer a Friend</a></li>
					<li><a href="<?php echo SITE_URL; ?>myprofile.php"><i class="fa fa-id-card-o"></i> Edit Profile</a></li>

					<?php if (!(isset($inpage) && $inpage == 1)) { ?><p><center>------- <b style="color: #01a650; margin: 0 7px;">Employer</b> -------</center></p><?php } ?>
					<li><a href="<?php echo SITE_URL; ?>job_create.php"><i class="fa fa-plus"></i> Submit a Job</a></li>
					<li><a href="<?php echo SITE_URL; ?>myjobs.php"><i class="fa fa-list"></i> My Jobs <?php if (GetPendingTasksTotal(0, $userid) > 0) { ?><span class="label label-warning"><?php echo GetPendingTasksTotal(0, $userid); ?></span><?php } ?></a></li>
					<li><a href="<?php echo SITE_URL; ?>myworkers.php"><i class="fa fa-users"></i> My Workers</a></li>
					<li><a href="<?php echo SITE_URL; ?>workers.php"><i class="fa fa-search"></i> Find Workers</a></li>
					<?php if (SEND_BONUS == 1) { ?><li><a href="<?php echo SITE_URL; ?>send_money.php"><i class=" fa fa-thumbs-up"></i> Send Bonus</a></li><?php } ?>

					<?php if (!(isset($inpage) && $inpage == 1)) { ?><p><center>------- <b style="color: #01a650; margin: 0 7px;">Worker</b> -------</center></p><?php } ?>
					<li><a href="<?php echo SITE_URL; ?>offer_create.php"><i class="fa fa-plus"></i> Submit an Offer</a></li>
					<li><a href="<?php echo SITE_URL; ?>myoffers.php"><i class="fa fa-list"></i> My Offers</a></li>
					<li><a href="<?php echo SITE_URL; ?>jobs.php"><i class="fa fa-briefcase"></i> Available Jobs</a></li>
					<li><a href="<?php echo SITE_URL; ?>jobs_finished.php"><i class="fa fa-check-circle-o"></i> Finished Jobs</a></li>
					<li><a href="<?php echo SITE_URL; ?>jobs_invited.php"><i class="fa fa-fire"></i> Job Invitations <?php if (GetJobInvitesTotal($userid) > 0) { ?><span class="label label-info"><?php echo GetJobInvitesTotal($userid); ?></span><?php } ?></a></li>
				<!--</ul>-->
