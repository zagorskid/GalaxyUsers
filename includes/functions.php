<?php

/**
 * Script finished normally, no errors occurred
 */
function exitSuccess() {
    echo "0";
}


/**
 * Script finished with errors
 */
function exitFail($error, $exit) {
    echo "1;" . $error;
    log_event("ERROR", $error);
    
    if ($exit) {
        exit();
    }
}


/**
 * Function handling logging messages to file
 */
function log_event($type, $message) {
    error_log("[" . date("Y.m.d H:i:s") . "] $type: " . $message . "\n", 
            3, "log/event.log");   
}

/**
 * Function generating verification sum
 */
function generateMD5Sum($galaxySalt, $username, $timestamp, $passwd, $action, $email) {
    return md5($galaxySalt . $username . $galaxySalt . $timestamp . $galaxySalt . $passwd . $galaxySalt . $action . $galaxySalt . $email . $galaxySalt);
}

?>
