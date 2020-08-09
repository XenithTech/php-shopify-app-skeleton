<?php

	//UA - User Authentication

	include 'config.php';

	$shop = str_replace('.myshopify.com', '', $_GET['shop']);
	$hmac = $_GET['hmac'];
	$client_id = getClientId($shop);
	if(!verifyHMAC($hmac, $client_id)){
		echo "Unable to process request. ERROR: I-R-1";
		die();
	}

	function getClientId($shop){

		global $sn, $dn, $un, $pw;

		$client_id = -1;

		$dsn = "mysql:host=".$sn.";dbname=".$dn.";charset=utf8";
		$opt = array(
			PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
		);

		$return_check = $pdo = new PDO($dsn, $un, $pw, $opt);

		if($return_check === false){
			echo "Unable to process request. ERROR: I-GCI-1";
			die();
		}
		$return_check = $stm = $pdo->prepare("SELECT client_id FROM clients WHERE client_name = ?");

		if($return_check === false){
			echo "Unable to process request. ERROR: I-GCI-2";
			die();
		}
		$return_check = $stm->execute(array($shop));

		if($return_check === false){
			echo "Unable to process request. ERROR: I-GCI-3";
			die();
		}
		$result = $stm->fetchAll();

		if(count($result) !== 0){
			$client_id = $result[0]['client_id'];
		}
		else{
			echo "Unable to process request. ERROR: I-GCI-4";
			die();
		}

		return $client_id;
	}

	function verifyHMAC($hmac, $client_id){

		global $sn, $dn, $un, $pw, $s;
				
		$dsn = "mysql:host=".$sn.";dbname=".$dn.";charset=utf8";
		$opt = array(
			PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
		);

		$return_check = $pdo = new PDO($dsn, $un, $pw, $opt);
		if($return_check === false){
			die("Unable to process request. ERROR: I-VH-1");
		}
		$return_check = $stm = $pdo->prepare("SELECT nonce FROM client_stores WHERE client_id = ? AND last_activity >= NOW() - INTERVAL 10 SECOND AND active = 1");
		if($return_check === false){
			die("Unable to process request. ERROR: I-VH-2");
		}
		$return_check = $stm->execute(array($client_id));

		if($return_check === false){
			die("Unable to process request. ERROR: I-VH-3");
		}

		$result = $stm->fetchAll();

		if(count($result) !== 0){
			$nonce = $result[0]['nonce'];
		}
		else{
			die("Unable to process request. ERROR: I-VH-4");
		}

		$check = hash_hmac('sha256', $nonce, $s);

		if($check == $hmac){
			return true;
		}
		else{
			return false;
		}
	}
?>

<html>
	<head>
	</head>
	<body>
		<center>
			<h1>Welcome to My App, <?php echo $shop ?>! (ID: <?php echo $client_id ?>)</h1>
		</center>
	</body>
</html>