<?php

	//UA - User Authentication

	include 'config.php';

	$query = array();
	parse_str($_SERVER['QUERY_STRING'], $query);
	$shop = str_replace('.myshopify.com', '', $_GET['shop']);
	$hmac = $_GET['hmac'];

	$query_no_hmac = $query;
	unset($query_no_hmac['hmac']);

	$message = http_build_query($query_no_hmac);

	if(verifyHMAC()){
		$client_id = processClient($shop);
		$nonce = generateNonce($client_id);

		if($client_id == -1){
			echo "Unable to process request. ERROR: O-R-1";
			die();
		}

		header("Location: https://".$shop.".myshopify.com/admin/oauth/authorize?client_id=".$k."&scope=".implode(',', $permissions)."&redirect_uri=https://phptestapp.xenithtech.com/postoauth.php&state=".$nonce);

	}

	function verifyHMAC(){
		global $s;
		global $message;
		global $hmac;

		$check = hash_hmac('sha256', $message, $s);

		if($check == $hmac){
			return true;
		}
		else{
			return false;
		}

	}

	function processClient($shop){

		global $sn, $dn, $un, $pw;

		$client_id = -1;

		$dsn = "mysql:host=".$sn.";dbname=".$dn.";charset=utf8";
		$opt = array(
			PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
		);



		$return_check = $pdo = new PDO($dsn, $un, $pw, $opt);
		if($return_check === false){
			echo "Unable to process request. ERROR: O-PC-1";
			die();
		}
		$return_check = $stm = $pdo->prepare("SELECT client_id FROM clients WHERE client_name = ?");
		if($return_check === false){
			echo "Unable to process request. ERROR: O-PC-2";
			die();
		}
		$return_check = $stm->execute(array($shop));

		if($return_check === false){
			echo "Unable to process request. ERROR: O-PC-3";
			die();
		}

		$result = $stm->fetchAll();

		if(count($result) !== 0){
			$client_id = $result[0]['client_id'];
		}
		else{
			$client_id = createClient($shop);
		}

		if($client_id == -1){
			echo "Unable to process request. ERROR: O-PC-4";
			die();
		}

		$return_check = $stm = $pdo->prepare("SELECT store_id FROM client_stores WHERE client_id = ?");
		if($return_check === false){
			echo "Unable to process request. ERROR: O-PC-5";
			die();
		}
		$return_check = $stm->execute(array($client_id));

		if($return_check === false){
			echo "Unable to process request. ERROR: O-PC-6";
			die();

		}

		$result = $stm->fetchAll();

		if(count($result) == 0){
			$return_check = $stm = $pdo->prepare("INSERT INTO client_stores (client_id, store_name, url) VALUES (?, ?, ?)");

			if($return_check === false){
				echo "Unable to process request. ERROR: O-PC-7";
				die();
			}
			$return_check = $stm->execute(array($client_id, $shop, "https://".$shop.".myshopify.com/"));
			if($return_check === false){
				echo "Unable to process request. ERROR: O-PC-8";
				die();
			}
		}

		return $client_id;
	}

	function createClient($shop){
		global $sn, $dn, $un, $pw;

		$dsn = "mysql:host=".$sn.";dbname=".$dn.";charset=utf8";
		$opt = array(
			PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
		);

		$return_check = $pdo = new PDO($dsn, $un, $pw, $opt);
		if($return_check === false){
			echo "Unable to process request. ERROR: O-CC-1";
			die();
		}

		$return_check = $stm = $pdo->prepare("INSERT INTO clients (client_name) VALUES (?)");

		if($return_check === false){
			echo "Unable to process request. ERROR: O-CC-2";
			die();
		}
		$return_check = $stm->execute(array($shop));
		if($return_check === false){
			echo "Unable to process request. ERROR: O-CC-3";
			die();
		}

		$return_check = $stm = $pdo->prepare("SELECT client_id FROM clients WHERE client_name = ?");
		if($return_check === false){
			echo "Unable to process request. ERROR: O-CC-4";
			die();
		}
		$return_check = $stm->execute(array($shop));

		if($return_check === false){
			echo "Unable to process request. ERROR: O-CC-5";
			die();

		}

		$result = $stm->fetchAll();

		if(count($result) !== 0){
			return $result[0]['client_id'];
		}

		return -1;
	}

	function generateNonce($client_id){
	    $nonce = hash('sha256', makeRandomString());
	    storeNonce($client_id, $nonce);
	    return $nonce;
	}

	function makeRandomString($bits = 256) {
	    $bytes = ceil($bits / 8);
	    $return = '';
	    for ($i = 0; $i < $bytes; $i++) {
	        $return .= chr(mt_rand(0, 255));
	    }
	    return $return;
	}

	function storeNonce($client_id, $nonce){
		global $sn, $dn, $un, $pw;

		$dsn = "mysql:host=".$sn.";dbname=".$dn.";charset=utf8";
		$opt = array(
			PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
		);

		$return_check = $pdo = new PDO($dsn, $un, $pw, $opt);
		if($return_check === false){
			echo "Unable to process request. ERROR: O-SN-1";
			die();
		}
		$return_check = $stm = $pdo->prepare("UPDATE client_stores SET nonce = ? WHERE client_id = ?");
		if($return_check === false){
			echo "Unable to process request. ERROR: O-SN-2";
			die();
		}
		$return_check = $stm->execute(array($nonce, $client_id));

		if($return_check === false){
			echo "Unable to process request. ERROR: O-SN-3";
			die();

		}
	}

?>