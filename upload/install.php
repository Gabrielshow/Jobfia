<?php
/*******************************************************************\
 * Jobfia v3.1
 * http://www.jobfia.com
 *
 * Copyright (c) 2015-2018 Jobfia Software. All rights reserved.
 * ------------ Jobfia IS NOT FREE SOFTWARE --------------
\*******************************************************************/

	if (!function_exists('str_split')) {
		function str_split($str) 
{
  $strArray = [];
  
  for($i = 0; $i < strlen($str); $i++) {
    $char = $str[$i];
    $strArray[] = $char;
  }

  return $strArray;
}
}

	define('is_Setup', TRUE);

	require_once("./inc/config.inc.php");

	$complete = 0;

	$check_install = @mysqli_num_rows(@mysqli_query($conn, "SELECT setting_value from jobfia_settings WHERE setting_key='iword' LIMIT 1"));
	if ($check_install != 0)
	{
		die("<p>&nbsp;</p><p><center><img src='images/isuccess.png'/><br/><span style='font-family: times, Times New Roman, times-roman, georgia, serif; font-size: 29px; letter-spacing: -1px; color: #444;'><font color='#01DF01'>Installation complete</font><br/> Please delete <font color='#FA8258'>install.php</font> file from your server!<br/><br/><a style='color:#0578B7;' href='/'>Yes, I have deleted it!</a></span></center></p>");
	}


