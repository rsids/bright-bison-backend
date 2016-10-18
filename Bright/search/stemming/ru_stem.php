<?php

 /* o------------------------------------------------------------------------------o
    *
    *  Written on a cold winter evening close to the end of 2005 by Dennis Kreminsky
    *
   *    PHP5 implementation of Martin Porter's stemming algorithm for Russian language.
    *   Additional stemming supplied and adapted for Sphider-plus
    *   by Rolf Kellner [Tec] Feb. 2010
     *
    * o------------------------------------------------------------------------------o */

    define ('CHAR_LENGTH', '2'); // all Russian characters take 2 bytes in UTF-8

    class ru_Stemmer {

        public function Stem($word){
            $word=self::re($word);
            $a=self::rv($word);
            $start=$a[0];
            $rv=$a[1];
            $rv=self::step1($rv);
            $rv=self::step2($rv);
            $rv=self::step3($rv);
            $rv=self::step4($rv);
            return $start.$rv;
        }

        private function re($word) {
            /**
                               * Remove: ...ÑÐºÐ¸Ð¹ and ...Ñƒ
                               */
            $re = preg_replace("/(ÑÐºÐ¸Ð¹|Ñƒ)$/", '', $word);
            return $re;
        }

        private function rv($word){
            $vowels=array('Ð°','Ðµ','Ð¸','Ð¾','Ñƒ','Ñ‹','Ñ','ÑŽ','Ñ');

            $flag=0;
            $rv='';
            $start='';
            for ($i=0; $i<strlen($word); $i+=CHAR_LENGTH){
                if ($flag==1)
                   $rv.=substr($word, $i, CHAR_LENGTH);
                else
                   $start.=substr($word, $i, CHAR_LENGTH);
                if (array_search(substr($word,$i,CHAR_LENGTH), $vowels)!==FALSE)
                   $flag=1;
            }
            return array($start,$rv);
        }

        private function step1($word){
            $perfective1=array('Ð²', 'Ð²ÑˆÐ¸', 'Ð²ÑˆÐ¸ÑÑŒ');
            foreach ($perfective1 as $suffix)
                if (substr($word,-(strlen($suffix)))==$suffix && (substr($word,-strlen($suffix)-CHAR_LENGTH,CHAR_LENGTH)=='Ð°' || substr($word,-strlen($suffix)-CHAR_LENGTH,CHAR_LENGTH)=='Ñ'))
                    return substr($word, 0, strlen($word)-strlen($suffix));

            $perfective2=array('Ð¸Ð²','Ð¸Ð²ÑˆÐ¸','Ð¸Ð²ÑˆÐ¸ÑÑŒ','Ñ‹Ð²ÑˆÐ¸','Ñ‹Ð²ÑˆÐ¸ÑÑŒ');

            foreach ($perfective2 as $suffix)
                if (substr($word,-(strlen($suffix)))==$suffix)
                    return substr($word, 0, strlen($word)-strlen($suffix));

            $reflexive=array('ÑÑ', 'ÑÑŒ');
            foreach ($reflexive as $suffix)
                if (substr($word,-(strlen($suffix)))==$suffix)
                    $word=substr($word, 0, strlen($word)-strlen($suffix));

            $adjective=array('ÐµÐµ','Ð¸Ðµ','Ñ‹Ðµ','Ð¾Ðµ','Ð¸Ð¼Ð¸','Ñ‹Ð¼Ð¸','ÐµÐ¹','Ð¸Ð¹','Ñ‹Ð¹','Ð¾Ð¹','ÐµÐ¹','Ð¸Ð¹','Ñ‹Ð¹','Ð¾Ð¹','Ð¾Ð¼','ÐµÐ³Ð¾','Ð¾Ð³Ð¾','ÐµÐ¼Ñƒ','Ð¾Ð¼Ñƒ','Ð¸Ñ…','Ñ‹Ñ…','ÐµÐ¼Ñƒ','Ð¾Ð¼Ñƒ','Ð¸Ñ…','Ñ‹Ñ…','ÑƒÑŽ','ÑŽÑŽ','Ð°Ñ','ÑÑ','Ð¾ÑŽ','ÐµÑŽ');
            $participle2=array('ÐµÐ¼','Ð½Ð½','Ð²Ñˆ','ÑŽÑ‰','Ñ‰');
            $participle1=array('Ð¸Ð²Ñˆ','Ñ‹Ð²Ñˆ','ÑƒÑŽÑ‰');
            foreach ($adjective as $suffix)
                if (substr($word,-(strlen($suffix)))==$suffix){
                    $word=substr($word, 0, strlen($word)-strlen($suffix));
                    foreach ($participle1 as $suffix)
                        if (substr($word,-(strlen($suffix)))==$suffix && (substr($word,-strlen($suffix)-CHAR_LENGTH,CHAR_LENGTH)=='Ð°' || substr($word,-strlen($suffix)-CHAR_LENGTH,CHAR_LENGTH)=='Ñ'))
                            $word=substr($word, 0, strlen($word)-strlen($suffix));
                        foreach ($participle2 as $suffix)
                            if (substr($word,-(strlen($suffix)))==$suffix)
                                $word=substr($word, 0, strlen($word)-strlen($suffix));
                    return $word;
                }

            $verb1=array('Ð»Ð°','Ð½Ð°','ÐµÑ‚Ðµ','Ð¹Ñ‚Ðµ','Ð»Ð¸','Ð¹','Ð»','ÐµÐ¼','Ð½','Ð»Ð¾','Ð½Ð¾','ÐµÑ‚','ÑŽÑ‚','Ð½Ñ‹','Ñ‚ÑŒ','ÐµÑˆÑŒ','Ð½Ð½Ð¾');
            foreach ($verb1 as $suffix)
                if (substr($word,-(strlen($suffix)))==$suffix && (substr($word,-strlen($suffix)-CHAR_LENGTH,CHAR_LENGTH)=='Ð°' || substr($word,-strlen($suffix)-CHAR_LENGTH,CHAR_LENGTH)=='Ñ'))
                    return substr($word, 0, strlen($word)-strlen($suffix));

            $verb2=array('Ð¸Ð»Ð°','Ñ‹Ð»Ð°','ÐµÐ½Ð°','ÐµÐ¹Ñ‚Ðµ','ÑƒÐ¹Ñ‚Ðµ','Ð¸Ñ‚Ðµ','Ð¸Ð»Ð¸','Ñ‹Ð»Ð¸','ÐµÐ¹','ÑƒÐ¹','Ð¸Ð»','Ñ‹Ð»','Ð¸Ð¼','Ñ‹Ð¼','ÐµÐ½','Ð¸Ð»Ð¾','Ñ‹Ð»Ð¾','ÐµÐ½Ð¾','ÑÑ‚','ÑƒÐµÑ‚','ÑƒÑŽÑ‚','Ð¸Ñ‚','Ñ‹Ñ‚','ÐµÐ½Ñ‹','Ð¸Ñ‚ÑŒ','Ñ‹Ñ‚ÑŒ','Ð¸ÑˆÑŒ','ÑƒÑŽ','ÑŽ');
            foreach ($verb2 as $suffix)
                if (substr($word,-(strlen($suffix)))==$suffix)
                    return substr($word, 0, strlen($word)-strlen($suffix));

            $noun=array('Ð°','ÐµÐ²','Ð¾Ð²','Ð¸Ðµ','ÑŒÐµ','Ðµ','Ð¸ÑÐ¼Ð¸','ÑÐ¼Ð¸','Ð°Ð¼Ð¸','ÐµÐ¸','Ð¸Ð¸','Ð¸','Ð¸ÐµÐ¹','ÐµÐ¹','Ð¾Ð¹','Ð¸Ð¹','Ð¹','Ð¸ÑÐ¼','ÑÐ¼','Ð¸ÐµÐ¼','ÐµÐ¼','Ð°Ð¼','Ð¾Ð¼','Ð¾','Ñƒ','Ð°Ñ…','Ð¸ÑÑ…','ÑÑ…','Ñ‹','ÑŒ','Ð¸ÑŽ','ÑŒÑŽ','ÑŽ','Ð¸Ñ','ÑŒÑ','Ñ');
            foreach ($noun as $suffix)
                if (substr($word,-(strlen($suffix)))==$suffix)
                    return substr($word, 0, strlen($word)-strlen($suffix));
                return $word;
        }

        private function step2($word){
            if (substr($word,-CHAR_LENGTH,CHAR_LENGTH)=='ÃÂ¸')
                $word=substr($word, 0, strlen($word)-CHAR_LENGTH);
            return $word;
        }

        private function step3($word){
            $vowels=array('Ð°','Ðµ','Ð¸','Ð¾','Ñƒ','Ñ‹','Ñ','ÑŽ','Ñ');
            $flag=0;
            $r1='';
            $r2='';
            for ($i=0; $i<strlen($word); $i+=CHAR_LENGTH){
                if ($flag==2)
                   $r1.=substr($word, $i, CHAR_LENGTH);
                if (array_search(substr($word,$i,CHAR_LENGTH), $vowels)!==FALSE)
                   $flag=1;
                if ($flag=1 && array_search(substr($word,$i,CHAR_LENGTH), $vowels)===FALSE)
                   $flag=2;
            }
            $flag=0;
            for ($i=0; $i<strlen($r1); $i+=CHAR_LENGTH){
                if ($flag==2)
                   $r2.=substr($r1, $i, CHAR_LENGTH);
                if (array_search(substr($r1,$i,CHAR_LENGTH), $vowels)!==FALSE)
                   $flag=1;
                if ($flag=1 && array_search(substr($r1,$i,CHAR_LENGTH), $vowels)===FALSE)
                   $flag=2;
            }
            $derivational=array('Ð¾ÑÑ‚','Ð¾ÑÑ‚ÑŒ');
            foreach ($derivational as $suffix)
                if (substr($r2,-(strlen($suffix)))==$suffix)
                    $word=substr($word, 0, strlen($r2)-strlen($suffix));
                return $word;
        }

        private function step4($word){
            if (substr($word,-CHAR_LENGTH*2)=='Ð½Ð½')
                $word=substr($word, 0, strlen($word)-CHAR_LENGTH);
            else{
                $superlative=array('ÐµÐ¹Ñˆ', 'ÐµÐ¹ÑˆÐµ');
                foreach ($superlative as $suffix)
                    if (substr($word,-(strlen($suffix)))==$suffix)
                      $word=substr($word, 0, strlen($word)-strlen($suffix));
                if (substr($word,-CHAR_LENGTH*2)=='Ð½Ð½')
                    $word=substr($word, 0, strlen($word)-CHAR_LENGTH);
            }

            // should there be a guard flag? can't think of a russian word that ends with ...ÐµÐ¹ÑˆÑŒ or ..Ð½Ð½ÑŒ , though the algorithm states this is an "otherwise" case
            if (substr($word,-CHAR_LENGTH,CHAR_LENGTH)=='ÑŒ')
                $word=substr($word, 0, strlen($word)-CHAR_LENGTH);
            return $word;
        }
    }

?>
