<?php

// include functions
include "includes/functions.php";

// get DB connection
include "config/dbconf.php";
$dbconn = pg_connect("host=" . $dbhost
        . " port=" . $dbport
        . " dbname=" . $dbname
        . " user=" . $dbuser
        . " password=" . $dbpassword
        )
    or die("Unable to connect with database: " . pg_last_error());

// SQL query
$query = 'SELECT * FROM galaxy_user ORDER BY id ASC';
$result = pg_query($query) or die('SQL error: ' . pg_last_error());


// Wyświetlenie wyników w postaci czystego tekstu oddzielonego średnikami
while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
    foreach ($line as $col_value) {
        echo "$col_value;";
    }
    echo "\n";
}
echo "\n";

// Free resultset
pg_free_result($result);

// Close DB connection
pg_close($dbconn);

?>

