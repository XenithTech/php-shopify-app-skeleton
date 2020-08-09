<?php

	//AS - App Setup

	include 'config.php';

	$query = array();
	parse_str($_SERVER['QUERY_STRING'], $query);
	$shop = $_GET['shop'];
	$code = $_GET['code'];
	$hmac = $_GET['hmac'];
	$nonce = $_GET['state'];

	$query_no_hmac = $query;
	unset($query_no_hmac['hmac']);

	$message = http_build_query($query_no_hmac);

	if(verifyHMAC()){
		$client_id = processClient($shop);

		if($client_id == -1){
			die("Unable to process request. ERROR: PO-R-1");
		}

		if(verifyNonce()){
			if(verifyHost()){

				$query = array(
					"client_id" => $k,
					"client_secret" => $s,
					"code" => $code
				);

				$access_token_url = "https://" . $_GET['shop'] . "/admin/oauth/access_token";

				$ch = curl_init();
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
				curl_setopt($ch, CURLOPT_URL, $access_token_url);
				curl_setopt($ch, CURLOPT_POST, count($query));
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($query));
				$result = curl_exec($ch);
				curl_close($ch);
				$result = json_decode($result, true);
				$access_token = $result['access_token'];

		        storeToken($client_id, $access_token);

				$hmac = generateHMAC($client_id);

		        header("Location: ".$redirect_url."?shop=".$_GET['shop']."&hmac=".$hmac);
			}
			else{
				die("Unable to process request. ERROR: PO-R-2");
			}
		}
		else{
			die("Unable to process request. ERROR: PO-R-3");
		}

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

	function verifyNonce(){

		global $sn, $dn, $un, $pw, $client_id, $nonce;
		
		$dsn = "mysql:host=".$sn.";dbname=".$dn.";charset=utf8";
		$opt = array(
			PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
		);

		$return_check = $pdo = new PDO($dsn, $un, $pw, $opt);
		if($return_check === false){
			die("Unable to process request. ERROR: PO-VN-1");
		}
		$return_check = $stm = $pdo->prepare("SELECT nonce FROM client_stores WHERE client_id = ?");
		if($return_check === false){
			die("Unable to process request. ERROR: PO-VN-2");
		}
		$return_check = $stm->execute(array($client_id));

		if($return_check === false){
			die("Unable to process request. ERROR: PO-VN-3");
		}

		$result = $stm->fetchAll();

		if(count($result) !== 0){
			$check = $result[0]['nonce'];
		}
		else{
			die("Unable to process request. ERROR: PO-VN-4");
		}

		$return_check = $stm = $pdo->prepare("UPDATE client_stores SET nonce = '' WHERE client_id = ?");
		if($return_check === false){
			echo "Unable to process request. ERROR: PO-VN-5";
			die();
		}
		$return_check = $stm->execute(array($client_id));

		if($return_check === false){
			echo "Unable to process request. ERROR: PO-VN-6";
			die();
		}

		if($nonce == $check){
			return true;
		}

		return false;
	}

	function verifyHost(){
		global $shop;
		if(endswith($shop, '.myshopify.com')){
			$shop = str_replace('.myshopify.com', '', $shop);
			if(preg_match('/[a-z\.\-0-9]/i', $shop)){
				return true;
			}

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
			die("Unable to process request. ERROR: PO-PC-1");
		}
		$return_check = $stm = $pdo->prepare("SELECT client_id FROM clients WHERE client_name = ?");
		if($return_check === false){
			die("Unable to process request. ERROR: PO-PC-2");
		}
		$return_check = $stm->execute(array(str_replace('.myshopify.com', '', $shop)));

		if($return_check === false){
			die("Unable to process request. ERROR: PO-PC-3");
		}

		$result = $stm->fetchAll();

		if(count($result) !== 0){
			return $result[0]['client_id'];
		}
		else{
			die("Unable to process request. ERROR: PO-PC-4");
		}
	}

	function generateHMAC($client_id){
		global $s;

		$nonce = generateNonce($client_id);

		$hmac = hash_hmac('sha256', $nonce, $s);

		storeHMAC($client_id, $hmac);

		return $hmac;
	}


	function storeHMAC($client_id, $hmac){
		global $sn, $dn, $un, $pw;
		
		$dsn = "mysql:host=".$sn.";dbname=".$dn.";charset=utf8";
		$opt = array(
			PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
		);

		ini_set('display_errors', 'Off');

		$return_check = $pdo = new PDO($dsn, $un, $pw, $opt);
		if($return_check === false){
			echo "Unable to process request. ERROR: PO-SH-1";
			die();
		}
		$return_check = $stm = $pdo->prepare("UPDATE client_stores SET hmac = ?, last_activity = NOW() WHERE client_id = ?");
		if($return_check === false){
			echo "Unable to process request. ERROR: PO-SH-2";
			die();
		}
		$return_check = $stm->execute(array($hmac, $client_id));

		if($return_check === false){
			echo "Unable to process request. ERROR: PO-SH-3";
			die();
		}
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

		ini_set('display_errors', 'Off');

		$return_check = $pdo = new PDO($dsn, $un, $pw, $opt);
		if($return_check === false){
			echo "Unable to process request. ERROR: PO-SN-1";
			die();
		}
		$return_check = $stm = $pdo->prepare("UPDATE client_stores SET nonce = ? WHERE client_id = ?");
		if($return_check === false){
			echo "Unable to process request. ERROR: PO-SN-2";
			die();
		}
		$return_check = $stm->execute(array($nonce, $client_id));

		if($return_check === false){
			echo "Unable to process request. ERROR: PO-SN-3";
			die();
		}
	}

	function storeToken($client_id, $token){
		global $sn, $dn, $un, $pw;
		
		$dsn = "mysql:host=".$sn.";dbname=".$dn.";charset=utf8";
		$opt = array(
			PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
		);

		ini_set('display_errors', 'Off');

		$return_check = $pdo = new PDO($dsn, $un, $pw, $opt);
		if($return_check === false){
			die("Unable to process request. ERROR: PO-ST-1");
		}
		$return_check = $stm = $pdo->prepare("UPDATE client_stores SET token = ? WHERE client_id = ?");
		if($return_check === false){
			die("Unable to process request. ERROR: PO-ST-2");
		}
		$return_check = $stm->execute(array($token, $client_id));

		if($return_check === false){
			die("Unable to process request. ERROR: PO-ST-3");

		}
	}

	function endswith($string, $test) {
	    $strlen = strlen($string);
	    $testlen = strlen($test);
	    if ($testlen > $strlen) return false;{
	    	return substr_compare($string, $test, $strlen - $testlen, $testlen) === 0;
	    }
	}

?>