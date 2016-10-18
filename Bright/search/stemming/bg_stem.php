<?php

 /* o------------------------------------------------------------------------------o
    *
    *  This script was originally written in PEARL by  Ljiljana Dolamic and Jacques Savoy
    *
    *  Improvements, PHP5 implementation and adapted for Sphider-plus application
    *   by Rolf Kellner [Tec] Feb. 2010
    *
    * o------------------------------------------------------------------------------o */

    define('bg_article', '/(ÑŠÑ‚|Ñ‚Ð¾|Ñ‚Ðµ|Ñ‚Ð°)$/');
    define('bg_plural', '/(Ð¸Ñ‰Ð°|Ð¸Ñ‰Ðµ|Ð¾Ð²Ðµ|Ñ‚Ð°)$/');
    define('bg_normal', '/(ÐµÐ¸|Ð¸Ð¸|Ð°Ð¾Ð¹)$/');

    class bg_stemmer{

        public function stem($word) {
            //$word = lower_case($word);
            $word = self::Remove_Article($word);
            $word = self::Remove_Plural($word);
            $word = self::Normalize($word);
            $word = self::Palatalization($word);
            return $word;
        }

        private function Remove_Article($word) {
            $word = preg_replace(bg_article, '', $word);
            if (preg_match('/(ÑÑ‚)$/', $word)){
                if (preg_match("/(a|e|Ð¸|Ð¾|Ñƒ|ÑŠ)$/", substr($word, 0, -4))) { //  word ends with vowal + ÑÑ‚
                    $word = preg_replace('/(ÑÑ‚)$/', 'Ð¹', $word);
                } else {
                    $word = preg_replace('/(ÑÑ‚)$/', '', $word);
                }
            }
            return $word;
        }

        private function Remove_Plural($word) {

            $word = preg_replace(bg_plural, '', $word);
            $word = preg_replace('/(Ð¾Ð²Ñ†Ð¸)$/', 'Ð¾', $word);
            $word = preg_replace('/(ÐµÐ²Ñ†Ð¸)$/', 'Ðµ', $word);
            $word = preg_replace('/(\.\.Ðµ\.Ð¸)$/', '.Ñ.', $word);


            if (preg_match('/(ÐµÐ²Ðµ)$/', $word)){
                if (preg_match("/(a|e|Ð¸|Ð¾|Ñƒ|ÑŠ)$/", substr($word, 0, -6))) { //  word ends with vowal + ÐµÐ²Ðµ
                    $word = preg_replace('/(ÐµÐ²Ðµ)$/', 'Ð¹', $word);
                } else {
                    $word = preg_replace('/(ÐµÐ²Ðµ)$/', '', $word);
                }
            }
            return $word;
        }

        private function Normalize($word) {

            $word = preg_replace(bg_normal, '', $word);
            $word = preg_replace('/(Ð¹Ð½)$/', 'Ð½', $word);
            $word = preg_replace('/(LÐµC)$/', 'LC', $word);
            $word = preg_replace('/(LÑŠL)$/', 'LL', $word);

            if (preg_match('/(Ñ)$/', $word)){
                if (preg_match("/(a|e|Ð¸|Ð¾|Ñƒ|ÑŠ)$/", substr($word, 0, -2))) { //  word ends with vowal +  Ñ
                    $word = preg_replace('/(Ñ)$/', 'Ð¹', $word);
                } else {
                    $word = preg_replace('/(Ñ)$/', '', $word);
                }
            }
            return $word;
        }

        private function Palatalization($word) {
            $word = preg_replace('/(Ñ†|Ñ‡)$/', 'Ðº', $word);
            $word = preg_replace('/(Ð·|Ð¶)$/', 'Ð³', $word);
            $word = preg_replace('/(Ñ|Ñˆ)$/', 'Ñ…', $word);
            return $word;
        }

    }
?>