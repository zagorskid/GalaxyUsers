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

echo "<h1>Galaxy users</h1>\n";
echo "<h3>Liczba uzytkownikow: " . pg_numrows($result) . "</h3>\n";

// Wyświetlenie wyników w postaci HTML
?>
<table border="1">
<thead>
    <tr>
        <td>Id</td>
        <td>Create time</td>
        <td>Update time</td>
        <td>E-mail</td>
        <td>Password</td>
        <td>External</td>
        <td>Deleted</td>
        <td>Purged</td>
        <td>Username</td>
        <td>Form values id</td>
        <td>Disk usage</td>
        <td>Active</td>
        <td>Activation token</td>
    </tr>
</thead>
<?php

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

