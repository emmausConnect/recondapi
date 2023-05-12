<?php
declare(strict_types=1);
/** *** gestion des erreurs *******
 * errno
 *  The first parameter, errno, will be passed the level of the error raised, as an integer. 
 * errstr
 *  The second parameter, errstr, will be passed the error message, as a string. 
 * errfile
 *  If the callback accepts a third parameter, errfile, it will be passed the filename that the error was raised in, as a string. 
 * errline
 *  If the callback accepts a fourth parameter, errline, it will be passed the line number where the error was raised, as an integer. 
 * errcontext
 *  If the callback accepts a fifth parameter, errcontext, it will be passed an array that points to the active symbol table at the point the error occurred. In other words, errcontext will contain an array of every variable that existed in the scope the error was triggered in. User error handlers must not modify the error context. 
 * 
 *********************************************** */
function errormanagement_customError($errno, $errstr, $errorfile, $errorline) {

	// Sends an error message to the web server's error log or to a file.
    $errorFile = '../work/logfiles/error.log';
    if (! file_exists($errorFile)) {
        $f = fopen($errorFile, "x+");
        fclose($f);
    }
    //echo "ligne c : ".__LINE__.'<br>';
    $msg = date("Y/m/d H:i:s")." : ERROR : errorfile : $errorfile,  errorline : $errorline, errno : $errno, errstr : $errstr\n";

    file_put_contents("../work/logfiles/error.log", "file_put_contents $msg", FILE_APPEND);

	//error_log($msg,0);
    //Sends an error message to the web server's error log or to a file. 
    // 0 	message is sent to PHP's system logger, using the Operating System's system logging mechanism or a file, depending on what the error_log configuration directive is set to. This is the default option.
    // 1 	message is sent by email to the address in the destination parameter. This is the only message type where the fourth parameter, additional_headers is used.
    // 2 	No longer an option.
    // 3 	message is appended to the file destination. A newline is not automatically added to the end of the message string.
    // 4 	message is sent directly to the SAPI logging handler. 
    error_log($msg,3,"../work/logfiles/error.log");

	// If the function returns false then the normal error handler continues.
	return true;
}

function getErrorReporing() {
    /**    error reporting :
     https://www.cloudways.com/blog/php-debug/
    In the php.ini file, you can enable it easily. Do remember that you must remove; from each starting line.

        error_reporting = E_ALL & ~E_NOTICE
        error_reporting = E_ALL & ~E_NOTICE | E_STRICT
        error_reporting = E_COMPILE_ERROR|E_RECOVERABLE_ERROR|E_ER… _ERROR
        error_reporting = E_ALL & ~E_NOTICE
        
    To enable PHP error logging for the current call in the individual file, you can write the following code on the top:

        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

    But still, you need to enable this line in the php.ini file for reporting parse errors:

        display_errors = on	
    */
    //error_reporting(E_ALL ^ E_WARNING);

    /** https://www.php.net/manual/en/function.set-error-handler.php
     *  set_error_handler(?callable $callback, int $error_levels = E_ALL): ?callable
     */
    set_error_handler("errormanagement_customError");
 }
?>