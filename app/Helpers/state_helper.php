<?php

use CodeIgniter\Database\Config;

function getStates()
{
    $db = Config::connect();
    $query = $db->query("SELECT state_name, state_code, gst_code FROM state");
    return $query->getResult();
}

function getStateByCode($code)
{
    $db = Config::connect();
    $query = $db->query("SELECT state_name FROM state WHERE state_code = ?", [$code]);
    $result = $query->getRow();

    if ($result) {
        return $result->state_name;
    } else {
        return null;
    }
}
