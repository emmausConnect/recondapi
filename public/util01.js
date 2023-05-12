// UTILITAIRE 01

function setCookie(cName, cValue, expDays) {
    let expires
    if (expDays == 0) {
        expires = "expires=0"
    }else{
        let date = new Date();
        date.setTime(date.getTime() + (expDays * 24 * 60 * 60 * 1000));
        expires = "expires=" + date.toUTCString();
    }
    let cookieTxt = encodeURIComponent(cName) + "=" + encodeURIComponent(cValue) + "; " + expires + "; path=/; secure";
    document.cookie = cookieTxt;
}

function getCookie(cName) {
    const name = cName + "=";
    const cDecoded = decodeURIComponent(document.cookie); //to be careful
    const cArr = cDecoded .split('; ');
    let res;
    cArr.forEach(
        val => 
        {
        if (val.indexOf(name) === 0)
        {
            res = val.substring(name.length);
        }
    })
    return res;
}

/** lecture de la valeur de debug dans le cookie
 * 
 * @returns la valeur du debug dans le cookie ou "0"
 */
function getCookieDebug() {
    let cookie_name = "paramsession";
    try {
        let sessioncookies = getCookie(cookie_name);
        let sessioncookiesJson = JSON.parse(sessioncookies);
        return sessioncookiesJson['debug'];
    }catch(err) {
        return 0;
    }

}

function setCookieDebug(debugVal) {
    let cookie_name = "paramsession";
	let sessioncookies = getCookie(cookie_name);
	let sessioncookiesJson = JSON.parse(sessioncookies);
	sessioncookiesJson["debug"]=debugVal;
    let sessioncookiesJsonString = JSON.stringify(sessioncookiesJson);
    setCookie(cookie_name,sessioncookiesJsonString, 0 );
}

function initCookieBrowserSession() {
    let cookie_name = "paramsession";

}
