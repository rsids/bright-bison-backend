<?php
include_once(__DIR__  . '/../Bright.php');

if(!isset($_GET['mode']) || !isset($_GET['src'])) {
    // redirect to default image
    header('HTTP/1.1 500 Internal Server Error');
    header('Location: ' . ERROR_IMG);
    exit;
} else {
    $mode = addslashes($_GET['mode']);
    $src = $_GET['src'];
    // Unset all other get parameters
    $debug = -1;
    if(!LIVESERVER && isset($_GET['phpThumbDebug'])) {
        $debug = (int)$_GET['phpThumbDebug'];
    }

    $original = filter_input_array(INPUT_GET);

    $_GET = array();
    $_GET['src'] = $src;

    $modes = unserialize(IMAGE_MODES);
    $modes['brightthumb'] = array('w' => 98, 'h' => 74);
    $modes['brightlogo'] = array('w' => 117, 'h' => 117, 'far' => 'C','f'=> 'png', 'bg'=>'0000FF');
    if(array_key_exists($mode, $modes)) {

        $ext = strtolower(substr($original['src'], -3));
        if(!array_key_exists('f', $modes[$mode])) {
            switch($ext){
                case 'png':
                case 'gif':
                    $_GET['f'] = $ext;
                    break;
                case 'peg':
                case 'jpg':
                    $_GET['f'] = 'jpeg';
                    break;
                default:
                    // Invalid image
                    header('HTTP/1.1 500 Internal Server Error');
                    header('Location: ' . ERROR_IMG);
                    exit;
            }
        }
        if($mode === 'brightthumb') {
            $img = @exif_thumbnail(BASEPATH . $_GET['src'], $modes['brightthumb']['w'] , $modes['brightthumb']['h'], $type);
            if($img !== false) {
                header('Content-type: ' .image_type_to_mime_type($type));
                echo $img;
                exit;
            }
        }

        foreach($modes[$mode] as $key => $value) {
            $_GET[$key] = $value;
        }
        if($debug > -1)
            $_GET['phpThumbDebug'] = $debug;

        if(array_key_exists('allowedvars', $modes[$mode])) {
            foreach($modes[$mode]['allowedvars'] as $var) {
                if(array_key_exists($var, $original)) {
                    $_GET[$var] = $original[$var];
                }
            }
            unset($_GET['allowedvars']);
        }
        ob_start();
        include(BASEPATH . 'bright/library/phpThumb/phpThumb.php');

        $img = ob_get_clean();

        if(!$img) {
            // Output original image
            header('Content-type: image/' .$_GET['f']);
            echo file_get_contents(BASEPATH . $_GET['src']);
        }
    } else {
        header('HTTP/1.1 500 Internal Server Error');
        header('Location: ' . ERROR_IMG);
        exit;
    }

}
