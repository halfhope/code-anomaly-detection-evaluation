<?php

function output($data, $code = 0, $description = '') {
    header("Content-type: text/json");
    echo $code != 0
        ? json_encode(array("error" => $description ?? "Unknown error", "status_code" => $code))
        : json_encode(array("data" => $data, "status_code" => $code));
    exit();
}

function digits_generate($digits = 4) {
    return rand(pow(10, $digits-1), pow(10, $digits)-1);
}
