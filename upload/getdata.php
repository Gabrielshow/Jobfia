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
	
	$what = getGetparameter('what');


		$cc = 0;
		$result = smart_mysql_query("SELECT * FROM jobfia_job_tasks WHERE status='confirmed' ORDER BY added DESC LIMIT 3"); //date = today ORDER BY RAND()
        if (mysqli_num_rows($result) > 0) {
	?>
				
		<?php while ($row = mysqli_fetch_array($result)) { $cc++; ?>
			<div class="item">
				<blockquote>
			
				<small class="pull-right" style="background: #EEE; border-radius: 3px; font-size: 11px; padding: 3px 7px; color: #333; display: block;"><?php //echo relative_date(strtotime($row['added'])); ?></small><br>
				<p><i class="fa fa-user-circle-o"></i> <?php echo GetWorkerInfo($row['worker_id'], $show_type = 2); ?> just completed the job "<a href="<?php echo SITE_URL; ?>job_details.php?id=<?php echo $row['job_id']; ?>"><?php echo $row['job_title']; ?></a>" and earned <b><?php echo DisplayMoney($row['job_price']); ?></b></p>
								<p class="author">- <?php echo SITE_TITLE; ?></p>
		
				</blockquote>
			</div>
		<?php	} ?>

		
		<?php

		}else{ echo "<p class='text-center'>No jobs completed yet.</p>"; }
	
?>