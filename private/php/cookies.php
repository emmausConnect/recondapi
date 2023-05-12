<?php
declare(strict_types=1);
/**
 * gestion du cookie BrowserSession "paramsession".
 * Il contient une chaîne Json.
 * {
 *    debug = x
 * }
 * 
 * 
 */

/**
 * initialisation du cookie "paramsession"
 */
function initCookieBrowserSession() {
    $cookie_name = "paramsession";
    //setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/");

    if (!isset($_COOKIE[$cookie_name])) {
        setcookie($cookie_name, '{"debug":0}', 0, "/","",false,false); // expire en fin de session du navigateur
    }
}


/**
 * test si le BrowserSessionCookie existe
 */
function checkFromBrowserSessionCookie() {
    $cookie_name = "paramsession";
    if (isset($_COOKIE[$cookie_name])) {
        return true;
    }else{
        return false;
    }
}

/**
 * extrait le  BrowserSessionCookie
 * 
 * @return BrowserSessionCookie comme un array ou null si non trouvé ou erreur de Json
 */
function getFromBrowserSessionCookieAsArray() {
    $cookie_name = "paramsession";
    $retour = null;
    if (isset($_COOKIE[$cookie_name])) {
        $value = $_COOKIE[$cookie_name]; // c'est une chaine JSon
        try {
            $valueJson = json_decode($value, true);
            $retour = $valueJson['debug'];
        }catch (Exception $ex) {
            $retour = null;
        }
    }
    return $retour;
}

/**
 * extrait la valeur du paramètre "debug".
 * 
 * @return la bvaleur trouvée ou "0" si non trouvé ou erreur.
 */
function getDebugFromBrowserSessionCookie() {
    $cookie_name = "paramsession";
    $retour = "0";
    if (isset($_COOKIE[$cookie_name])) {
        $value = $_COOKIE[$cookie_name]; // c'est une chaine JSon
        try {
            $valueJson = json_decode($value, true);
            $retour = "".$valueJson['debug'];
        }catch (Exception $ex) {
            $retour = "0";
        }
    }
    return $retour;
}
