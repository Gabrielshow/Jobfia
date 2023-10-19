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
	require_once("inc/pagination.inc.php");


	$results_per_page = 10;
	$cc = 0;

	if (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0) { $page = (int)$_GET['page']; } else { $page = 1; }
	$from = ($page-1)*$results_per_page;
	
	$result = smart_mysql_query("SELECT *, DATE_FORMAT(added, '%M %e, %Y') AS news_date FROM jobfia_news WHERE status='active' ORDER BY added DESC LIMIT $from, $results_per_page");
	
	$total_result = smart_mysql_query("SELECT * FROM jobfia_news WHERE status='active' ORDER BY added DESC");
	$total = mysqli_num_rows($total_result);
	$total_on_page = mysqli_num_rows($result);


	///////////////  Page config  ///////////////
	$PAGE_TITLE = "News";
	
	$single_page = 1;

	require_once ("inc/header.inc.php");

?>

	<h1><i class="fa fa-newspaper-o"></i> News</h1>

	<?php if ($total > 0 && !$_GET['id']) { ?>

		<?php while ($row = mysqli_fetch_array($result)) { ?>
			<div class="news_date"><?php echo $row['news_date']; ?></div>
			<h3><a href="<?php echo SITE_URL; ?>news.php?id=<?php echo $row['news_id']; ?>"><?php echo $row['news_title']; ?></a></h3>
			<div class="news_description">
				<?php
					$description = stripslashes($row['news_description']);
					if (strlen($description) > 450)
						$news_description = substr($description, 0, 450)."...<a class='seemore' href='".SITE_URL."news.php?id=".$row['news_id']."'>read more</a>";
					else
						$news_description = $description;
					
					echo $news_description;
				?>
			</div>
		<?php } ?>

			<?php 
				echo ShowPagination("news",$results_per_page,"news.php?","WHERE status='active'");
			?>
			
	<?php } ?>
	
	<?php if ($total == 0) { ?><p>There are no site news at this time.</p><?php } ?>


	<?php 

	if (isset($_GET['id']) && is_numeric($_GET['id']))
	{
		$news_id = (int)$_GET['id'];
		$result = smart_mysql_query("SELECT *, DATE_FORMAT(added, '%M %e, %Y') AS news_date FROM jobfia_news WHERE news_id='$news_id' AND status='active' LIMIT 1");
		$total2 = mysqli_num_rows($result);
		
	?>
	
	<?php if ($total2 > 0) { $row = mysqli_fetch_array($result); ?>

		<p class="pull-right"><a class="label label-primary" href="<?php echo SITE_URL; ?>news.php">read other news</a></p>

		<div class="news_date"><?php echo $row['news_date']; ?></div>
		<h3><?php echo $row['news_title']; ?></h3>
		<div class="news_description"><?php echo stripslashes($row['news_description']); ?></div>


	<?php }else{ ?>
			<p align="center">Sorry, news not found.</p>
			<p align="center">><a class="seemore" href="<?php echo SITE_URL; ?>news.php">read other news</a></p>
	<?php } ?>
	
	<?php } ?>


<?php require_once ("inc/footer.inc.php"); ?>