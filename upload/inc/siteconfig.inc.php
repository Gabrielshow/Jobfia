<?php
/*******************************************************************\
 * Jobfia v3.1
 * http://www.jobfia.com
 *
 * Copyright (c) 2015-2018 Jobfia Software. All rights reserved.
 * ------------ Jobfia IS NOT FREE SOFTWARE --------------
\*******************************************************************/

	$setts_sql = "SELECT * FROM jobfia_settings";
	$setts_result = smart_mysql_query($setts_sql);

	unset($settings);
	$settings = array();

	while ($setts_row = mysqli_fetch_array($setts_result))
	{
		$settings[$setts_row['setting_key']] = $setts_row['setting_value'];
	}

	define('SITE_TITLE', $settings['website_title']);
	define('EMAIL_FROM_NAME', $settings['email_from_name']);
	define('SITE_MAIL', $settings['website_email']);
	define('NOREPLY_MAIL', $settings['noreply_email']);
	define('SITE_URL', $settings['website_url']);
	define('SITE_HOME_TITLE', $settings['website_home_title']);
	define('SITE_LANGUAGE', $settings['website_language']);
	define('SITE_TIMEZONE', $settings['website_timezone']);
	define('BLOCK_SAME_IP', $settings['block_same_ip']);
	define('SITE_CURRENCY', $settings['website_currency']);
	define('SITE_CURRENCY_CODE', $settings['website_currency_code']);
	define('SITE_CURRENCY_FORMAT', $settings['website_currency_format']);
	define('ACCOUNT_ACTIVATION', $settings['account_activation']);
	define('PIN_VERIFICATION', $settings['pin_verification']);
	define('CHECK_SPAM_EMAIL', $settings['check_spam_email']);
	define('SIGNUP_CAPTCHA', $settings['signup_captcha']);
	define('AUTO_COMFIRM_DAYS', $settings['auto_confirm_days']);
	define('DEL_NOTCOMPLETE_DAYS', $settings['del_notcomplete_days']);
	define('PAYPAL_ACCOUNT', $settings['paypal_account']);
	define('PAYZA_ACCOUNT', $settings['payza_account']);
	define('PAYZA_SECURITY_CODE', $settings['payza_security_code']);
	define('SKRILL_ACCOUNT', $settings['skrill_account']);
	define('SKRILL_SECURITY_CODE', $settings['skrill_security_code']);
	define('AUTHORIZE_LOGIN', $settings['authorize_login']);
	define('AUTHORIZE_TRANSACTION_KEY', $settings['authorize_transaction_key']);
	define('AUTHORIZE_MD5_HASH', $settings['authorize_md5_hash']);
	define('RESULTS_PER_PAGE', $settings['results_per_page']);
	define('MIN_PAYOUT', $settings['min_payout']);
	define('SIGNUP_BONUS', $settings['signup_credit']);
	define('REFER_FRIEND_BONUS', $settings['refer_credit']);
	define('REFER_FRIEND_BONUS_AFTER', $settings['refer_credit_after']);
	define('JOB_APPROVAL_FEE', $settings['job_approval_fee']);
	define('JOB_FEE', $settings['job_fee']);
	define('SHOW_SITE_STATS', $settings['show_site_statistics']);
	define('FACEBOOK_CONNECT', $settings['facebook_connect']);
	define('FACEBOOK_APPID', $settings['facebook_appid']);
	define('FACEBOOK_SECRET', $settings['facebook_secret']);
	define('FACEBOOK_PAGE', $settings['facebook_page']);
	define('SHOW_FB_LIKEBOX', $settings['show_fb_likebox']);
	define('TWITTER_PAGE', $settings['twitter_page']);
	define('GOOGLEPLUS_PAGE', $settings['googleplus_page']);
	define('GOOGLE_ANALYTICS', stripslashes($settings['google_analytics']));
	define('SHOW_WORKERS_UNREG', $settings['show_workers_unreg']);
	define('SHOW_USERS_UNREG', $settings['show_users_unreg']);
	define('SHOW_JOBS_UNREG', $settings['show_jobs_unreg']);
	define('SHOW_JOB_INFO_UNREG', $settings['show_job_info_unreg']);
	define('SHOW_LATEST_COMPLETED', $settings['show_lastest_completed']);
	define('SHOW_HOMEPAGE_JOBS', $settings['show_homepage_jobs']);
	define('HOMEPAGE_JOBS_TYPE', $settings['homepage_jobs_type']);
	define('HOMEPAGE_JOBS_LIMIT', $settings['homepage_jobs_limit']);
	define('HOMEPAGE_OFFERS_LIMIT', $settings['homepage_offers_limit']);
	define('CANCEL_APPLICANTS', $settings['cancel_applicants']);
	define('SEND_BONUS', $settings['allow_send_bonus']);
	define('BONUS_FEE', $settings['bonus_fee']);
	define('WORKER_HIDE_JOBS', $settings['worker_hide_jobs']);
	define('SUBMIT_SERVICES', $settings['submit_services']);
	define('HIDE_NOT_COUNTRY_JOBS', $settings['hide_not_country_jobs']);
	define('AUTO_APPROVE_JOBS', $settings['auto_approve_jobs']);
	define('AUTO_APPROVE_SERVICES', $settings['auto_approve_services']);
	define('MIN_WORKER_RATING', $settings['min_worker_rating']);
	define('MIN_POSITIONS', $settings['min_positions']);
	define('DAILY_APPLICATIONS', $settings['daily_applications']);
	define('PREMIUM_DAILY_APPLICATIONS', $settings['premium_daily_applications']);
	define('FEATURED_JOB_FEE', $settings['featured_job_fee']);
	define('MIN_BONUS_PAYMENT', $settings['min_bonus']);
	define('MIN_DEPOSIT', $settings['min_deposit']);
	define('AVATAR_WIDTH', $settings['avatar_width']);
	define('AVATAR_HEIGHT', $settings['avatar_height']);
	define('PREMIUM_ACCOUNTS', $settings['premium_accounts']);
	define('PREMIUM_FEE', $settings['premium_account_fee']);
	define('PROOF_VCODE', $settings['proof_vcode']);
	define('JOB_TIME_DEFAULT', $settings['default_job_time']);
	define('JOB_COUNTRIES_LIMIT', $settings['job_countries_limit']);
	define('AVATARS_URL', '/images/avatars/');
	define('JOBFIA_PROOFS_URL', '/uploads/');
	define('ALLOWS_PROOFS_UPLOAD', $settings['allow_proofs_upload']);
	define('PROOF_MAX_SIZE', $settings['proof_max_size']);
	define('PROOF_ALLOWED_FILES', $settings['proof_filetypes']);
	define('NEW_JOB_ALERT', $settings['email_new_job']);
	define('NEW_OFFER_ALERT', $settings['email_new_offer']);
	define('NEW_DEPOSIT_ALERT', $settings['email_new_deposit']);
	define('NEW_WITHDRAW_ALERT', $settings['email_new_withdraw']);
	define('NEW_TICKET_ALERT', $settings['email_new_ticket']);
	define('SMTP_MAIL', $settings['smtp_mail']);
	define('SMTP_PORT', $settings['smtp_port']);
	define('SMTP_HOST', $settings['smtp_host']);
	define('SMTP_USERNAME', $settings['smtp_username']);
	define('SMTP_PASSWORD', $settings['smtp_password']);
	define('SMTP_SSL', $settings['smtp_ssl']);
	define('ADDTHIS_ID', $settings['addthis_id']);

	// results per page (dropdown menu)
	$results_on_page = array("10", "20", "50", "100", "111111");	

?>