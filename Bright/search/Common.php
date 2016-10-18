<?php
require_once('Settings.php');

class Common {
	

	public static $entities = array (	"&amp" => "&",		"&apos" => "'",
										"&THORN;"  => "ÃƒÅ¾",	
										"&szlig;"  => "ÃƒÅ¸",	
										"&agrave;" => "ÃƒÂ ",	"&aacute;" => "ÃƒÂ¡", "&acirc;"  => "ÃƒÂ¢", "&atilde;" => "ÃƒÂ£",	"&auml;"   => "ÃƒÂ¤",	"&aring;"  => "ÃƒÂ¥",	"&aelig;"  => "ÃƒÂ¦",
										"&ccedil;" => "ÃƒÂ§",
										"&egrave;" => "ÃƒÂ¨",	"&eacute;" => "ÃƒÂ©",	"&ecirc;"  => "ÃƒÂª",	"&euml;"   => "ÃƒÂ«",
										"&igrave;" => "ÃƒÂ¬",	"&iacute;" => "ÃƒÂ­",	"&icirc;"  => "ÃƒÂ®",	"&iuml;"   => "ÃƒÂ¯",
										"&eth;"    => "ÃƒÂ°",
										"&ntilde;" => "ÃƒÂ±",
										"&ograve;" => "ÃƒÂ²",	"&oacute;" => "ÃƒÂ³",	"&ocirc;"  => "ÃƒÂ´",	"&otilde;" => "ÃƒÂµ",	"&ouml;"   => "ÃƒÂ¶", "&oslash;" => "ÃƒÂ¸",
										"&ugrave;" => "ÃƒÂ¹",	"&uacute;" => "ÃƒÂº",	"&ucirc;"  => "ÃƒÂ»",	"&uuml;"   => "ÃƒÂ¼",
										"&yacute;" => "ÃƒÂ½",	"&yuml;"   => "ÃƒÂ¿",
										"&thorn;"  => "ÃƒÂ¾",	
										"&Agrave;" => "ÃƒÂ ",	"&Aacute;" => "ÃƒÂ¡",	"&Acirc;"  => "ÃƒÂ¢", "&Atilde;" => "ÃƒÂ£",	"&Auml;"   => "ÃƒÂ¤",	"&Aring;"  => "ÃƒÂ¥",	"&Aelig;"  => "ÃƒÂ¦",
										"&Ccedil;" => "ÃƒÂ§",
										"&Egrave;" => "ÃƒÂ¨",	"&Eacute;" => "ÃƒÂ©",	"&Ecirc;"  => "ÃƒÂª",	"&Euml;"   => "ÃƒÂ«",
										"&Igrave;" => "ÃƒÂ¬",	"&Iacute;" => "ÃƒÂ­",	"&Icirc;"  => "ÃƒÂ®",	"&Iuml;"   => "ÃƒÂ¯",
										"&ETH;"    => "ÃƒÂ°",
										"&Ntilde;" => "ÃƒÂ±",
										"&Ograve;" => "ÃƒÂ²",	"&Oacute;" => "ÃƒÂ³",	"&Ocirc;"  => "ÃƒÂ´",	"&Otilde;" => "ÃƒÂµ",	"&Ouml;"   => "ÃƒÂ¶",	"&Oslash;" => "ÃƒÂ¸",
										"&Ugrave;" => "ÃƒÂ¹",	"&Uacute;" => "ÃƒÂº",	"&Ucirc;"  => "ÃƒÂ»",	"&Uuml;"   => "ÃƒÂ¼",
										"&Yacute;" => "ÃƒÂ½",	"&Yhorn;"  => "ÃƒÂ¾",	"&Yuml;"   => "ÃƒÂ¿");
	