if (isset($_POST['action']) && $_POST['action'] == "install")
{
	$license_key = trim($_POST['license']);

	unset($errs);
	$errs = array();

	if (!$license_key)
	{
		$errs[] = "Please enter your license key";
	}
	else
	{
		if (!preg_match("/^[0-9]{4}[-]{1}[0-9]{4}[-]{1}[0-9]{4}[-]{1}[0-9]{4}[-]{1}[0-9]{4}?$/", $license_key))
		{
			$errs[] = "License key is wrong! Please try again!";
			$wrong_key = 1;
		}
		else
		{
			if ($wrong_key == 1)
			{$licence_status = "correct";$st = 1;}else{$licence_status = "wrong";$key=explode("-",$license_key);$keey=$key[rand(0,2)];
			if($ikey[4][2]=7138%45){$step=1;$t=1;$licence_status="wrong";}else{$licence_status="correct";$step=2;}
			if($keey>0){$i=30+$step;if(rand(7,190)>=rand(0,1))$st=+$i;$u=0;}$status2=str_split($key[1],1);$status4=str_split($key[3],1);$status1=str_split($key[0],1);$status3=str_split($key[2],1);
			if($step==1){$kky=str_split($key[$u+4],1);if((($key[$u]+$key[2])-($key[3]+$key[$t])==(((315*2+$u)+$t)*++$t))&&(($kky[3])==$status4[2])&&(($status3[1])==$kky[0])&&(($status2[3])==$kky[1])&&(($kky[2]==$status2[1]))){$rnd_num = rand(100,999);}else{$rnd_num = rand(11,49);}}} if($licenses!=7){$wrong=1;$licence_status="wrong";}else{$wrong=0;$correct=1;}
		}

	}

	if (count($errs) == 0)
	{
		// check license //
		$words_arr = array(1 => 'GreenApple', 2 => 'whiteLemon', 3 => 'OrangeTomatos', 4 => 'YellowGrapefruit', 5 => 'greyStrawberry');
		$wrnd = rand(1,5);
		$rnd_word = $words_arr[$wrnd];
		$rnd_pwd = generatePassword(11);
		$my_word = $rnd_word.$rnd_num;
		$my_pwd = PasswordEncryption($rnd_pwd.$rnd_word);
		$domain	= mysqli_real_escape_string($conn, "http://".$_SERVER['HTTP_HOST']."/");
		if ($domain == "") $domain = "http://www.yourdomain.com/";


smart_mysql_query("DROP TABLE IF EXISTS `jobfia_categories`");
smart_mysql_query("CREATE TABLE IF NOT EXISTS `jobfia_categories` (
  `category_id` int(9) unsigned NOT NULL auto_increment,
  `parent_id` int(11) NOT NULL default '0',
  `name` varchar(50) collate utf8_unicode_ci NOT NULL default '',
  `min_price` decimal(19,2) NOT NULL default '0.00',
  `description` text collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`category_id`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");


smart_mysql_query("INSERT INTO `jobfia_categories` (`category_id`, `parent_id`, `name`, `min_price`, `description`) VALUES
		(1, 0, 'Sign up', 0.25, ''),
		(2, 0, 'Click or Search', 0.20, ''),
		(3, 0, 'Youtube', 0.20, ''),
		(4, 0, 'Facebook', 0.25, ''),
		(5, 0, 'Twitter', 0.25, ''),
		(6, 0, 'Forums', 0.20, ''),
		(7, 0, 'Write an Article', 1, ''),
		(8, 0, 'Write a Review', 0.75, ''),
		(9, 0, 'Voting &amp; Rating', 0.15, ''),
		(10, 0, 'Surveys', 0.35, ''),
		(11, 0, 'Website Owners', 0.50, ''),
		(12, 0, 'Leads', 0.20, ''),
		(13, 0, 'Data Mining', 1, ''),
		(14, 0, 'Write a Comment', 0.40, ''),
		(15, 0, 'Search &amp; Click', 0.30, ''),
		(16, 0, 'Bookmark', 0.20, ''),
		(17, 0, 'Content Moderation', 0.50, ''),
		(18, 0, 'Other', 0.10, '')");


smart_mysql_query("DROP TABLE IF EXISTS `jobfia_content`");
smart_mysql_query("CREATE TABLE IF NOT EXISTS `jobfia_content` (
  `content_id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(50) collate utf8_unicode_ci NOT NULL default '',
  `title` varchar(100) collate utf8_unicode_ci NOT NULL default '',
  `description` text collate utf8_unicode_ci NOT NULL,
  `meta_description` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `meta_keywords` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `modified` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`content_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");


smart_mysql_query("INSERT INTO `jobfia_content` (`content_id`, `name`, `title`, `description`, `modified`) VALUES
(1, 'home', 'Home page', '	<div id=\"slider\"><ul><li><img src=\"{$domain}images/slide1.jpg\" alt=\"\" /></li><li><img src=\"{$domain}images/slide2.jpg\" alt=\"\" /></li><li><img src=\"{$domain}images/slide3.jpg\" alt=\"\" /></li></ul></div>\r\n<h1 class=\'welcome\' style=\'text-align:center;border:none;\'>Fast and Easy Way to Post &amp; Do Jobs</h1></p>\r\n<p style=\'text-align: justify;\'>Welcome to our micro jobs site. We turn your free time into money. Jobfia unique approach guaratee employers that every task paid is successfully completed while guaranteeing that workers get paid for every job they complete. Post jobs and get success. Do jobs and earn money. Why not to sign up right now!</p>\r\n<div class=\'home\'>\r\n<h2 class=\'welcome\'>Are you an employer?</h2>\r\n<center><img src=\'{$domain}images/icon_employer.png\'></center>\r\n<ul id=\'home_benefits\'>\r\n<li>Post mini jobs</li>\r\n<li>Find great workers</li>\r\n<li>Pay only when you are satisfied</li>\r\n<li>Get amazing results</li>\r\n<li>Boost your business</li>\r\n</ul>\r\n\r\n<br/>\r\n<p align=\'center\'><a class=\'action\' href=\'{$domain}signup.php\'>Post a Job</a></p>\r\n</div>\r\n\r\n<div class=\'home\'>\r\n<h2 class=\'welcome\'>Are you looking for work?</h2>\r\n<center><img src=\'{$domain}images/icon_worker.png\'></center>\r\n\r\n<ul id=\'home_benefits\'>\r\n<li>Find jobs of your choice</li>\r\n<li>Complete simple tasks</li>\r\n<li>Earn money</li>\r\n<li>Get Sign Up bonus</li>\r\n<li>Invite your friends and earn more</li>\r\n</ul>\r\n\r\n<br/><p align=\'center\'><a class=\'action\' href=\'{$domain}signup.php\'>Get Started</a></p>\r\n</div>', NOW()),
(2, 'aboutus', 'About Us', '<p>Here information about your site.</p>', NOW()),
(3, 'rules', 'Job Posting Rules', '<span class=\'alert\'><b>We don\'t approve jobs asking to:</b></span>\r\n<br/><ul><li>spam or harm another website or person</li><li>download software</li><li>use a credit card or provide bank account details</li><li>exchange money between payment systems</li><li>disclose personal information</li><li>install software to their PC</li></ul>', NOW()),
(4, 'help', 'Help', '<p>Help information here (edit from admin area).</p>', NOW()),
(5, 'terms', 'Terms and Conditions', '<p>Your site terms and conditions (edit from admin area).</p>', NOW()),
(6, 'privacy', 'Privacy Policy', '<p>Privacy Policy information (edit from admin area).</p>', NOW()),
(7, 'contact', 'Contact Us', '<p>If you have any questions, please feel free to contact us.</p>', NOW())");



smart_mysql_query("DROP TABLE IF EXISTS `jobfia_countries`");
smart_mysql_query("CREATE TABLE IF NOT EXISTS `jobfia_countries` (
  `country_id` int(11) NOT NULL auto_increment,
  `group_id` int(11) NOT NULL default '0',
  `code` varchar(2) collate utf8_unicode_ci NOT NULL default '',
  `name` varchar(100) collate utf8_unicode_ci NOT NULL default '',
  `sort_order` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`country_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

smart_mysql_query("INSERT INTO `jobfia_countries` (`code`, `name`) VALUES
('AF', 'Afghanistan'),
('AX', 'Aland Islands'),
('AL', 'Albania'),
('DZ', 'Algeria'),
('AS', 'American Samoa'),
('AD', 'Andorra'),
('AO', 'Angola'),
('AI', 'Anguilla'),
('AG', 'Antigua and Barbuda'),
('AR', 'Argentina'),
('AM', 'Armenia'),
('AW', 'Aruba'),
('AU', 'Australia'),
('AT', 'Austria'),
('AZ', 'Azerbaijan'),
('BS', 'Bahamas'),
('BH', 'Bahrain'),
('BD', 'Bangladesh'),
('BB', 'Barbados'),
('BY', 'Belarus'),
('BE', 'Belgium'),
('BZ', 'Belize'),
('BJ', 'Benin'),
('BM', 'Bermuda'),
('BT', 'Bhutan'),
('BO', 'Bolivia'),
('BA', 'Bosnia and Herzegovina'),
('BW', 'Botswana'),
('BV', 'Bouvet Island'),
('BR', 'Brazil'),
('IO', 'British Indian Ocean Territory'),
('BN', 'Brunei Darussalam'),
('BG', 'Bulgaria'),
('BF', 'Burkina Faso'),
('BI', 'Burundi'),
('KH', 'Cambodia'),
('CM', 'Cameroon'),
('CA', 'Canada'),
('CV', 'Cape Verde'),
('KY', 'Cayman Islands'),
('CF', 'Central African Republic'),
('TD', 'Chad'),
('CL', 'Chile'),
('CN', 'China'),
('CX', 'Christmas Island'),
('CC', 'Cocos (Keeling) Islands'),
('CO', 'Colombia'),
('KM', 'Comoros'),
('CG', 'Congo'),
('CD', 'Congo, The Democratic Republic of the'),
('CK', 'Cook Islands'),
('CR', 'Costa Rica'),
('CI', 'Cote D''Ivoire'),
('HR', 'Croatia'),
('CU', 'Cuba'),
('CY', 'Cyprus'),
('CZ', 'Czech Republic'),
('DK', 'Denmark'),
('DJ', 'Djibouti'),
('DM', 'Dominica'),
('DO', 'Dominican Republic'),
('EC', 'Ecuador'),
('EG', 'Egypt'),
('SV', 'El Salvador'),
('GQ', 'Equatorial Guinea'),
('ER', 'Eritrea'),
('EE', 'Estonia'),
('ET', 'Ethiopia'),
('FK', 'Falkland Islands (Malvinas)'),
('FO', 'Faroe Islands'),
('FJ', 'Fiji'),
('FI', 'Finland'),
('FR', 'France'),
('GF', 'French Guiana'),
('PF', 'French Polynesia'),
('TF', 'French Southern Territories'),
('GA', 'Gabon'),
('GM', 'Gambia'),
('GE', 'Georgia'),
('DE', 'Germany'),
('GH', 'Ghana'),
('GI', 'Gibraltar'),
('GR', 'Greece'),
('GL', 'Greenland'),
('GD', 'Grenada'),
('GP', 'Guadeloupe'),
('GU', 'Guam'),
('GT', 'Guatemala'),
('GN', 'Guinea'),
('GW', 'Guinea-Bissau'),
('GY', 'Guyana'),
('HT', 'Haiti'),
('HM', 'Heard Island and McDonald Islands'),
('VA', 'Holy See (Vatican City State)'),
('HN', 'Honduras'),
('HK', 'Hong Kong'),
('HU', 'Hungary'),
('IS', 'Iceland'),
('IN', 'India'),
('ID', 'Indonesia'),
('IR', 'Iran, Islamic Republic of'),
('IQ', 'Iraq'),
('IE', 'Ireland'),
('IL', 'Israel'),
('IT', 'Italy'),
('JM', 'Jamaica'),
('JP', 'Japan'),
('JO', 'Jordan'),
('KZ', 'Kazakhstan'),
('KE', 'Kenya'),
('KI', 'Kiribati'),
('KP', 'Korea, Democratic People\'s Republic of'),
('KR', 'Korea, Republic of'),
('KW', 'Kuwait'),
('KG', 'Kyrgyzstan'),
('LA', 'Lao People''s Democratic Republic'),
('LV', 'Latvia'),
('LB', 'Lebanon'),
('LS', 'Lesotho'),
('LR', 'Liberia'),
('LY', 'Libyan Arab Jamahiriya'),
('LI', 'Liechtenstein'),
('LT', 'Lithuania'),
('LU', 'Luxembourg'),
('MO', 'Macao'),
('MK', 'Macedonia'),
('MG', 'Madagascar'),
('MW', 'Malawi'),
('MY', 'Malaysia'),
('MV', 'Maldives'),
('ML', 'Mali'),
('MT', 'Malta'),
('MH', 'Marshall Islands'),
('MQ', 'Martinique'),
('MR', 'Mauritania'),
('MU', 'Mauritius'),
('YT', 'Mayotte'),
('MX', 'Mexico'),
('FM', 'Micronesia, Federated States of'),
('MD', 'Moldova, Republic of'),
('MC', 'Monaco'),
('MN', 'Mongolia'),
('ME', 'Montenegro'),
('MS', 'Montserrat'),
('MA', 'Morocco'),
('MZ', 'Mozambique'),
('MM', 'Myanmar'),
('NA', 'Namibia'),
('NR', 'Nauru'),
('NP', 'Nepal'),
('NL', 'Netherlands'),
('AN', 'Netherlands Antilles'),
('NC', 'New Caledonia'),
('NZ', 'New Zealand'),
('NI', 'Nicaragua'),
('NE', 'Niger'),
('NG', 'Nigeria'),
('NU', 'Niue'),
('NF', 'Norfolk Island'),
('MP', 'Northern Mariana Islands'),
('NO', 'Norway'),
('OM', 'Oman'),
('PK', 'Pakistan'),
('PW', 'Palau'),
('PS', 'Palestinian Territory, Occupied'),
('PA', 'Panama'),
('PG', 'Papua New Guinea'),
('PY', 'Paraguay'),
('PE', 'Peru'),
('PH', 'Philippines'),
('PN', 'Pitcairn'),
('PL', 'Poland'),
('PT', 'Portugal'),
('PR', 'Puerto Rico'),
('QA', 'Qatar'),
('RE', 'Reunion'),
('RO', 'Romania'),
('RU', 'Russian Federation'),
('RW', 'Rwanda'),
('SH', 'Saint Helena'),
('KN', 'Saint Kitts and Nevis'),
('LC', 'Saint Lucia'),
('PM', 'Saint Pierre and Miquelon'),
('VC', 'Saint Vincent and the Grenadines'),
('WS', 'Samoa'),
('SM', 'San Marino'),
('ST', 'Sao Tome and Principe'),
('SA', 'Saudi Arabia'),
('SN', 'Senegal'),
('RS', 'Serbia'),
('SC', 'Seychelles'),
('SL', 'Sierra Leone'),
('SG', 'Singapore'),
('SK', 'Slovakia'),
('SI', 'Slovenia'),
('SB', 'Solomon Islands'),
('SO', 'Somalia'),
('ZA', 'South Africa'),
('GS', 'South Georgia'),
('ES', 'Spain'),
('LK', 'Sri Lanka'),
('SD', 'Sudan'),
('SR', 'Suriname'),
('SJ', 'Svalbard and Jan Mayen'),
('SZ', 'Swaziland'),
('SE', 'Sweden'),
('CH', 'Switzerland'),
('SY', 'Syrian Arab Republic'),
('TW', 'Taiwan, Province Of China'),
('TJ', 'Tajikistan'),
('TZ', 'Tanzania, United Republic of'),
('TH', 'Thailand'),
('TL', 'Timor-Leste'),
('TG', 'Togo'),
('TK', 'Tokelau'),
('TO', 'Tonga'),
('TT', 'Trinidad and Tobago'),
('TN', 'Tunisia'),
('TR', 'Turkey'),
('TM', 'Turkmenistan'),
('TC', 'Turks and Caicos Islands'),
('TV', 'Tuvalu'),
('UG', 'Uganda'),
('UA', 'Ukraine'),
('AE', 'United Arab Emirates'),
('GB', 'United Kingdom'),
('US', 'United States'),
('UM', 'United States Minor Outlying Islands'),
('UY', 'Uruguay'),
('UZ', 'Uzbekistan'),
('VU', 'Vanuatu'),
('VE', 'Venezuela'),
('VN', 'Viet Nam'),
('VG', 'Virgin Islands, British'),
('VI', 'Virgin Islands, U.S.'),
('WF', 'Wallis And Futuna'),
('EH', 'Western Sahara'),
('YE', 'Yemen'),
('ZM', 'Zambia'),
('ZW', 'Zimbabwe')");


smart_mysql_query("DROP TABLE IF EXISTS `jobfia_currencies`");
smart_mysql_query("CREATE TABLE IF NOT EXISTS `jobfia_currencies` (
  `currency_id` int(11) NOT NULL auto_increment,
  `currency_name` varchar(50) collate utf8_unicode_ci NOT NULL default '',
  `currency` varchar(10) collate utf8_unicode_ci NOT NULL default '',
  `currency_code` varchar(3) collate utf8_unicode_ci NOT NULL default '',
  `currency_country` varchar(100) collate utf8_unicode_ci NOT NULL default '',
  `exchange_rate` decimal(15,4) NOT NULL default '0.0000',
  `status` enum('active','inactive') collate utf8_unicode_ci NOT NULL default 'active',
  PRIMARY KEY  (`currency_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

smart_mysql_query("INSERT INTO `jobfia_currencies` (`currency_name`, `currency`, `currency_code`) VALUES
('US Dollar', '$', 'USD'),
('Euro', '&euro;', 'EUR'),
('British Pound', '&pound;', 'GBP'),
('Australian Dollar', '$', 'AUD'),
('Canadian Dollar', '$', 'CAD'),
('Czech Koruna', 'Kc', 'CZK'),
('Danish Krone', 'kr.', 'DKK'),
('Indian Rupee', 'Rs.', 'INR'),
('Russian Ruble', '???.', 'RUB'),
('Romanian Leu', 'lei', 'RON'),
('Swedish Krona', 'kr.', 'SEK'),
('Swiss Franc', 'fr.', 'CHF'),
('Turkish Lira', 'tl.', 'TRY'),
('Japanese Yen', '&yen;', 'JPY'),
('Singapore Dollar', '$', 'SGD'),
('Norwegian Krone', 'kr.', 'NOK'),
('Bulgarian Lev', '??.', 'BGN'),
('Colombian Peso', '$', 'COP'),
('Croatian Kuna', 'kn', 'HRK'),
('Hong Kong Dollar', '$', 'HKD'),
('Hungarian Forint', 'Ft', 'HUF'),
('Korean Won', '&#8361;', 'KRW'),
('Lithuanian Litas', 'Lt', 'LTL'),
('New Zealand Dollar', '$', 'NZD'),
('Philippine Peso', '&#8369;', 'PHP'),
('Polish Zloty', 'z&#322;', 'PLN'),
('South African Rand', 'R', 'ZAR'),
('United Arab Emirates Dirham', '&#1583;.&#', 'UAE'),
('Israeli New Shekel', '&#8362;', 'ILS'),
('Mexican Peso', '$', 'MXN'),
('Brazilian Real', 'R$', 'BRL'),
('Malaysian Ringgit', 'RM', 'MYR'),
('Taiwan Dollar New', '$', 'TWD'),
('Thai Baht', '&#3647;', 'THB'),
('Pakistan Rupee', 'Rs.', 'PKR'),
('Bangladeshi taka', '&#2547;', 'BDT')");



smart_mysql_query("DROP TABLE IF EXISTS `jobfia_email_templates`");
smart_mysql_query("CREATE TABLE IF NOT EXISTS `jobfia_email_templates` (
  `template_id` int(11) unsigned NOT NULL auto_increment,
  `email_name` varchar(50) collate utf8_unicode_ci NOT NULL default '',
  `email_title` varchar(50) collate utf8_unicode_ci NOT NULL default '',
  `email_subject` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `email_message` text collate utf8_unicode_ci NOT NULL,
  `modified` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`template_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

smart_mysql_query("INSERT INTO `jobfia_email_templates` (`template_id`, `email_name`, `email_title`, `email_subject`, `email_message`, `modified`) VALUES
(1, 'signup', 'Sign Up email', 'Welcome to our jobs site!', '<p style=\'font-family: Verdana, Arial, Helvetica, sans-serif; font-size:13px\'>\r\nDear {first_name},<br /><br />\r\nThank you for registering!<br /><br />\r\n\r\nHere is your login information:<br /><br />\r\nLogin: <b>{username}</b><br />\r\nPassword: <b>{password}</b><br /><br />\r\nPlease <a href=\'{login_url}\'>click here</a> to login in to your account.<br /><br />Thank you.\r\n</p>', NOW()),
(2, 'activate', 'Activation email', 'Registration Confirmation Email', '<p style=\'font-family: Tahoma, Verdana, Arial, Helvetica, sans-serif; font-size:13px\'>\r\nHi {first_name},<br /><br />\r\nThank you for registering!<br /><br />\r\nHere is your login information:<br /><br />\r\nUsername: <b>{username}</b><br />\r\nPassword: <b>{password}</b><br /><br />\r\n\r\nPlease click the following link to activate your account: <a href=\'{activate_link}\'>{activate_link}</a><br /><br />Thank you!\r\n</p>', NOW()),
(3, 'forgot_password', 'Forgot Password email', 'Forgot password email', '<p style=\'font-family: Verdana, Arial, Helvetica, sans-serif; font-size:13px\'>\r\nDear {first_name},<br /><br />\r\nAs you requested, here is new password for your account:<br /><br />\r\nLogin: <b>{username}</b><br />Password: <b>{password}</b> <br /><br />\r\nPlease <a href=\'{login_url}\'>click here</a> to log in.\r\n<br /><br />\r\nThank you.\r\n</p>', NOW()),
(4, 'invite_friend', 'Invite a Friend email', 'Invitation from your friend', '<p style=\'font-family: Verdana, Arial, Helvetica, sans-serif; font-size:13px\'>\r\nHello {friend_name}, <br /><br />\r\nYour friend <b>{first_name}</b> wants to invite you to register on our jobs site.<br /><br />\r\nPlease <a href=\'{referral_link}\'>click here</a> to accept his invitation.\r\n<br /><br />\r\nBest Regards.\r\n</p>', NOW()),
(5, 'new_task_alert', 'New task was completed', 'New task was completed', '<p style=\'font-family: Verdana, Arial, Helvetica, sans-serif; font-size:12px\'>\r\nHello {first_name}, <br /><br />\r\nYour job <b>{job_name}</b> has has received new applicant(task).<br /><br />\r\nPlease <a href=\'{login_url}\'>click here</a> to login in to your account to view tasks and rate workers.\r\n<br /><br />\r\nBest Regards.\r\n</p>', NOW()),
(6, 'job_complete_alert', 'Job Complete Alert', 'Job has been completed', '<p style=\'font-family: Verdana, Arial, Helvetica, sans-serif; font-size:13px\'>\r\nHello {first_name}, <br /><br />\r\nYour job <b>{job_name}</b> has been successfully completed.<br /><br />\r\nPlease <a href=\'{login_url}\'>click here</a> to login in to your account and confirm your job.\r\n<br /><br />\r\nBest Regards.\r\n</p>', NOW()),
(7, 'job_notcomplete_alert', 'Not Complete Job Alert', 'Job does not complete', '<p style=\'font-family: Verdana, Arial, Helvetica, sans-serif; font-size:13px\'>\r\nHello {first_name}, <br /><br />\r\nEmployer marks your task for <b>{job_name}</b> as not complete.\r\n<br />You have 2 days to complete this task. After this period your appliation will be deleted.<br /><br />\r\nPlease <a href=\'{login_url}\'>click here</a> to login in to your account and complete this task.\r\n<br /><br />\r\nBest Regards.\r\n</p>', NOW()),
(8, 'job_invitation', 'Job Invitation email', 'You are invited to job', '<p style=\'font-family: Verdana, Arial, Helvetica, sans-serif; font-size:13px\'>\r\nHello {first_name}, <br /><br />\r\nYou have been invited to do a job.<br /><br />\r\n Job Name: <b>{job_name}</b><br />\r\nPayment: <b>{job_price}</b><br /><br />\r\nPlease <a href=\'{login_url}\'>click here</a> to login in to your account and accept this job.\r\n<br /><br />\r\nBest Regards.\r\n</p>', NOW()),
(9, 'new_job_alert', 'New Job email', 'New job has been posted', '<p style=\'font-family: Verdana, Arial, Helvetica, sans-serif; font-size:13px\'>\r\nHello {first_name}, <br /><br />\r\nNew job has been added. You may be interested in this job.<br /><br />\r\n Job Name: <b>{job_name}</b><br />\r\nPayment: <b>{job_price}</b><br /><br />\r\nPlease <a href=\'{job_url}\'>click here</a> to view this job.\r\n<br /><br />\r\nBest Regards.\r\n</p>', NOW()),
(10, 'job_approved', 'Job Approved email', 'Your job was approved', '<p style=\'font-family: Verdana, Arial, Helvetica, sans-serif; font-size:13px\'>\r\nHello {first_name}, <br /><br />\r\nYour job <b>{job_name}</b> was approved and it is currently running. You can view completed task and rate your workers from your account.<br /><br />\r\nBest Regards.\r\n</p>', NOW()),
(11, 'job_declined', 'Job Declined email', 'Your job was declined', '<p style=\'font-family: Verdana, Arial, Helvetica, sans-serif; font-size:13px\'>\r\nHello {first_name}, <br /><br />\r\nWe are sorry, but your job <b>{job_name}</b> was declined.<br /><br />\r\n Reason: <b>{reason}</b>\r\n<br /><br />\r\nBest Regards.\r\n</p>', NOW())");



smart_mysql_query("DROP TABLE IF EXISTS `jobfia_news`");
smart_mysql_query("CREATE TABLE IF NOT EXISTS `jobfia_news` (
  `news_id` int(11) unsigned NOT NULL auto_increment,
  `news_title` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `news_description` text collate utf8_unicode_ci NOT NULL,
  `status` enum('active','inactive') collate utf8_unicode_ci NOT NULL default 'active',
  `added` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`news_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");



smart_mysql_query("DROP TABLE IF EXISTS `jobfia_messages`");
smart_mysql_query("CREATE TABLE IF NOT EXISTS `jobfia_messages` (
  `message_id` int(11) unsigned NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `recipient_id` int(11) NOT NULL default '0',
  `is_admin` tinyint(1) NOT NULL default '0',
  `subject` varchar(100) collate utf8_unicode_ci NOT NULL default '',
  `message` text collate utf8_unicode_ci NOT NULL,
  `viewed` tinyint(1) NOT NULL default '0',
  `status` enum('new','replied','closed') collate utf8_unicode_ci NOT NULL default 'new',
  `created` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`message_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");



smart_mysql_query("DROP TABLE IF EXISTS `jobfia_messages_answers`");
smart_mysql_query("CREATE TABLE IF NOT EXISTS `jobfia_messages_answers` (
  `answer_id` int(11) unsigned NOT NULL auto_increment,
  `message_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `recipient_id` int(11) NOT NULL default '0',
  `is_admin` tinyint(1) NOT NULL default '0',
  `answer` text collate utf8_unicode_ci NOT NULL,
  `viewed` tinyint(1) NOT NULL default '0',
  `answer_date` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`answer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");



smart_mysql_query("DROP TABLE IF EXISTS `jobfia_transactions`");
smart_mysql_query("CREATE TABLE IF NOT EXISTS `jobfia_transactions` (
  `transaction_id` int(11) unsigned NOT NULL auto_increment,
  `reference_id` varchar(50) collate utf8_unicode_ci NOT NULL default '',
  `user_id` int(11) NOT NULL default '0',
  `recipient_id` int(11) NOT NULL default '0',
  `payment_type` varchar(50) collate utf8_unicode_ci NOT NULL default '',
  `payment_method` varchar(50) collate utf8_unicode_ci NOT NULL default '',
  `payment_details` text collate utf8_unicode_ci NOT NULL,
  `transaction_fee` decimal(15,4) NOT NULL default '0.0000',
  `currency` varchar(3) collate utf8_unicode_ci NOT NULL default '',
  `amount` decimal(15,4) NOT NULL default '0.0000',
  `status` enum('pending', 'confirmed', 'paid','request','declined') collate utf8_unicode_ci NOT NULL default 'pending',
  `reason` text collate utf8_unicode_ci NOT NULL,
  `created` datetime NOT NULL default '0000-00-00 00:00:00',
  `updated` datetime NOT NULL default '0000-00-00 00:00:00',
  `process_date` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`transaction_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");



smart_mysql_query("DROP TABLE IF EXISTS `jobfia_jobs`");
smart_mysql_query("CREATE TABLE IF NOT EXISTS `jobfia_jobs` (
  `job_id` int(11) unsigned NOT NULL auto_increment,
  `job_type` enum('job', 'offer') collate utf8_unicode_ci NOT NULL default 'job',
  `title` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `image` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `user_id` int(11) NOT NULL default '0',
  `duration` int(8) NOT NULL default '0',
  `positions` int(8) NOT NULL default '0',
  `daily_limit` int(8) NOT NULL default '0',
  `price` decimal(19,2) NOT NULL default '0.00',
  `description` text collate utf8_unicode_ci NOT NULL,
  `proof` text collate utf8_unicode_ci NOT NULL,
  `is_proof_file` tinyint(1) NOT NULL default '1',
  `instructions` text collate utf8_unicode_ci NOT NULL,
  `auto_rate` tinyint(1) NOT NULL default '0',
  `vcode` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `job_price` decimal(19,2) NOT NULL default '0.00',
  `tags` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `worldwide` tinyint(1) NOT NULL default '1',
  `few_times` tinyint(1) NOT NULL default '0',
  `premium` tinyint(1) NOT NULL default '0',
  `featured` tinyint(1) NOT NULL default '0',
  `highlighted` tinyint(1) NOT NULL default '0',
  `homepage` tinyint(1) NOT NULL default '0',
  `max_orders` int(11) NOT NULL default '0',
  `views` int(11) NOT NULL default '0',
  `status` enum('active','pending','inactive','declined','complete') collate utf8_unicode_ci NOT NULL default 'pending',
  `paused` datetime NOT NULL default '0000-00-00 00:00:00',
  `reason` text collate utf8_unicode_ci NOT NULL,
  `added` datetime NOT NULL default '0000-00-00 00:00:00',
  `updated` datetime NOT NULL default '0000-00-00 00:00:00',
  `reviewed` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`job_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

/*
smart_mysql_query("DROP TABLE IF EXISTS `jobfia_offers`");
smart_mysql_query("CREATE TABLE IF NOT EXISTS `jobfia_offers` (
  `offer_id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `user_id` int(11) NOT NULL default '0',
  `duration` int(8) NOT NULL default '0',
  `price` decimal(19,2) NOT NULL default '0.00',
  `description` text collate utf8_unicode_ci NOT NULL,
  `instructions` text collate utf8_unicode_ci NOT NULL,
  `tags` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `worldwide` tinyint(1) NOT NULL default '1',
  `few_times` tinyint(1) NOT NULL default '0',
  `premium` tinyint(1) NOT NULL default '0',
  `featured` tinyint(1) NOT NULL default '0',
  `highlighted` tinyint(1) NOT NULL default '0',
  `homepage` tinyint(1) NOT NULL default '0',
  `max_orders` int(11) NOT NULL default '0',
  `views` int(11) NOT NULL default '0',
  `sales` int(11) NOT NULL default '0',
  `status` enum('active','pending','inactive','declined','complete') collate utf8_unicode_ci NOT NULL default 'pending',
  `paused` datetime NOT NULL default '0000-00-00 00:00:00',
  `reason` text collate utf8_unicode_ci NOT NULL,
  `added` datetime NOT NULL default '0000-00-00 00:00:00',
  `updated` datetime NOT NULL default '0000-00-00 00:00:00',
  `reviewed` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`offer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
*/

/*
smart_mysql_query("DROP TABLE IF EXISTS `jobfia_feedbacks`");
smart_mysql_query("CREATE TABLE `jobfia_feedbacks` (
  `feedback_id` int(11) unsigned NOT NULL auto_increment,
  `offer_id` int(11) unsigned NOT NULL default '0',
  `user_id` int(11) unsigned NOT NULL default '0',
  `rating` tinyint(1) NOT NULL default '0',
  `feedback` text collate utf8_unicode_ci NOT NULL,
  `status` enum('pending','active', 'inactive') collate utf8_unicode_ci NOT NULL default 'pending',
  `added` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`feedback_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
*/

smart_mysql_query("DROP TABLE IF EXISTS `jobfia_favorites`");
smart_mysql_query("CREATE TABLE `jobfia_favorites` (
  `favorite_id` int(11) unsigned NOT NULL default '0',
  `user_id` int(11) unsigned NOT NULL default '0',
  `favorite_type` varchar(100) collate utf8_unicode_ci NOT NULL default '',
  `status` enum('active','inactive') collate utf8_unicode_ci NOT NULL default 'inactive',
  `added` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`favorite_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");


smart_mysql_query("DROP TABLE IF EXISTS `jobfia_orders`");
smart_mysql_query("CREATE TABLE IF NOT EXISTS `jobfia_orders` (
  `order_id` int(11) unsigned NOT NULL auto_increment,
  `reference_id` varchar(50) collate utf8_unicode_ci NOT NULL default '',
  `user_id` int(11) NOT NULL default '0',
  `offer_id` int(11) NOT NULL default '0',
  `offer_title` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `payment_method` int(10) NOT NULL default '0',
  `currency` varchar(3) collate utf8_unicode_ci NOT NULL default '',
  `comment` text collate utf8_unicode_ci NOT NULL,
  `amount` decimal(15,4) NOT NULL default '0.0000',
  `status` enum('pending', 'paid','request','declined') collate utf8_unicode_ci NOT NULL default 'pending',
  `reason` text collate utf8_unicode_ci NOT NULL,
  `created` datetime NOT NULL default '0000-00-00 00:00:00',
  `updated` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`order_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");



smart_mysql_query("DROP TABLE IF EXISTS `jobfia_job_tasks`");
smart_mysql_query("CREATE TABLE IF NOT EXISTS `jobfia_job_tasks` (
  `job_task_id` int(11) unsigned NOT NULL auto_increment,
  `worker_id` int(11) NOT NULL default '0',
  `job_id` int(11) NOT NULL default '0',
  `job_title` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `job_price` decimal(19,2) NOT NULL default '0.00',
  `job_proof` text collate utf8_unicode_ci NOT NULL,
  `proof_file` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `proof_downloads` int(11) NOT NULL default '0',
  `comment` text collate utf8_unicode_ci NOT NULL,
  `country_code` varchar(2) collate utf8_unicode_ci NOT NULL default '',
  `worker_ip` varchar(15) collate utf8_unicode_ci NOT NULL default '',
  `status` enum('pending','confirmed','notcomplete','declined') collate utf8_unicode_ci NOT NULL default 'pending',
  `added` datetime NOT NULL default '0000-00-00 00:00:00',
  `updated` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`job_task_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");



smart_mysql_query("DROP TABLE IF EXISTS `jobfia_worker_invites`");
smart_mysql_query("CREATE TABLE `jobfia_worker_invites` (
  `invite_id` int(11) unsigned NOT NULL auto_increment,
  `job_id` int(11) unsigned NOT NULL default '0',
  `employer_id` int(11) unsigned NOT NULL default '0',
  `comment` text collate utf8_unicode_ci NOT NULL,
  `worker_id` int(11) unsigned NOT NULL default '0',
  `status` enum('pending','active', 'declined') collate utf8_unicode_ci NOT NULL default 'pending',
  `invited` datetime NOT NULL default '0000-00-00 00:00:00',
  `updated` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`invite_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");



smart_mysql_query("DROP TABLE IF EXISTS `jobfia_reports`");
smart_mysql_query("CREATE TABLE IF NOT EXISTS `jobfia_reports` (
  `report_id` int(11) unsigned NOT NULL auto_increment,
  `reporter_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `job_id` int(11) NOT NULL default '0',
  `report` text collate utf8_unicode_ci NOT NULL,
  `reply` text collate utf8_unicode_ci NOT NULL,
  `viewed` tinyint(1) NOT NULL default '0',
  `status` enum('active','pending','declined') collate utf8_unicode_ci NOT NULL default 'pending',
  `added` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`report_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");



smart_mysql_query("DROP TABLE IF EXISTS `jobfia_job_to_category`");
smart_mysql_query("CREATE TABLE `jobfia_job_to_category` (
  `job_id` int(11) unsigned NOT NULL default '0',
  `category_id` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`job_id`,`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");



smart_mysql_query("DROP TABLE IF EXISTS `jobfia_job_to_country`");
smart_mysql_query("CREATE TABLE `jobfia_job_to_country` (
  `job_id` int(11) unsigned NOT NULL default '0',
  `country_id` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`job_id`,`country_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");



smart_mysql_query("DROP TABLE IF EXISTS `jobfia_worker_skills`");
smart_mysql_query("CREATE TABLE `jobfia_worker_skills` (
  `worker_id` int(11) unsigned NOT NULL default '0',
  `skill_id` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`worker_id`,`skill_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");



smart_mysql_query("DROP TABLE IF EXISTS `jobfia_pmethods`");
smart_mysql_query("CREATE TABLE IF NOT EXISTS `jobfia_pmethods` (
  `pmethod_id` int(11) unsigned NOT NULL auto_increment,
  `pmethod_type` enum('withdraw','deposit') collate utf8_unicode_ci NOT NULL default 'withdraw',
  `pmethod_title` varchar(100) collate utf8_unicode_ci NOT NULL default '',
  `min_amount` decimal(15,4) NOT NULL default '0.0000',
  `fee` varchar(5) collate utf8_unicode_ci NOT NULL default '',
  `pmethod_image` varchar(100) collate utf8_unicode_ci NOT NULL default '',
  `pmethod_details` text collate utf8_unicode_ci NOT NULL,
  `status` enum('active','inactive') collate utf8_unicode_ci NOT NULL default 'active',
  PRIMARY KEY  (`pmethod_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

smart_mysql_query("INSERT INTO `jobfia_pmethods` (`pmethod_id`, `pmethod_type`, `pmethod_title`, `pmethod_image`, `pmethod_details`, `status`) VALUES
		(1, 'deposit', 'PayPal', 'paypal.png', '', 'active'),
		(2, 'deposit', 'Payza', 'payza.png', '', 'active'),
		(3, 'deposit', 'Skrill', 'skrill.png', '', 'inactive'),
		(4, 'deposit', 'Authorize.net', 'authorizenet.png', '', 'inactive'),
		(5, 'withdraw', 'PayPal', 'paypal.png', 'Please enter your paypal account:', 'active'),
		(6, 'withdraw', 'Payza', 'payza.png', 'Please enter your payza account:', 'active'),
		(7, 'withdraw', 'Wire Transfer', '', 'Please enter following information: <br />\r\n - Your Full Name <br />\r\n - Bank Name <br />\r\n - Bank Address <br />\r\n - Account #', 'active'),
		(8, 'withdraw', 'Skrill', 'skrill.png', 'Please enter your skrill account:', 'active')");



smart_mysql_query("DROP TABLE IF EXISTS `jobfia_my_workers`");
smart_mysql_query("CREATE TABLE `jobfia_my_workers` (
  `employer_id` int(11) unsigned NOT NULL default '0',
  `workers_group` varchar(100) collate utf8_unicode_ci NOT NULL default '',
  `worker_id` int(11) unsigned NOT NULL default '0',
  `status` enum('active','inactive') collate utf8_unicode_ci NOT NULL default 'inactive',
  `added` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`employer_id`,`worker_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");



smart_mysql_query("DROP TABLE IF EXISTS `jobfia_email_blacklist`");
smart_mysql_query("CREATE TABLE `jobfia_email_blacklist` (
  `blacklist_id` int(11) unsigned NOT NULL auto_increment,
  `domain` varchar(100) collate utf8_unicode_ci NOT NULL default '',
  `status` enum('active','inactive') collate utf8_unicode_ci NOT NULL default 'active',
  PRIMARY KEY  (`blacklist_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

smart_mysql_query("INSERT INTO `jobfia_email_blacklist` (`domain`, `status`) VALUES
		('mailinator.com', 'active'),
		('mailinator.net', 'active'),
		('binkmail.com', 'active'),
		('whyspam.me', 'active'),
		('yopmail.com', 'active')");



smart_mysql_query("DROP TABLE IF EXISTS `jobfia_users`");
smart_mysql_query("CREATE TABLE IF NOT EXISTS `jobfia_users` (
  `user_id` int(11) unsigned NOT NULL auto_increment,
  `worker` tinyint(1) NOT NULL default '0',
  `usertype` enum('worker','employer', 'both') collate utf8_unicode_ci NOT NULL default 'worker',
  `username` varchar(70) collate utf8_unicode_ci NOT NULL default '',
  `password` varchar(32) collate utf8_unicode_ci NOT NULL default '',
  `email` varchar(100) collate utf8_unicode_ci NOT NULL default '',
  `fname` varchar(32) collate utf8_unicode_ci NOT NULL default '',
  `lname` varchar(25) collate utf8_unicode_ci NOT NULL default '',
  `avatar` varchar(100) collate utf8_unicode_ci NOT NULL default '',
  `about` text collate utf8_unicode_ci NOT NULL,
  `company` varchar(32) collate utf8_unicode_ci NOT NULL default '',
  `address` varchar(32) collate utf8_unicode_ci NOT NULL default '',
  `address2` varchar(70) collate utf8_unicode_ci NOT NULL default '',
  `city` varchar(50) collate utf8_unicode_ci NOT NULL default '',
  `state` varchar(50) collate utf8_unicode_ci NOT NULL default '',
  `zip` varchar(10) collate utf8_unicode_ci NOT NULL default '',
  `country_id` int(11) NOT NULL default '0',
  `phone` varchar(20) collate utf8_unicode_ci NOT NULL default '',
  `pin_code` varchar(12) collate utf8_unicode_ci NOT NULL default '',
  `pin_verified` tinyint(1) NOT NULL default '0',
  `payment_method` varchar(50) collate utf8_unicode_ci NOT NULL default '',
  `balance` decimal(15,4) NOT NULL default '0.0000',
  `show_as` tinyint(1) NOT NULL default '1',
  `rate` decimal(15,4) NOT NULL default '5.0000',
  `premium` tinyint(1) NOT NULL default '0',
  `top_seller` tinyint(1) NOT NULL default '0',
  `satisfied_tasks` int(11) unsigned NOT NULL default '0',
  `ref_clicks` int(11) unsigned NOT NULL default '0',
  `ref_id` int(11) unsigned NOT NULL default '0',
  `ref_bonus` tinyint(1) NOT NULL default '0',
  `newsletter` tinyint(1) NOT NULL default '0',
  `notification1` tinyint(1) NOT NULL default '0',
  `notification2` tinyint(1) NOT NULL default '0',
  `notification3` tinyint(1) NOT NULL default '0',
  `notification4` tinyint(1) NOT NULL default '0',
  `ip` varchar(15) collate utf8_unicode_ci NOT NULL default '',
  `status` enum('active','inactive') collate utf8_unicode_ci NOT NULL default 'active',
  `auth_provider` varchar(100) collate utf8_unicode_ci NOT NULL default '',
  `auth_uid` int(11) unsigned NOT NULL default '0',
  `activation_key` varchar(32) collate utf8_unicode_ci NOT NULL default '',
  `unsubscribe_key` varchar(100) collate utf8_unicode_ci NOT NULL default '',
  `login_session` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `last_activity` datetime NOT NULL default '0000-00-00 00:00:00',
  `last_login` datetime NOT NULL default '0000-00-00 00:00:00',
  `login_count` int(8) unsigned NOT NULL default '0',
  `last_ip` varchar(15) collate utf8_unicode_ci NOT NULL default '',
  `created` datetime NOT NULL default '0000-00-00 00:00:00',
  `block_reason` tinytext collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");



smart_mysql_query("DROP TABLE IF EXISTS `jobfia_settings`");
smart_mysql_query("CREATE TABLE IF NOT EXISTS `jobfia_settings` (
  `setting_id` int(11) NOT NULL auto_increment,
  `setting_key` varchar(50) collate utf8_unicode_ci NOT NULL default '',
  `setting_value` text collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`setting_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=100");

smart_mysql_query("INSERT INTO `jobfia_settings` (`setting_key`, `setting_value`) VALUES
('website_title', 'Jobs Site'),
('website_url', '$domain'),
('website_home_title', 'Jobs Site - Earn money in minutes!'),
('email_from_name', 'Job Site Support'),
('website_email', 'admin@yourdomain.com'),
('noreply_email', 'noreply@yourdomain.com'),
('website_language', 'english'),
('website_timezone', '0'),
('website_currency', '$'),
('website_currency_code', 'USD'),
('website_currency_format', '1'),
('paypal_account', 'payments@yourdomain.com'),
('payza_account', ''),
('payza_security_code', ''),
('skrill_account', ''),
('skrill_security_code', ''),
('skrill_account', ''),
('authorize_login', ''),
('authorize_transaction_key', ''),
('authorize_md5_hash', ''),
('signup_credit', '1'),
('refer_credit', '1'),
('refer_credit_after', '20'),
('job_approval_fee', '2'),
('job_fee', '10%'),
('min_payout', '10'),
('block_same_ip', '0'),
('account_activation', '1'),
('pin_verification', '0'),
('check_spam_email', '0'),
('signup_captcha', '1'),
('auto_confirm_days', '7'),
('del_notcomplete_days', '2'),
('show_workers_unreg', '1'),
('show_users_unreg', '1'),
('show_jobs_unreg', '1'),
('show_job_info_unreg', '1'),
('show_lastest_completed', '1'),
('show_homepage_jobs', '1'),
('homepage_jobs_type', '0'),
('homepage_jobs_limit', '10'),
('homepage_offers_limit', '10'),
('cancel_applicants', '1'),
('allow_send_bonus', '1'),
('bonus_fee', '0'),
('submit_services', '1'),
('worker_hide_jobs', '0'),
('hide_not_country_jobs', '1'),
('premium_accounts', '1'),
('premium_account_fee', '5'),
('proof_vcode', '1'),
('default_job_time', '3'),
('job_countries_limit', '10'),
('allow_proofs_upload', '1'),
('proof_max_size', '2097152'),
('proof_filetypes', 'gif|jpg|png|jpeg'),
('auto_approve_jobs', '0'),
('auto_approve_services', '1'),
('min_worker_rating', '3'),
('min_positions', '3'),
('daily_applications', '5'),
('premium_daily_applications', '20'),
('featured_job_fee', '5'),
('min_bonus', '1'),
('min_deposit', '10'),
('avatar_width', '65'),
('avatar_height', '65'),
('facebook_connect', '0'),
('facebook_appid', ''),
('facebook_secret', ''),
('facebook_page', ''),
('show_fb_likebox', '0'),
('twitter_page', ''),
('googleplus_page', ''),
('google_analytics', ''),
('addthis_id', 'YOUR-ACCOUNT-ID'),
('show_site_statistics', '1'),
('results_per_page', '20'),
('email_new_job', '0'),
('email_new_offer', '0'),
('email_new_deposit', '0'),
('email_new_withdraw', '0'),
('email_new_ticket', '0'),
('smtp_mail', '0'),
('smtp_port', '25'),
('smtp_host', ''),
('smtp_username', ''),
('smtp_password', ''),
('smtp_ssl', ''),
('license', '{$license_key}'),
('word', '{$my_pwd}'),
('iword', '{$my_word}')");

		$complete = 1;
	}
	else
	{
		$allerrors = "";
		foreach ($errs as $errorname)
			$allerrors .= $errorname."<br/>\n";
	}

}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Jobfia Installation</title>
	<link rel="stylesheet" href="css/bootstrap.min.css" >
<style type="text/css">
<!--

body {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 14px;
	color: #444;
	margin: 0;
	padding: 0;
}

a {color: #3494CF; text-decoration: underline;}
a:hover {color: #94CA29; text-decoration: none;}

h1 {
	font-family: times, Times New Roman, times-roman, georgia, serif;
	color: #444;
	margin: 0;
	padding: 30px 0px 10px 0px;
	font-size: 41px;
	line-height: 44px;
	letter-spacing: -2px;
	font-weight: bold;
}

form {
	margin: 0 0 0 0;
	font-size: 13px;
	font-family: Verdana, Arial, Helvetica, sans-serif;
	color: #000;
}

table tr td {
	font-size: 13px;
	font-family: Verdana, Arial, Helvetica, sans-serif;
	color: #000;
}

input.textbox {
	background-color: #FFF;
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 13px;
	margin: 0;
	padding: 6px 3px;
	border: 1px solid #bdc7d8;
	border-radius: 4px;
}

.submit {
	padding: 8px 12px 8px 12px;
	background: #8FCE1A;
	border: 1px solid #8FDB02;
	color: #FFFFFF;
	font-size: 14px;
	font-family: Verdana, tahoma, arial, sans-serif;
	font-weight: bold;
	text-align: center;
	cursor: pointer;
	border-radius: 8px;
}

.submit:hover {
	background: #8FDB02;
	border: 1px solid #94DD02;
}

.success {
	font-family: times, Times New Roman, times-roman, georgia, serif;
	font-size: 29px;
	line-height: 40px;
	letter-spacing: -1px;
	color: #FFFFFF;
	margin: 0;
	padding: 0;
	font-weight: 100;
}

.info_label {
	font-size: 13px;
	color: #2B2B2B;
}

.info {
	font-family: times, Times New Roman, times-roman, georgia, serif;
	font-size: 28px;
}

.note {
	font-family: Aria, Times New Roman, times-roman, georgia, serif;
	font-size: 21px;
	color: #000;
	font-weight: normal;
	padding: 15px 0;
}

-->
</style>
</head>
<table width="520" align="center" cellpadding="5" cellspacing="0" border="0" align="center">
<tr>
<td height="300" valign="middle" align="center" style="padding: 10px">

       <h1><font color="#6eb716">Jobfia</font> Installation</h1>

		<?php if ($complete == 1) { ?>
			<table width="100%" align="center" cellpadding="5" cellspacing="0" border="0">
			<tr height="50">
				<td style="border: 1px solid #01a650;" bgcolor="#01a650" align="center" valign="middle">
					<span class="success">Congratulations! Installation is complete!</span>
				</td>
			</tr>
			<tr height="200">
				<td bgcolor="#F9F9F9" style="border: 1px dotted #eee;" align="center" valign="middle">
					
					<table width="100%" align="center" cellpadding="5" cellspacing="0" border="0">
					<tr valign="middle">
						<td nowrap="nowrap" width="50%" align="right"><span class="info_label">Admin Username:</span></td><td nowrap="nowrap" width="50%" align="left"><span class="info">admin</span></td>
					</tr>
					<tr valign="middle">
						<td nowrap="nowrap" align="right"><span class="info_label">Admin Password:</span></td><td nowrap="nowrap" align="left"><span class="info"><?php echo $rnd_pwd; ?></span></td>
					</tr>
					</table>
					<br/><br/>
					<span class="note">Note your admin area password!</span>
					<p>You can now <a target="_blank" href="/admin/">login to your control panel</a> and change your password.</a></p>
				</td>
			</tr>
			<tr height="35">
				<td nowrap="nowrap" align="center" valign="middle">
					<span style="color:#FF0000; font-weight:bold;"><u>Important</u>: Please now DELETE "install.php" file from your server!</span>
				</td>
			</tr>
			</table>
			<br/>
		<?php }else{ ?>


		<?php if (isset($allerrors)) { ?>
			<table width="100%" style="border: 1px #F3C5D4 dotted;" bgcolor="#FB0F0F" align="center" cellpadding="5" cellspacing="0" border="0">
			<tr height="35">
				<td align="center" valign="middle">
					<font color="#FFFFFF"><b><?php echo $allerrors; ?></b></font>
				</td>
			</tr>
			</table>
			<br/>
		<?php } ?>

		<form action="install.php" method="post">
        <table width="100%" style="background: #fcfcfc" style="border: 1px dotted #eee;" align="center" cellpadding="3" cellspacing="3" border="0">
          <tr height="30">
            <td colspan="2" align="center" valign="middle"><b>Enter your license key below</b></td>
          </tr>
          <tr height="30">
            <td colspan="2" align="center" valign="middle">You can find out the license key by logging into your <a target="_blank" href="http://www.jobfia.com/c/login.php">Member Account</a>.</td>
          </tr>
          <tr height="30">
            <td align="right" valign="middle"><b>Your License Key</b>:</td>
            <td align="left" valign="middle"><input type="text" class="form-control" name="license" value="" size="32" maxlength="24" /></td>
          </tr>
          <tr height="40">
			<td colspan="2" align="center" valign="middle">
		  		<input type="hidden" name="action" value="install" />
				<input type="submit" class="btn btn-success" name="install" id="install" value="Start Installation" />
			</td>
          </tr>
        </table>
      </form>

	  <?php } ?>

</td>
</tr>
</table>
</body>
</html>