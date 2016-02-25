<?php

include "includes/functions.php";
include "config/dbconf.php";

//Debug config
$debug = false;

// Get post data
$action = filter_input(INPUT_POST, "ACTION", FILTER_UNSAFE_RAW);
$username = filter_input(INPUT_POST, "USERNAME", FILTER_UNSAFE_RAW);
$email = filter_input(INPUT_POST, "EMAIL", FILTER_UNSAFE_RAW);
$passwd = filter_input(INPUT_POST, "PASSWD", FILTER_UNSAFE_RAW);
$timestamp = filter_input(INPUT_POST, "TIMESTAMP", FILTER_UNSAFE_RAW);
$md5 = filter_input(INPUT_POST, "MD5", FILTER_UNSAFE_RAW);

if ($debug) {
    echo "Post data:\n";
    echo "Action: " . $action . "\n";
    echo "Username: " . $username . "\n";
    echo "Email: " . $email . "\n";
    echo "Password: " . $passwd . "\n";
    echo "Timestamp: " . $timestamp . "\n";
    echo "MD5: " . $md5 . "\n";
    echo "\n";
}

// validate form data
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exitFail("Niepoprawny adres email.", true);
}
if (empty($username)) {
    exitFail("Nie przeslano nazwy uzytkownika.", true);
}
if (!preg_match("/^[a-zA-Z0-9-]+$/", $username)) {
    exitFail("Nieprawidlowy format nazwy uzytkownika. Dopuszczalne sa jedynie litery, cyfry oraz znak \"-\".", true);
}
if (empty($passwd)) {
    exitFail("Nie przeslano hasla.", true);
}
if (empty($timestamp)) {
    $timestamp = time();
}


// TODO: add MD5 verification