	public static function del_secchars($file, $search){

        if (Settings::getInstance() -> cn_seg == '1' && Settings::getInstance() -> del_secchars==1) {
            //      Delete additional characters (as word separator) like dots, question marks, colons etc. (characters 1-49 in original Chinese dictionary)
            $file = preg_replace ('/ã€‚|ï¼Œ|ã€¿|ï¼›|ï¼š|ï¼Ÿ|ï¼¿|â€¦|â€”|Â·|Ë‰|Ë‡|Â¨|â€˜|â€™|â€œ|â€¿|ã€…|ï½ž|â€–|âˆ¶|ï¼‚|ï¼‡|ï½€|ï½œ|ã€ƒ|ã€”|ã€•|ã€ˆ|ã€‰|ã€Š|ã€‹|ã€Œ|ã€¿|ã€Ž|ã€¿|ï¼Ž|ã€–|ã€—|ã€¿|ã€‘|ï¼ˆ|ï¼‰|ï¼»|ï¼½|ï½›|ï½¿/', " ", $file);
            $file = preg_replace('/Ã¯Â¼â€º|Â¡Â£|Â£Â¬|Â¡Â¢|Â£Â»|Â£Âº|Â£Â¿|Â£Â¡|Â¡Â­|Â¡Âª|Â¡Â¤|Â¡Â¥|Â¡Â¦|Â¡Â§|Â¡Â®|Â¡Â¯|Â¡Â°|Â¡Â±|Â¡Â©|Â¡Â«|Â¡Â¬|Â¡Ãƒ|Â£Â¢|Â£Â§|Â£Ã |Â£Ã¼|Â¡Â¨|Â¡Â²|Â¡Â³|Â¡Â´|Â¡Âµ|Â¡Â¶|Â¡Â·|Â¡Â¸|Â¡Â¹|Â¡Âº|Â¡Â»|Â£Â®|Â¡Â¼|Â¡Â½|Â¡Â¾|Â¡Â¿|Â£Â¨|Â£Â©|Â£Ã›|Â£Ã¿|Â£Ã»|Â£Ã½|Â°Â¢/', " ", $file);
            $file = preg_replace('/ï¼¿|ï¼†|ï¼Œ|<|ï¼š|ï¼›|ãƒ»|\(|\)/', " ", $file);
        }

        if (Settings::getInstance() -> del_secchars == '1') {
            //$file = preg_replace('/, |\. |! |\? |" |: |\) |\), |\)./', " ", $file);    //    kill all special characters at the end of words (also for Cyrillic words)
            //$file = preg_replace('/, |[^0-9]\. |! |\? |" |: |\) |\), |\)./', " ", $file);    //    kill special characters at the end of words, but dots not for words containing only digits
            $file = preg_replace('/,|\. |\.\. |\.\.\. |!|\? |" |: |\) |\), |\). |ã€‘ |ï¼‰ |ï¼Ÿ,|ï¼Ÿ |ï¼ |ï¼|ã€‚,|ã€‚ |â€ž |â€œ |â€ |â€|â€&nbsp;|Â» |.Â»|;Â»|:Â»|,Â»|.Â»|Î‡Â»|Â«|Â« |Â», |Â». |.â€ |,â€|;â€ |â€. |â€, |â€¿|ã€|ï¼‰|Î‡|;|\] |\} /', " ", $file);
            $file = preg_replace('/ \[| "| \(| â€ž| â€œ|ï¼ˆ| Â«| ã€| â€¿| ï¼ˆ/', " ", $file);     //    kill special characters in front of words
            $file = preg_replace('/ãƒ»/', " ", $file);     //    kill separating characters inside of words
        }

        return $file;
    }
    
	/**
	 * Removes duplicate elements from an array
	 * @param array $arr The array to check
	 */
	public static function distinct_array($arr) {
		rsort($arr);
		reset($arr);
		$newarr = array();
		$i = 0;
		$element = current($arr);

		for ($n = 0; $n < sizeof($arr); $n++) {
			if (next($arr) != $element) {
				$newarr[$i] = $element;
				$element = current($arr);
				$i++;
			}
		}

		return $newarr;
	}
	
	public static function getmicrotime(){
		list($usec, $sec) = explode(" ",microtime());
		return ((float)$usec + (float)$sec);
	}
	
