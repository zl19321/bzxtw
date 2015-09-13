<?php
return array(
	'app_begin' => array('init_sesstion'),	
	'after_content_add' => array(
		'seo_ping',// seo ping
		'clear_html'
	),
);