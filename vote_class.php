<?php

require_once "./helpers/mysqli.php";
require_once "./helpers/util.php";

session_start();

$vote = $_REQUEST["vote"];
$anomaly_class_name = $_REQUEST["anomaly_class_name"];
$user_id = $_SESSION['id'];

$vote_variants = array(1, 2, 3, 4, 5);

if (empty($user_id)) {
    output(null, -1, "Not authorized");
}

if (empty($anomaly_class_name)) {
    output(null, -3, "Anomaly class name is not specified");
}

if (empty($vote) || !in_array($vote, $vote_variants)) {
    output(null, -6, "Vote incorrect (from 1 to 5)");
}

$mysqli = mysql_connect();

if (is_null($mysqli)) {
    output(null, -7);
}

$anomaly_class_name_escaped = $mysqli->escape_string($anomaly_class_name);
$anomaly_class_result = $mysqli->query("SELECT * from anomaly_classes WHERE name = '$anomaly_class_name_escaped'");

if ($anomaly_class_result->num_rows === 0) {
    $mysqli->query("INSERT INTO anomaly_classes(name) VALUES('$anomaly_class_name_escaped')");
    $class_id = $mysqli->insert_id;
} else {
    $class = $anomaly_class_result->fetch_object();
    $class_id = $class->id;
}

$vote_result = $mysqli->query("SELECT * from votes_classes WHERE class_id = '$class_id' AND user_id = '$user_id'");

if ($vote_result->num_rows === 0) {
    $mysqli->query("INSERT INTO votes_classes(class_id, user_id, vote) VALUES('$class_id', '$user_id', '$vote')");
    output(array("status" => "added"));
} else {
    $mysqli->query("UPDATE votes_classes SET vote = '$vote' WHERE class_id = '$class_id' AND user_id = '$user_id'");
    output(array("status" => "updated"));
}