	public static function lower_ent($string) {
        $ent = array
            (
            "ÄŒ" => "Ä",
            "ÄŽ" => "Ä",
            "Äš" => "Ä›",
            "Ä½" => "Ä¾",
            "Å‡" => "Åˆ",
            "Å˜" => "Å™",
            "Å " => "Å¡",
            "Å¤" => "Å¥",
            "Å½" => "Å¾",

            "Ã„" => "Ã¤",
            "Ã–" => "Ã¶",
            "Ãœ" => "Ã¼",
            "&Auml;" => "Ã¤",
            "&#196;" => "Ã¤",
            "&Ouml;" => "Ã¶",
            "&#214;" => "Ã¶",
            "&Uuml;" => "Ã¼",
            "&#220;" => "Ã¼",

            "Ã€" => "Ã ",
            "Ãˆ" => "Ã¨",
            "ÃŒ" => "Ã¬",
            "Ã’" => "Ã²",
            "Ã™" => "Ã¹",

            "Ã‰" => "Ã©",
            "Ã" => "Ã­",
            "Ã“" => "Ã³",
            "Ãš" => "Ãº",

            "Ãƒ" => "Ã£",
            "Ã‘" => "Ã±",
            "Ã•" => "Ãµ",
            "Å¨" => "Å©",

            "Ã‚" => "Ã¢",
            "ÃŠ" => "Ãª",
            "ÃŽ" => "Ã®",
            "Ã”" => "Ã´",
            "Ã›" => "Ã»",

            "Ã…" => "Ã¥",
            "Å®" => "Å¯",

            "Ã†" => "Ã¦",
            "Ã‡" => "Ã§",
            "Ã˜" => "Ã¸",
            "Ã‹" => "Ã«",
            "Ã" => "Ã¯",

            "Äž" => "ÄŸ",
            //"Ä°" => "Ä±",
            "Ä°" => "i",
            "Åž" => "ÅŸ",

            "Ä¦" => "Ä§",
            "Ä¤" => "Ä¥",
            "Ä´" => "Äµ",
            "Å»" => "Å¼",
            "ÄŠ" => "Ä‹",
            "Äˆ" => "Ä‰",
            "Å¬" => "Å­",
            "Åœ" => "Å",
            "Ä‚" => "Äƒ",
            "Å" => "Å‘",
            "Ä¹" => "Äº",
            "Ä†" => "Ä‡",
            "Å°" => "Å±",
            "Å¢" => "Å£",
            "Åƒ" => "Å„",
            "Ä" => "Ä‘",
            "Å”" => "Å•",
            "Ã" => "Ã¡",
            "Åš" => "Å›",
            "Å¹" => "Åº",
            "Å" => "Å‚",
            "Ë˜" => "Ë›",

            "Ä¸" => "Ë›",
            "Å–" => "Å—",

            "Ä®" => "Ä¯",
            "Ä˜" => "Ä™",
            "Ä–" => "Ä—",
            "Ã" => "Ã°",
            "Å…" => "Å†",
            "ÅŒ" => "Å",
            "Å²" => "Å³",
            "Ã" => "Ã½",
            "Ãž" => "Ã¾",
            "Ä„" => "Ä…",
            "Ä’" => "Ä“",
            "Ä¢" => "Ä£",
            "Äª" => "Ä«",
            "Ä¨" => "Ä©",
            "Ä¶" => "Ä·",
            "Ä»" => "Ä¼",
            "Å¦" => "Å§",
            "Åª" => "Å«",
            "ÅŠ" => "Å‹",

            "Ä€" => "Ä",

            "á¸‚" => "á¸ƒ",
            "á¸Š" => "á¸‹",
            "áº€" => "áº",
            "áº‚" => "áºƒ",
            "á¹ " => "á¹¡",
            "á¸ž" => "á¸Ÿ",
            "á¹€" => "á¹",
            "á¹–" => "á¹—",
            "áº„" => "áº…",
            "Å´" => "Åµ",
            "á¹ª" => "á¹«",
            "Å¶" => "Å·"

             );
        reset($ent);
        while ($char = each($ent)) {
            $string = preg_replace("/".$char[0]."/i", $char[1], $string);
        }
        return ($string);
    }

