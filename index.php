<?php

echo 'Test';

include "config/dbconf.php";

// get DB connection
$dbconn = pg_connect("host=" . $dbhost
        . " port=" . $dbport
        . " dbname=" . $dbname
        . " user=" . $dbuser
        . " password=" . $dbpassword
        )
    or die('Unable to connect with database: ' . pg_last_error());

// SQL query
$query = 'SELECT * FROM galaxy_user';
$result = pg_query($query) or die('SQL error: ' . pg_last_error());


echo 'Num rows: ' . pg_numrows($result) . "\n";

// Wyświetlenie wyników w postaci HTML
echo "<table>\n";
while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
    echo "\t<tr>\n";
    foreach ($line as $col_value) {
        echo "\t\t<td>$col_value</td>\n";
    }
    echo "\t</tr>\n";
}
echo "</table>\n";

// Free resultset
pg_free_result($result);

// Close DB connection
pg_close($dbconn);

?>