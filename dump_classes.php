<?php

require_once "./helpers/mysqli.php";
require_once "./helpers/util.php";

define("CSV_DELIMITER", ",");

$email = $_REQUEST['email'];

if (empty($email)) {
    output(null, -1, "Not authorized");
}

$mysqli = mysql_connect();

if (is_null($mysqli)) {
    output(null, -4);
}


$votes_result = $mysqli->query("SELECT anomaly_classes.name as class_name, vote FROM votes_classes JOIN users ON user_id = users.id JOIN anomaly_classes ON anomaly_classes.id = votes_classes.class_id WHERE users.email = '$email'");
$votes = $votes_result->fetch_all();

$csv = [];

foreach ($votes as $vote) {
    $csv[] = implode(CSV_DELIMITER, $vote);
}

header("Content-type: text/csv");
header("Content-Disposition: attachment; filename=dump.csv");

echo implode(PHP_EOL, $csv);