
	<?php if (isset($single_page) && $single_page == 1) { ?>
		</div></div></div>
		</section>
	<?php } ?>
	
	<?PHP /* ?>
	<section id="something-sell" class="clearfix parallax-section">
		<div class="container">
			<div class="row">
				<div class="col-sm-12 text-center">
					<h2 class="title">Sign up and start to do simply jobs and earn money.</h2>
					<h4>Browse thousands of jobs on your site.</h4>
					<a href="<?php echo SITE_URL; ?>signup.php" class="btn btn-primary">Sign up for free</a>
				</div>
			</div>
		</div>
	</section>
	<?php */ ?>

	<!-- footer -->
	<footer id="footer" class="clearfix">
		<!-- footer-top -->
		<section class="footer-top clearfix">
			<div class="container">
				<div class="row">
					<!-- footer-widget -->
					<div class="col-sm-3">
						<div class="footer-widget">
							<h3>Quik Links</h3>
							<ul>
								<li><a href="<?php echo SITE_URL; ?>">Home</a></li>
								<li><a href="<?php echo SITE_URL; ?>about.php">About Us</a></li>
								<li><a href="<?php echo SITE_URL; ?>news.php">News</a></li>
								<li><a href="<?php echo SITE_URL; ?>terms.php">Terms &amp; Conditions</a></li>
								<li><a href="<?php echo SITE_URL; ?>privacy.php">Privacy Policy</a></li>
								<li><a href="<?php echo SITE_URL; ?>contact.php">Contact Us</a></li>
							</ul>
						</div>
					</div><!-- footer-widget -->

					<!-- footer-widget -->
					<div class="col-sm-3">
						<div class="footer-widget">
							<h3>My Account</h3>
							<ul>
								<li><a href="<?php echo SITE_URL; ?>myaccount.php">My Account</a></li>
								<li><a href="<?php echo SITE_URL; ?>job_create.php">Submit a Job</a></li>
								<li><a href="<?php echo SITE_URL; ?>mypayments.php">My Payments</a></li>
								<li><a href="<?php echo SITE_URL; ?>invite.php">Invite a Friend</a></li>
								<li><a href="<?php echo SITE_URL; ?>top_workers.php">Top Workers</a></li>
								<li><a href="<?php echo SITE_URL; ?>workers.php">Find Workers</a></li>
							</ul>
						</div>
					</div><!-- footer-widget -->

					<!-- footer-widget -->
					<div class="col-sm-3">
						<div class="footer-widget social-widget">
							<h3>Follow Us</h3>
							<ul>
								<?php //if (FACEBOOK_PAGE != "") { ?><li><a href="<?php echo FACEBOOK_PAGE; ?>" target="_blank"><i class="fa fa-facebook-official"></i>Facebook</a></li><?php //} ?>
								<?php //if (TWITTER_PAGE != "") { ?><li><a href="<?php echo TWITTER_PAGE; ?>" target="_blank"><i class="fa fa-twitter-square"></i>Twitter</a></li><?php //} ?>
								<li><a href="#" target="_blank"><i class="fa fa-google-plus-square" style="color: #c1272d"></i>Google+</a></li>
								<li><a href="<?php echo SITE_URL; ?>rss.php"><i class="fa fa-rss-square" style="color: #f68f10"></i> RSS</a></li>
							</ul>
			
						</div>
					</div><!-- footer-widget -->

					<!-- footer-widget -->
					<div class="col-sm-3">
						<div class="footer-widget news-letter">
							<h3>Newsletter</h3>
							<p>Keep up to date with the latest jobs and news!</p>
							<!-- form -->
							<form action="<?php echo SITE_URL; ?>index.php">
								<input type="email" class="form-control" placeholder="Your email address">
								<input type="hidden" name="action" value="newsletter">
								<button type="submit" class="btn btn-primary">Subscribe</button>
							</form><!-- form -->			
						</div>
					</div><!-- footer-widget -->
				</div><!-- row -->
			</div><!-- container -->
		</section><!-- footer-top -->

		<div class="footer-bottom clearfix text-center">
			<div class="container">
				<p>Copyright &copy; <?php echo date("Y"); ?> <?php echo SITE_TITLE; ?>. All rights reserved.</p>				
				<!-- Do not remove this copyright notice! -->
					<div class="powered-by-jobfia">Powered by <a href="http://www.jobfia.com" title="php mini micro jobs script" target="_blank"><b style="color: #86d330">Jobfia</b></a><div>
				<!-- Do not remove this copyright notice! -->				
			</div>
		</div><!-- footer-bottom -->
	</footer><!-- footer -->

	<!-- JS -->
    <script src="<?php echo SITE_URL; ?>js/jquery.min.js"></script>
    <script src="<?php echo SITE_URL; ?>js/bootstrap.min.js"></script>
    <script src="<?php echo SITE_URL; ?>js/price-range.js"></script>   
    <script src="<?php echo SITE_URL; ?>js/main.js"></script>
    <script src="<?php echo SITE_URL; ?>js/jobfia.js"></script>
	
	<?php echo GOOGLE_ANALYTICS; ?>

</body>
</html>