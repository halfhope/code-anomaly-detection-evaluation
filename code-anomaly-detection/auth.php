<?php

header('Access-Control-Allow-Origin: https://petukhovvictor.github.io/', false);

require_once "./helpers/mysqli.php";
require_once "./helpers/util.php";

session_start();

if (!empty($_SESSION['id'])) {
    output(array("code" => $_SESSION['code']));
}

$email = $_REQUEST["email"];
$access_code = $_REQUEST["code"];

if (empty($email)) {
    output(null, -1, 'Not specified email');
}

$mysqli = mysql_connect();

if (is_null($mysqli)) {
    output(null, -2);
}

$email_escaped = $mysqli->escape_string($email);
$access_code_escaped = $mysqli->escape_string($access_code);

if ($result = $mysqli->query("SELECT * from users WHERE email = '$email_escaped'")) {
    $user_exist = $result->num_rows !== 0;

    if ($user_exist && empty($access_code)) {
        output(null, -3, 'Not specified access code');
    } elseif ($user_exist) {
        $result = $mysqli->query("SELECT * from users WHERE email = '$email_escaped' AND code = '$access_code_escaped'");
        if ($result && $result->num_rows === 0) {
            output(null, -4, 'Access code is not correct');
        } else {
            $user = $result->fetch_object();
            $_SESSION['id'] = $user->id;
            $_SESSION['code'] = $user->code;
            output(array("code" =>  $user->code));
        }
    } else {
        $code = digits_generate();
        $mysqli->query("INSERT INTO users(email, code) VALUES('$email_escaped', $code)");
        $_SESSION['id'] = $mysqli->insert_id;
        $_SESSION['code'] = $code;
        output(array("code" => $code));
    }

    $result->close();
}

$mysqli->close();
