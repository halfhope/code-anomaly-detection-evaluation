<?php

require_once "./helpers/mysqli.php";
require_once "./helpers/util.php";

session_start();

$anomaly_class_name = $_REQUEST["anomaly_class_name"];
$user_id = $_SESSION['id'];

if (empty($user_id)) {
    output(null, -1, "Not authorized");
}

if (empty($anomaly_class_name)) {
    output(null, -2, "Anomaly class name is not specified");
}

$mysqli = mysql_connect();

if (is_null($mysqli)) {
    output(null, -4);
}

$anomaly_class_name_escaped = $mysqli->escape_string($anomaly_class_name);

$votes_result = $mysqli->query("SELECT gist_id, vote FROM votes JOIN users ON user_id = users.id JOIN anomalies ON anomaly_id = anomalies.id JOIN anomaly_classes ON anomalies.class_id = anomaly_classes.id JOIN anomaly_types ON anomalies.type_id = anomaly_types.id WHERE anomaly_classes.name = '$anomaly_class_name_escaped' AND users.id = '$user_id'");
$votes = [];

while ($vote = $votes_result->fetch_object()) {
    $votes[] = $vote;
}

$votes_class_result = $mysqli->query("SELECT vote FROM votes_classes JOIN users ON user_id = users.id JOIN anomaly_classes ON votes_classes.class_id = anomaly_classes.id WHERE anomaly_classes.name = '$anomaly_class_name_escaped' AND users.id = '$user_id'");
$votes_class = $votes_class_result->fetch_object();

output(array(
    "anomalies" => $votes,
    "class"     => $votes_class ?? $votes_class->vote
), 0);