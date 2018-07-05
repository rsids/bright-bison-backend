<?php
session_start();
include_once('../Bright.php');
//include_once('Bright/user/User.php');

if (!isset($_SERVER['HTTP_REFERER']) || strpos($_SERVER['HTTP_REFERER'], BASEURL) !== 0) {
    $key = filter_input(INPUT_GET, 'apikey', FILTER_SANITIZE_STRING);
    if ($key !== APIKEY) {
        die('Cannot be called from outside the domain');
    } else {
        // Login as admin
        $administrator = new Administrator();
        $administrator->authApikey();
    }
}


if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'downloadCSV':
            $user = new User();
            $csv = $user->downloadCSV();
            header("Content-type: application/octet-stream");
            header('Content-Disposition: filename="users.csv"');
            echo $csv;
            exit;
        case 'restoreBackup':
            include('backup.php');
            exit;
    }
} else {
    $c = $m = $a = null;
    if (isset($_GET['c']) && isset($_GET['m'])) {
        $c = filter_input(INPUT_GET, 'c', FILTER_SANITIZE_STRING);
        $m = filter_input(INPUT_GET, 'm', FILTER_SANITIZE_STRING);
        $a = isset($_GET['a']) ? $_GET['a'] : null;
    } else {
        $params = (array)json_decode(file_get_contents('php://input'));
        if (isset($params) && isset($params['c']) && isset($params['c'])) {
            $c = filter_var($params['c'], FILTER_SANITIZE_STRING);
            $m = filter_var($params['m'], FILTER_SANITIZE_STRING);
            $a = isset($params['a']) ? $params['a'] : null;
        }

    }
    if ($c !== null && $m !== null && $c !== false && $m != false && class_exists($c)) {
        $cls = new $c();

        if (method_exists($cls, $m)) {
            if ($a !== null) {
                $result = call_user_func_array(array($cls, $m), $a);

            } else {
                $result = call_user_func(array($cls, $m));
            }
            if (isset($_GET['callback'])) {
                echo $_GET['callback'] . '(' . json_encode($result) . ')';
            } else {
                echo json_encode($result);

            }
            exit;
        }
    }
}