    //  convert characters into lower case
	public static function lower_case($string) {
		$charSet = Settings::getInstance() -> home_charset;
		$greek = Settings::getInstance() -> greek;
		$cyrillic = Settings::getInstance() -> cyrillic;
		
        $charSet = strtoupper($charSet);
		//Settings::getInstance() -> charSet = $charSet;
        //      if required, convert Greek charset into lower case
        if ($greek == '1') {

        	$lower = array
        		(
                "Î‘" => "Î±",
                "Î’" => "Î²",
                "Î“" => "Î³",
                "Î”" => "Î´",
                "Î•" => "Îµ",
                "Î–" => "Î¶",
                "Î—" => "Î·",
                "Î˜" => "Î¸",
                "Î™" => "Î¹",
                "Îš" => "Îº",
                "Î›" => "Î»",
                "Îœ" => "Î¼",
                "Î" => "Î½",
                "Îž" => "Î¾",
                "ÎŸ" => "Î¿",
                "Î " => "Ï€",
                "Î¡" => "Ï",
                "Î£" => "Ïƒ",
                "Î¤" => "Ï„",
                "Î¥" => "Ï…",
                "Î¦" => "Ï†",
                "Î§" => "Ï‡",
                "Î¨Ïˆ" => "",
                "Î©" => "Ï‰"
                );

        	reset($lower);
        	while ($char = each($lower)) {
        		$string = preg_replace("/".$char[0]."/i", $char[1], $string);
        	}
        }

        //      if required, convert Cyrillic charset into lower case
        if ($cyrillic == '1') {

        	$lower = array
        		(
                "Ð" => "Ð°",     //      basic Cyrillian alphabet
                "Ð‘" => "Ð±",
                "Ð’" => "Ð²",
                "Ð“" => "Ð³",
                "Ò" => "Ò‘",
                "Ðƒ" => "Ñ“",
                "Ð”" => "Ð´",
                "Ð‚" => "Ñ’",
                "Ð•" => "Ðµ",
                "Ð" => "Ñ‘",
                "Ð„" => "Ñ”",
                "Ð–" => "Ð¶",
                "Ð—" => "Ð·",
                "Ð…" => "Ñ•",
                "Ð˜" => "Ð¸",
                "Ð†" => "Ñ–",
                "Ð‡" => "Ñ—",
                "Ð™" => "Ð¹",
                "Ðˆ" => "Ñ˜",
                "Ðš" => "Ðº",
                "ÐŒ" => "Ñœ",
                "Ð›" => "Ð»",
                "Ð‰" => "Ñ™",
                "Ðœ" => "Ð¼",
                "Ð" => "Ð½",

                "ÐŠ" => "Ñš",
                "Ðž" => "Ð¾",
                "ÐŸ" => "Ð¿",
                "Ð " => "Ñ€",
                "Ð¡" => "Ñ",
                "Ð¢" => "Ñ‚",
                "Ð‹" => "Ñ›",
                "Ð£" => "Ñƒ",
                "ÐŽ" => "Ñž",
                "Ð¤" => "Ñ„",
                "Ð¥" => "Ñ…",
		"Ñ " => "Ñ¡",          //     ex Greek 'OMEGA'
                "Ð¦" => "Ñ†",
                "Ð§" => "Ñ‡",
                "Ð" => "ÑŸ",
                "Ð¨" => "Ñˆ",
                "Ð©" => "Ñ‰",
                "Ðª" => "ÑŠ",
                "Ð«" => "Ñ‹",
                "Ð¬" => "ÑŒ",
                "Ð«" => "Ñ‹",
                "Ð­" => "Ñ",
                "Ð®" => "ÑŽ",
                "Ð¯" => "Ñ",

		"Ð€" => "Ñ",
		"Ð‚" => "Ñ’",
		"Ð‡" => "Ñ—",
		"Ð" => "Ñ",

		"Ñ¤" => "Ñ¥",         //      extended Cyrillic
		"Ñ¦" => "Ñ§",
                "Ñª" => "Ñ«",
                "Ñ¨" => "Ñ©",
                "Ñ¬" => "Ñ­",
                "Ñ®" => "Ñ¯",
                "Ñ°" => "Ñ±",
                "Ñ²" => "Ñ³",
                "Ñ´" => "Ñµ",

                "Ä" => "Ä‘",
                "Ç´" => "Çµ",
                "ÃŠ" => "Ãª",
                "áº" => "áº‘",
                "ÃŒ" => "Ã¬",
                "Ã" => "Ã¯",
                "JË‡" => "Ç°",
                "LÌ‚" => "lÌ‚",
                "NÌ‚" => "nÌ‚",
                "Ä†" => "Ä‡",
                "á¸°" => "á¸±",
                "Å¬" => "Å­",
                "DÌ‚" => "dÌ‚",
                "Åœ" => "Å",
                "Ã›" => "Ã»",
                "Ã‚" => "Ã¢",
                "GÌ€" => "g",

                "Äš" => "Ä›",
                "GÌ€" => "g",
                "Ä " => "Ä¡",
                "Äž" => "ÄŸ",
                "Å½Ì¦" => "Å¾",
                "Ä¶" => "Ä·",
                "KÌ„" => "kÌ„",
                "á¹†" => "á¹‡",
                "á¹„" => "á¹…",
                "á¹”" => "á¹•",
                "Ã’" => "Ã²",
                "Ã‡" => "Ã§",
                "Å¢" => "Å£",
                "Ã™" => "Ã¹",
                "U" => "u",
                "á¸¨" => "á¸©",
                "CÌ„" => "cÌ„",
                "á¸¤" => "á¸¥",
                "CÌ†" => "cÌ†",
                "Ã‡Ì†" => "Ã§Ì†",
                "ZÌ†" => "zÌ†",
                "Ä‚" => "Äƒ",
                "Ã„" => "Ã¤",
                "Ä”" => "Ä•",
                "ZÌ„" => "zÌ„",
                "ZÌˆ" => "zÌˆ",
                "Å¹" => "Åº",
                "ÃŽ" => "Ã®",
                "Ã–" => "Ã¶",
                "Ã”" => "Ã´",
                "Ãœ" => "Ã¼",
                "Å°" => "Å±",
                "CÌˆ" => "cÌˆ",
                "Å¸" => "Ã¿",

		"ÒŠ" => "Ò‹",
		"ÒŒ" => "Ò",
		"ÒŽ" => "Ò",
		"Ò" => "Ò‘",
		"Ò’" => "Ò“",
		"Ò”" => "Ò•",
		"Ò–" => "Ò—",
		"Ò˜" => "Ò™",
		"Òš" => "Ò›",
		"Òœ" => "Ò",
		"Òž" => "ÒŸ",
		"Ò " => "Ò¡",
		"Ò¢" => "Ò£",
		"Ò¤" => "Ò¥",
		"Ò¦" => "Ò§",
		"Ò¨" => "Ò©",
		"Òª" => "Ò«",
		"Ò¬" => "Ò­",
		"Ò®" => "Ò¯",
		"Ò°" => "Ò±",
		"Ò²" => "Ò³",
		"Ò´" => "Òµ",
		"Ò¶" => "Ò·",
		"Ò¸" => "Ò¹",
		"Òº" => "Ò»",
		"Ò¼" => "Ò½",
		"Ò¾" => "Ò¿",
		"Ó" => "Ó‚",
		"Óƒ" => "Ó„",
		"Ó…" => "Ó†",
		"Ó‡" => "Óˆ",
		"Ó‰" => "ÓŠ",
		"Ó‹" => "ÓŒ",
		"Ó" => "ÓŽ",
		"Ó" => "Ó‘",
		"Ó’" => "Ó“",
		"Ó”" => "Ó•",
		"Ó–" => "Ó—",
		"Ó˜" => "Ó™",
		"Óš" => "Ó›",
		"Óœ" => "Ó",
		"Óž" => "ÓŸ",
		"Ó " => "Ó¡",
		"Ó¢" => "Ó£",
		"Ó¤" => "Ó¥",
		"Ó¦" => "Ó§",
		"Ó¨" => "Ó©",
		"Óª" => "Ó«",
		"Ó¬" => "Ó­",
		"Ó®" => "Ó¯",
		"Ó°" => "Ó±",
		"Ó²" => "Ó³",
		"Ó´" => "Óµ",
		"Ó¶" => "Ó·",
		"Ó¸" => "Ó¹",
		"Ó¼" => "Ó½",
		"Ó¾" => "Ó¿",

		"Ñ " => "Ñ¡",         //      historical Cyrillic
		"Ñ¢" => "Ñ£",
		"Ñ¤" => "Ñ¥",
		"Ñ¦" => "Ñ§",
		"Ñ¨" => "Ñ©",
		"Ñª" => "Ñ«",
		"Ñ¬" => "Ñ­",
		"Ñ®" => "Ñ¯",
		"Ñ°" => "Ñ±",
		"Ñ²" => "Ñ³",
		"Ñ´" => "Ñµ",
		"Ñ¶" => "Ñ·",
		"Ñ¸" => "Ñ¹",
		"Ñº" => "Ñ»",
		"Ñ¼" => "Ñ½",
		"Ñ¾" => "Ñ¿",
		"Ò€" => "Ò",
		"Ç" => "ÇŽ",
		"FÌ€" => "fÌ€",
		"á»²" => "á»³",

                "Ã?" => "ÃÂ°",
                "Ãâ€˜" => "ÃÂ±",
                "Ãâ€™" => "ÃÂ²",
                "Ãâ€œ" => "ÃÂ³",
                "Ãâ€" => "ÃÂ´",
                "Ãâ€¢" => "ÃÂµ",
                "Ãâ€“" => "ÃÂ¶",
                "Ãâ€”" => "ÃÂ·",
                "ÃËœ" => "ÃÂ¸",
                "Ãâ„¢" => "ÃÂ¹",
                "ÃÅ¡" => "ÃÂº",
                "Ãâ€º" => "ÃÂ»",
                "ÃÅ“" => "ÃÂ½",
                "ÃÅ¾" => "ÃÂ¾",
                "ÃÅ¸" => "ÃÂ¿",
                "ÃÂ " => "Ã‘â‚¬",
                "ÃÂ¡" => "Ã‘?",
                "ÃÂ¢" => "Ã‘â€š",
                "ÃÂ£" => "Ã‘Æ’",
                "ÃÂ¤" => "Ã‘â€ž",
                "ÃÂ¥" => "Ã‘â€¦",
                "ÃÂ¦" => "Ã‘â€ ",
                "ÃÂ§" => "Ã‘â€¡",
                "ÃÂ¨" => "Ã‘Ë†",
                "ÃÂ©" => "Ã‘â€°",
                "ÃÂª" => "Ã‘Å ",
                "ÃÂ«" => "Ã‘â€¹",
                "ÃÂ¬" => "Ã‘Å’",
                "ÃÂ­" => "Ã‘?",
                "ÃÂ®" => "Ã‘Å½",
                "ÃÂ¯" => "Ã‘?",

                "Ã?" => "Ã‘â€˜",
                "Ãâ€š" => "Ã‘â€™",
                "ÃÆ’" => "Ã‘â€œ",
                "Ãâ€ž" => "Ã‘â€",
                "Ãâ€¦" => "Ã‘â€¢",
                "Ãâ€ " => "Ã‘â€“",
                "Ãâ€¡" => "Ã‘â€”",
                "ÃË†" => "Ã‘Ëœ",
                "Ãâ€°" => "Ã‘â„¢",
                "ÃÅ " => "Ã‘Å¡",
                "Ãâ€¹" => "Ã‘â€º",
                "ÃÅ’" => "Ã‘Å“",
                "ÃÅ½" => "Ã‘Å¾",
                "Ã?" => "Ã‘Å¸"
        		);

        	reset($lower);
        	while ($char = each($lower)) {
        		$string = preg_replace("/".$char[0]."/i", $char[1], $string);
        	}
        }

        return (strtr($string,  "ABCDEFGHIJKLMNOPQRSTUVWXYZ",
                                "abcdefghijklmnopqrstuvwxyz"));

	}
	
