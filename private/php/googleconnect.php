<?php
declare(strict_types=1);
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../class/loggerrec.class.php';
function googleConnect($action) {
    GLOBAL $logger;
    $client = getGoogleClient();

    // authenticate code from Google OAuth Flow
    switch ($action) {
        case "read" :
            $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
            $client->setAccessToken($token['access_token']);

            // get profile info
            $google_oauth = new Google_Service_Oauth2($client);
            $google_account_info = $google_oauth->userinfo->get();
            $email =  $google_account_info->email;
            $name  =  $google_account_info->name;

            // now you can use this profile info to create account in your website and make user logged in.
            $_SESSION['token'] = $token;
            $_SESSION['emmaususerconnected'] = "Y";
            $_SESSION['name']  = $name;
            $_SESSION['email'] = $email;
            return('OK');
        case "geturl" :
            $url = $client->createAuthUrl();
            return ($url);
    }
    echo 'erreur : googleConnect($action) action invalide : ['.$action.']';
}
function googleDisconnect() {
    GLOBAL $logger;
    // Remove token and user data from the session
    unset($_SESSION['token']);
    unset($_SESSION['emmaususerconnected']);
    unset($_SESSION['name']);
    unset($_SESSION['email']);

    $client = getGoogleClient();
    // Reset OAuth access token
    $client->revokeToken();
    
    // Destroy entire session data
    // session_destroy();
}

function getGoogleClient() {
    GLOBAL $logger;
    // init configuration
    $clientID     = '3892632529-kke64c63fcfamgo7096u3e4uc0mup35l.apps.googleusercontent.com';
    $clientSecret = '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';
	
    $redirectUri = getRedirectUrl();
    // create Client Request to access Google API
    $client = new Google_Client();
    $client->setClientId($clientID);
    $client->setClientSecret($clientSecret);
    $client->setRedirectUri($redirectUri);
    $client->addScope("email");
    $client->addScope("profile");
    //$client->setApprovalPrompt('select_account');
    //$client->setLoginHint('@emmaus-connect.org');
    return $client;
}

function getRedirectUrl() {
    GLOBAL $logger;
	$schema = "";
	if (array_key_exists("REQUEST_SCHEME", $_SERVER)) {
		$schema = $_SERVER["REQUEST_SCHEME"];
	}else if (array_key_exists("SCRIPT_URI", $_SERVER)) {
		preg_match('/(.*):\/\/(.*)/', $_SERVER["SCRIPT_URI"], $a);
		if (count($a) >=2) {
			$schema = $a[1];
		}
	}
	if ($schema == "") {
		echo __FILE__ ." : impossible de savoir si http ou https";
        print_r($_SERVER);
		die;
	}
    if ($schema != "http" && $schema != "https") {
		echo __FILE__ ." : schéma doit être http ou https : [$schema]";
		die;
	}
	
    $redirectUri  = $schema.'://'.$_SERVER["HTTP_HOST"].'/exgoogleconnectdone.php';
    return $redirectUri;
}
