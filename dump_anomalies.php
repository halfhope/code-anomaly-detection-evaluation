<?php

require_once "./helpers/mysqli.php";
require_once "./helpers/util.php";

define("CSV_DELIMITER", ",");

$email = $_REQUEST["email"];
$type_id = $_REQUEST["type_id"];

if (empty($email)) {
    output(null, -1, "E-mail is not specified");
}

$mysqli = mysql_connect();

if (is_null($mysqli)) {
    output(null, -2);
}

$email_escaped = $mysqli->escape_string($email);
$type_id_escaped = $mysqli->escape_string($type_id);

$type_condition = $type_id ? " AND type_id = '$type_id_escaped'" : "";
$vote_result = $mysqli->query("SELECT anomalies.filename as anomaly_name, anomaly_classes.name as class_name, anomaly_types.name as type_name, vote FROM votes JOIN users ON user_id = users.id JOIN anomalies ON anomaly_id = anomalies.id JOIN anomaly_classes ON anomalies.class_id = anomaly_classes.id JOIN anomaly_types ON anomalies.type_id = anomaly_types.id WHERE users.email = '$email_escaped'$type_condition");
$votes = $vote_result->fetch_all();

$csv = [];

foreach ($votes as $vote) {
    $csv[] = implode(CSV_DELIMITER, $vote);
}

header("Content-type: text/csv");
header("Content-Disposition: attachment; filename=dump.csv");

echo implode(PHP_EOL, $csv);
