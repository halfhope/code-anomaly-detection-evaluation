<?php

define("DB_HOST", "localhost");
define("DB_USERNAME", "cad");
define("DB_PASSWORD", "cDaa9bKtujNhoCZadRHPgXFB");
define("DB_NAME", "cad");

function mysql_connect() {
    $mysqli = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

    if (mysqli_connect_errno()) {
        return null;
    }

    return $mysqli;
}