// check if action is defined
if (!empty($action)) {

    // validate action value
    $action = strtoupper($action);

    if ($action == "ENABLE") {
        // add new account or modify existing one
        
        // get DB connection
        $dbconn = pg_connect("host=$dbhost port=$dbport dbname=$dbname user=$dbuser password=$dbpassword")
                or die("1;Blad polaczenia z baza danych: " . pg_last_error());

        // check if accout with given id already exists        
        $query = sprintf("SELECT * FROM galaxy_user WHERE username='%s';", pg_escape_string($username));
        $result = pg_query($query);

        $numberOfUsersFound = pg_numrows($result);

        if ($numberOfUsersFound == 0) {
            // add new user
            $queryAdd = sprintf("INSERT INTO galaxy_user(create_time, update_time, email, password, "
                    . "external, deleted, purged, username, active) VALUES("
                    . "'%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s');",
                    date('Y-m-d H:i:s', $timestamp),
                    date('Y-m-d H:i:s', $timestamp),
                    pg_escape_string($email),
                    $passwd,
                    'f',
                    'f',
                    'f',
                    pg_escape_string($username),
                    't'
                    );
            $resultAdd = pg_query($queryAdd);
            
            if (!$resultAdd) {
                exitFail("Blad dodawania nowego uzytkownika do bazy: " . pg_last_error($dbconn), false);
            } else {
                log_event("INFO", "Dodano nowego uzytkownika do bazy: $username ($email).");
                exitSuccess();                
            }            
            pg_free_result($resultAdd);          
            
        } elseif ($numberOfUsersFound == 1) {
            // modify existing user
            $record = pg_fetch_array($result, null, PGSQL_ASSOC);

            if ($record["email"] != $email) {
                // email change
                $queryUpdate = sprintf("UPDATE galaxy_user SET update_time='%s', email='%s' WHERE username='%s';",
                        date('Y-m-d H:i:s', $timestamp),
                        pg_escape_string($email),
                        pg_escape_string($username)
                        );                
                $resultUpdate = pg_query($queryUpdate);
                
                if (!$resultUpdate) {
                    exitFail("Blad aktualizacji emaila uzytkownika: " . pg_last_error($dbconn), false);
                } else {
                    log_event("INFO", "Zmieniono adres email dla uzytkownika: $username.");
                    exitSuccess();                
                }
                pg_free_result($resultUpdate);               
            } 
            
            if ($record["password"] != $passwd) {
                // password change
                $queryUpdate = sprintf("UPDATE galaxy_user SET update_time='%s', password='%s' WHERE username='%s';",
                        date('Y-m-d H:i:s', $timestamp),
                        $passwd,
                        pg_escape_string($username)
                        );                
                $resultUpdate = pg_query($queryUpdate);
                
                if (!$resultUpdate) {
                    exitFail("Blad aktualizacji hasla uzytkownika: " . pg_last_error($dbconn), false);
                } else {
                    log_event("INFO", "Zmieniono haslo dla uzytkownika: $username.");
                    exitSuccess();                
                }
                pg_free_result($resultUpdate);               
            }
            
            if ($record["deleted"] == "t") {
                // activate account
                $queryUpdate = sprintf("UPDATE galaxy_user SET update_time='%s', deleted='%s' WHERE username='%s';",
                        date('Y-m-d H:i:s', $timestamp),
                        "f",
                        pg_escape_string($username)
                        );                
                $resultUpdate = pg_query($queryUpdate);
                
                if (!$resultUpdate) {
                    exitFail("Blad aktywacji konta uzytkownika: " . pg_last_error($dbconn), false);
                } else {
                    log_event("INFO", "Aktywowano konto uzytkownika uzytkownika: $username.");
                    exitSuccess();                
                }
                pg_free_result($resultUpdate);
            }
            
            if ($record["email"] == $email && $record["password"] == $passwd && $record["deleted"] == "f") {
                // nothing to do
                log_event("INFO", "Wyslano formularz dla uzytkownika: $username. Nic nie zostalo zmienione.");
                exitSuccess();
            }          
            
        } else {
            // something went wrong...
            exitFail("Blad wyszukiwania uzytkownika w bazie. Sprawdz unikalnosc identyfikatorow w bazie.", false);
        }
        pg_free_result($result);        
        // Close DB connection
        pg_close($dbconn);
        
        
        
    } elseif ($action == "DISABLE") {
        // disable account
        
        // get DB connection
        $dbconn = pg_connect("host=$dbhost port=$dbport dbname=$dbname user=$dbuser password=$dbpassword")
                or die("1;Blad polaczenia z baza danych: " . pg_last_error());

        // check if accout with given id already exists        
        $query = sprintf("SELECT * FROM galaxy_user WHERE username='%s';", pg_escape_string($username));
        $result = pg_query($query);

        $numberOfUsersFound = pg_numrows($result);

        if ($numberOfUsersFound == 0) {
            // something went wrong...
            exitFail("Blad wyszukiwania uzytkownika w bazie. Brak danych dla podanej nazwy uzytkownika.", false);
        } elseif ($numberOfUsersFound == 1) {
            // disable account
            $record = pg_fetch_array($result, null, PGSQL_ASSOC);
            
            if ($record["deleted"] == "f") {           
                $queryUpdate = sprintf("UPDATE galaxy_user SET update_time='%s', deleted='%s' WHERE username='%s';", 
                        date('Y-m-d H:i:s', $timestamp), 
                        "t", 
                        pg_escape_string($username)
                );
                $resultUpdate = pg_query($queryUpdate);

                if (!$resultUpdate) {
                    exitFail("Blad dezaktywacji konta uzytkownika: " . pg_last_error($dbconn), false);
                } else {
                    log_event("INFO", "Dezaktywowano konto uzytkownika uzytkownika: $username.");
                    exitSuccess();
                }
                pg_free_result($resultUpdate);
            } else {
                // nothing to do
                log_event("INFO", "Wyslano formularz dla uzytkownika: $username. Nic nie zostalo zmienione.");
                exitSuccess();
            }
            
        } else {
            // something went wrong...
            exitFail("Blad wyszukiwania uzytkownika w bazie. Sprawdz unikalnosc identyfikatorow w bazie.", false);
        }
        pg_free_result($result);        
        // Close DB connection
        pg_close($dbconn);
        
        
    } else {
        // unknown action
        exitFail("Nieznana akcja.", false);
    }
} else {
    exitFail("Parametr ACTION jest pusty.", false);
}
?>