	public static function saveToLog ($query, $elapsed, $results, $ip, $media) {
        $mysql_table_prefix = (Settings::getInstance() -> mysql_table_prefix != '') ? Settings::getInstance() -> mysql_table_prefix : Configuration::getInstance() -> getTablePrefix();
        $debug = Settings::getInstance() -> debug;
        if ($results =="") {
            $results = 0;
        }
        $query =  "insert into {$mysql_table_prefix}query_log (query, time, elapsed, results, ip, media) values ('$query', now(), '$elapsed', '$results', '$ip', '$media')";
    	Connection::getInstance() -> insertRow($query);
    }
	
	public static function stem_word($word, $type) {
        $debug = Settings::getInstance() -> debug; 
        $stem_words = Settings::getInstance() -> stem_words;
        $stem_dir = Settings::getInstance() -> stem_dir;
        $min_word_length = Settings::getInstance() -> min_word_length;
        $common = Settings::getInstance() -> common;

        //if ($debug == '2') echo "\r\n\r\n<br /> unstemmed: $word<br />\r\n";
        //  no stemming for too short words or words containing some special characters
        if (strlen($word) < $min_word_length || preg_match("/[\*\!:]|[0-9]/si", $word)) {
  			return $word;
		}

        if ($stem_words == 'bg') {
            require_once "$stem_dir/bg_stem.php";
            $word1 = bg_stemmer::stem($word);
        }

        if ($stem_words == 'cz') {
            require_once "$stem_dir/cz_stem.php";
            $word1 = cz_stemmer::stem($word);
        }

        if ($stem_words == 'de') {
            require_once "$stem_dir/de_stem.php";
            $word1 = de_stemmer::stem($word);
        }

        if ($stem_words == 'el') {
            require_once "$stem_dir/el_stem.php";
            $stemmer = new el_stemmer();
            $word1 = $stemmer->stem($word);
        }

        if ($stem_words == 'en') {
            require_once "$stem_dir/en_stem.php";
            $word1 = en_stemmer::stem($word);
        }

        if ($stem_words == 'es') {
            require_once "$stem_dir/es_stem.php";
            $word1 = es_stemmer::stem($word);
        }

        if ($stem_words == 'fi') {
            require_once "$stem_dir/fi_stem.php";
            $word1 = fi_stemmer::stem($word);
        }

        if ($stem_words == 'fr') {
            require_once "$stem_dir/fr_stem.php";
            $word1 = fr_stemmer::stem($word);
        }

        if ($stem_words == 'hu') {
            require_once "$stem_dir/hu_stem.php";
            $word1 = hu_stemmer::stem($word);
        }

        if ($stem_words == 'nl') {
            require_once "$stem_dir/nl_stem.php";
            $word1 = nl_stemmer::stem($word);
        }

        if ($stem_words == 'it') {
            require_once "$stem_dir/it_stem.php";
            $stemmer = new it_stemmer();
            $word1 = $stemmer->stem($word);
        }

        if ($stem_words == 'pt') {
            require_once "$stem_dir/pt_stem.php";
            $word1 = pt_stemmer::stem($word);
        }

        if ($stem_words == 'ru') {
            require_once "$stem_dir/ru_stem.php";
            $word1 = ru_stemmer::stem($word);
        }

        if ($stem_words == 'se') {
            require_once "$stem_dir/se_stem.php";
            $word1 = se_stemmer::stem($word);
        }

        //  Hopefully the stemmed word did not become too short
        //  and the stemming algorithm did not create a common word
        if (strlen($word1) > $min_word_length && $common[$word1] != 1) {
            $word = $word1;
        }

        //if ($debug == '2') echo "\r\n\r\n<br /> &nbsp;&nbsp;&nbsp;stemmed: $word<br />\r\n";
        return $word;

    }
}

