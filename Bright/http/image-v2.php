<?php
include_once(__DIR__  . '/../Bright.php');
include_once(__DIR__  . '/../images/Image.php');

$path = filter_input(INPUT_GET, 'src', FILTER_SANITIZE_STRING);
$mode = filter_input(INPUT_GET, 'mode', FILTER_SANITIZE_STRING);
if(!$path) {
    imgError();
}

$paths = explode('/' , $path);
$file = array_pop($paths);
$path = implode('/', $paths);
$modes = unserialize(IMAGE_MODES);
$modes['brightthumb'] = array('w' => 98, 'h' => 74);
$modes['brightlogo'] = array('w' => 117, 'h' => 117, 'far' => 'C','f'=> 'png', 'bg'=>'0000FF');

if (!file_exists(BASEPATH . $path . DIRECTORY_SEPARATOR . $file)) {
    error_log(BASEPATH . $path . $file . ' does not exist');
    imgError();
}

if(!@is_array(getimagesize(BASEPATH . $path . DIRECTORY_SEPARATOR . $file))) {
    error_log(BASEPATH . $path . $file . ' is not an image');
    imgError();
}

if(!array_key_exists($mode, $modes)) {
    error_log(sprintf('image mode "%s" does not exist', $mode));
    imgError();
}

$imgController = new Image();

$result = $imgController->createImage($file, $path, $mode, $modes[$mode]);
img($result);

function imgError() {
    img(__DIR__ . '/notfound.png');
}

function img($path) {
    header('Content-type: image/png');
    echo file_get_contents($path);
    exit;
}