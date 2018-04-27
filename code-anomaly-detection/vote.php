<?php

require_once "./helpers/mysqli.php";
require_once "./helpers/util.php";

session_start();

$vote = $_REQUEST["vote"];
$anomaly_class_name = $_REQUEST["anomaly_class_name"];
$anomaly_type_id = $_REQUEST["anomaly_type_id"];
$anomaly_filename = $_REQUEST["anomaly_filename"];
$anomaly_gist_id = $_REQUEST["anomaly_gist_id"];
$user_id = $_SESSION['id'];

$vote_variants = array(1, 2, 3, 4, 5);

if (empty($user_id)) {
    output(null, -1, "Not authorized");
}

if (empty($anomaly_class_name)) {
    output(null, -3, "Anomaly class name is not specified");
}

if (empty($anomaly_type_id)) {
    output(null, -4, "Anomaly type id is not specified");
}

if (empty($anomaly_id)) {
    output(null, -5, "Anomaly id is not specified");
}

if (empty($vote) || in_array($vote, $vote_variants)) {
    output(null, -6, "Vote incorrect (from 1 to 5)");
}

$mysqli = mysql_connect();

if (is_null($mysqli)) {
    output(null, -7);
}

$anomaly_type_id_escaped = $mysqli->escape_string($anomaly_type_id);
$anomaly_type_result = $mysqli->query("SELECT * from anomaly_types WHERE id = '$anomaly_type_id_escaped'");

if ($anomaly_type_result->num_rows === 0) {
    output(null, -8, 'Incorrect anomaly type');
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

$anomaly_filename_escaped = $mysqli->escape_string($anomaly_filename);
$anomaly_type_id_escaped = $mysqli->escape_string($anomaly_type_id);
$anomaly_gist_id_escaped = $mysqli->escape_string($anomaly_gist_id);
$anomaly_result = $mysqli->query("SELECT * from anomalies WHERE gist_id = '$anomaly_gist_id_escaped'");

if ($anomaly_result->num_rows === 0) {
    $mysqli->query("INSERT INTO anomalies(class_id, filename, description, gist_id, type_id) VALUES('$class_id', '$anomaly_filename_escaped', '', '$anomaly_gist_id_escaped', '$anomaly_type_id_escaped')");
    $anomaly_id = $mysqli->insert_id;
} else {
    $anomaly = $anomaly_result->fetch_object();
    $anomaly_id = $anomaly->id;
}

$vote_result = $mysqli->query("SELECT * from votes WHERE anomaly_id = '$anomaly_id' AND user_id = '$user_id'");

if ($vote_result->num_rows === 0) {
    $mysqli->query("INSERT INTO votes(anomaly_id, user_id, vote) VALUES('$anomaly_id', '$user_id', '$vote')");
} else {
    $mysqli->query("UPDATE votes SET vote = '$vote' WHERE anomaly_id = '$anomaly_id' AND user_id = '$user_id'");
}