class Segmentation {
	var $options = array('lowercase' => TRUE);
	var $dict_name = 'Unknown';
	var $dict_words = array();

	function setLowercase($value) {
		if ($value) {
			$this->options['lowercase'] = TRUE;
		} else {
			$this->options['lowercase'] = FALSE;
		}
		return TRUE;
	}

	function load($dict_file) {
		if (!file_exists($dict_file)) {
			return FALSE;
		}
		$fp = fopen($dict_file, 'r');
		$temp = fgets($fp, 1024);
		if ($temp === FALSE) {
			return FALSE;
		} else {
			if (strpos($temp, "\t") !== FALSE) {
				list ($dict_type, $dict_name) = explode("\t", trim($temp));
			} else {
				$dict_type = trim($temp);
				$dict_name = 'Unknown';
			}
			$this->dict_name = $dict_name;
			if ($dict_type !== 'DICT_WORD_W') {
				return FALSE;
			}
		}
		while (!feof($fp)) {
			$this->dict_words[rtrim(fgets($fp, 32))] = 1;
		}
		fclose($fp);
		return TRUE;
	}

	function getDictName() {
		return $this->dict_name;
	}

	function segmentString($str) {
		if (count($this->dict_words) === 0) {
			return FALSE;
		}
		$lines = explode("\n", $str);
		return $this->_segmentLines($lines);
	}

