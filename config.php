<?php 
	#Database configuration
	$sn = '';
	$un = '';
	$pw = '';
	$dn = '';

	#Shopify configuration
	$k = '';		#App Key
	$s = '';		#App Secret
	$redirect_url = 'https://yourapplocation/index.php';	#Redirect URL for after handshake
	$permissions = array(
		'read_orders',										#List what ever permissions your app will need here
		'read_script_tags',
		'write_script_tags'
	);
?>