<?php
/*
This file contains database config.phpuration assuming you are running mysql using user "root" and password ""
*/

define('FIREBASE_URL', 'https://berper-default-rtdb.firebaseio.com/');

// Firebase HTTP REST Request Helper
function firebase_request($path, $method = 'GET', $data = null) {
    $url = FIREBASE_URL . $path . '.json';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    if ($data !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    }
    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    if ($err) {
        return null;
    }
    return json_decode($response, true);
}

// Mock Connection and Result classes to prevent errors in legacy scripts
class FirebaseMockConnection {
    public $connect_error = null;
    public function query($sql) {
        return new FirebaseMockResult();
    }
    public function close() {
        return true;
    }
}
class FirebaseMockResult {
    public $num_rows = 0;
    public function fetch_assoc() { return null; }
}
$conn = new FirebaseMockConnection();

// Define procedural mysqli polyfills
if (!function_exists('mysqli_connect')) {
    function mysqli_connect($host = null, $user = null, $password = null, $database = null, $port = null, $socket = null) {
        return new FirebaseMockConnection();
    }
    function mysqli_query($link, $query, $resultmode = 0) {
        return new FirebaseMockResult();
    }
    function mysqli_fetch_assoc($result) {
        return null;
    }
    function mysqli_fetch_array($result, $mode = 0) {
        return array();
    }
    function mysqli_num_rows($result) {
        return 0;
    }
    function mysqli_error($link) {
        return "";
    }
    function mysqli_fetch_row($result) {
        return array();
    }
    function mysqli_insert_id($link) {
        return 0;
    }
}


?>