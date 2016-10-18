<?php

 /* o------------------------------------------------------------------------------o
    *
    *  This script was originally written in PEARL by  Ljiljana Dolamic and Jacques Savoy
    *
    *  Improvements, PHP5 implementation and adapted for Sphider-plus application
    *   by Rolf Kellner [Tec] Feb. 2010
    *
    * o------------------------------------------------------------------------------o */

    define('cz_case1', '/(atech|Ä›tem|etem|atÅ¯m|ech|ich|Ã­ch|Ã©ho|Ä›mi|emi|Ã©mu|Ä›te|ete|Ä›ti|eti)$/');
    define('cz_case2', '/(Ã­ho|iho|Ã­mi|Ã­mu|imu|Ã¡ch|ata|aty|Ã½ch|ama|ami|ovÃ©|ovi|Ã½mi|em|es|Ã©m)$/');
    define('cz_case3', '/(Ã­m|Å¯m|at|Ã¡m|os|us|Ã½m|mi|ou)$/');
    define('cz_case4', '/(a|e|i|o|u|y|Ã¡|Ã©|Ã­|Ã½|Ä›)$/');

    class cz_stemmer{

        public function stem($word) {
            //$word = lower_case($word);
            $word = self::Remove_Case($word);
            $word = self::Remove_Possessives($word);
            $word = self::Normalize($word);
            return $word;
        }

        private function Remove_Case($word) {
            $word1 = preg_replace(cz_case1, '', $word);           
            if ($word1 != $word) return $word1;            
            $word1 = preg_replace(cz_case2, '', $word);          
            if ($word1 != $word) return $word1;            
            $word1 = preg_replace(cz_case3, '', $word);           
            if ($word1 != $word) return $word1;            
            $word = preg_replace(cz_case4, '', $word);
            return $word;
        }

        private function Remove_Possessives($word) {
            $word = preg_replace('/(ov|in|Å¯v)$/', '', $word);
            return $word;
        }

        private function Normalize($word) {
            $word = preg_replace('/(Ät)$/', 'ck', $word);
            $word = preg_replace('/(Å¡t)$/', 'sk', $word);
            $word = preg_replace('/(c|Ä)$/', 'k', $word);
            $word = preg_replace('/(z|Å¾)$/', 'h', $word);
            $word = preg_replace('/(\.Å¯\.)$/', '.o.', $word);
            return $word;
        }

    }
?>