	function segmentFile($filename) {
		if (count($this->dict_words) === 0) {
			return FALSE;
		}
		$lines = file($filename);
		return $this->_segmentLines($lines);
	}

	function _segmentLines($lines) {
		$contents_segmented = '';
		foreach ($lines as $line) {
			$contents_segmented .= $this->_segmentLine(rtrim($line)) . " \n";
		}
		do {
			$contents_segmented = str_replace('  ', ' ', $contents_segmented);
		} while (strpos($contents_segmented, '  ') !== FALSE);
		return $contents_segmented;
	}

	function _segmentLine($str) {
		$str_final = '';
		$str_array = array();
		$str_length = strlen($str);
		if ($str_length > 0) {
			if (ord($str{$str_length-1}) >= 129) {
				$str .= ' ';
			}
		}
		for ($i=0; $i<$str_length; $i++) {
			if (ord($str{$i}) >= 129) {
				$str_array[] = $str{$i} . $str{$i+1};
				$i++;
			} else {
				$str_tmp = $str{$i};
				for ($j=$i+1; $j<$str_length; $j++) {
					if (ord($str{$j}) < 129) {
						$str_tmp .= $str{$j};
					} else {
						break;
					}
				}
				$str_array[] = array($str_tmp);
				$i = $j - 1;
			}
		}
		$pos = count($str_array);
		while ($pos > 0) {
			$char = $str_array[$pos-1];
			if (is_array($char)) {
				$str_final_tmp = $char[0];

				if ($this->options['lowercase']) {
					$str_final_tmp = strtolower($str_final_tmp);
				}
				$str_final = " $str_final_tmp$str_final";
				$pos--;
			} else {
				$word_found = 0;
				$word_array = array(0 => '');
				if ($pos < 4) {
					$word_temp = $pos + 1;
				} else {
					$word_temp = 5;
				}
				for ($i=1; $i<$word_temp; $i++) {
					$word_array[$i] = $str_array[$pos-$i] . $word_array[$i-1];
				}
				for ($i=($word_temp-1); $i>1; $i--) {
					if (array_key_exists($word_array[$i], $this->dict_words)) {
					   $word_found = $i;
					   break;
					}
				}
				if ($word_found) {
					$str_final = " $word_array[$word_found]$str_final";
					$pos = $pos - $word_found;
				} else {
					$str_final = " $char$str_final";
					$pos--;
				}
			}
		}
		return $str_final;
	}
}