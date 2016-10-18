<?php

 /* o------------------------------------------------------------------------------o
    *
    *  This script was originally written by  G.Ntais, first php port ny P.Kyriakidis
    *   Copyright (c) 2008 Panos Kyriakakis
    *   as a result of Martin Porter's stemming algorithm for Greek language.
    *
    *  Adapted for Sphider-plus application by Rolf Kellner [Tec] Feb. 2010
    *
    * o------------------------------------------------------------------------------o */

    class el_stemmer{
        private $step1list;
        private $step1regexp;
        private $v;
        private $v2;
        
        public function __construct() {
            
            $this->step1list = array();
            $this->step1list['Ï†Î±Î³Î¹Î±']='Ï†Î±';
            $this->step1list['Ï†Î±Î³Î¹Î¿Ï…']='Ï†Î±';
            $this->step1list['Ï†Î±Î³Î¹Ï‰Î½']='Ï†Î±';
            $this->step1list['ÏƒÎºÎ±Î³Î¹Î±']='ÏƒÎºÎ±';
            $this->step1list['ÏƒÎºÎ±Î³Î¹Î¿Ï…']='ÏƒÎºÎ±';
            $this->step1list['ÏƒÎºÎ±Î³Î¹Ï‰Î½']='ÏƒÎºÎ±';
            $this->step1list['Î¿Î»Î¿Î³Î¹Î¿Ï…']='Î¿Î»Î¿';
            $this->step1list['Î¿Î»Î¿Î³Î¹Î±']='Î¿Î»Î¿';
            $this->step1list['Î¿Î»Î¿Î³Î¹Ï‰Î½']='Î¿Î»Î¿';
            $this->step1list['ÏƒÎ¿Î³Î¹Î¿Ï…']='ÏƒÎ¿';
            $this->step1list['ÏƒÎ¿Î³Î¹Î±']='ÏƒÎ¿';
            $this->step1list['ÏƒÎ¿Î³Î¹Ï‰Î½']='ÏƒÎ¿';
            $this->step1list['Ï„Î±Ï„Î¿Î³Î¹Î±']='Ï„Î±Ï„Î¿';
            $this->step1list['Ï„Î±Ï„Î¿Î³Î¹Î¿Ï…']='Ï„Î±Ï„Î¿';
            $this->step1list['Ï„Î±Ï„Î¿Î³Î¹Ï‰Î½']='Ï„Î±Ï„Î¿';
            $this->step1list['ÎºÏÎµÎ±Ïƒ']='ÎºÏÎµ';
            $this->step1list['ÎºÏÎµÎ±Ï„Î¿Ïƒ']='ÎºÏÎµ';
            $this->step1list['ÎºÏÎµÎ±Ï„Î±']='ÎºÏÎµ';
            $this->step1list['ÎºÏÎµÎ±Ï„Ï‰Î½']='ÎºÏÎµ';
            $this->step1list['Ï€ÎµÏÎ±Ïƒ']='Ï€ÎµÏ';
            $this->step1list['Ï€ÎµÏÎ±Ï„Î¿Ïƒ']='Ï€ÎµÏ';
            $this->step1list['Ï€ÎµÏÎ±Ï„Î±']='Ï€ÎµÏ';
            $this->step1list['Ï€ÎµÏÎ±Ï„Ï‰Î½']='Ï€ÎµÏ';
            $this->step1list['Ï„ÎµÏÎ±Ïƒ']='Ï„ÎµÏ';
            $this->step1list['Ï„ÎµÏÎ±Ï„Î¿Ïƒ']='Ï„ÎµÏ';
            $this->step1list['Ï„ÎµÏÎ±Ï„Î±']='Ï„ÎµÏ';
            $this->step1list['Ï„ÎµÏÎ±Ï„Ï‰Î½']='Ï„ÎµÏ';
            $this->step1list['Ï†Ï‰Ïƒ']='Ï†Ï‰';
            $this->step1list['Ï†Ï‰Ï„Î¿Ïƒ']='Ï†Ï‰';
            $this->step1list['Ï†Ï‰Ï„Î±']='Ï†Ï‰';
            $this->step1list['Ï†Ï‰Ï„Ï‰Î½']='Ï†Ï‰';
            $this->step1list['ÎºÎ±Î¸ÎµÏƒÏ„Ï‰Ïƒ']='ÎºÎ±Î¸ÎµÏƒÏ„';
            $this->step1list['ÎºÎ±Î¸ÎµÏƒÏ„Ï‰Ï„Î¿Ïƒ']='ÎºÎ±Î¸ÎµÏƒÏ„';
            $this->step1list['ÎºÎ±Î¸ÎµÏƒÏ„Ï‰Ï„Î±']='ÎºÎ±Î¸ÎµÏƒÏ„';
            $this->step1list['ÎºÎ±Î¸ÎµÏƒÏ„Ï‰Ï„Ï‰Î½']='ÎºÎ±Î¸ÎµÏƒÏ„';
            $this->step1list['Î³ÎµÎ³Î¿Î½Î¿Ïƒ']='Î³ÎµÎ³Î¿Î½';
            $this->step1list['Î³ÎµÎ³Î¿Î½Î¿Ï„Î¿Ïƒ']='Î³ÎµÎ³Î¿Î½';
            $this->step1list['Î³ÎµÎ³Î¿Î½Î¿Ï„Î±']='Î³ÎµÎ³Î¿Î½';
            $this->step1list['Î³ÎµÎ³Î¿Î½Î¿Ï„Ï‰Î½']='Î³ÎµÎ³Î¿Î½';
            $this->step1regexp = '/(.*)('.implode('|',array_keys($this->step1list)).')$/u';
            
            $this->v = '[Î±ÎµÎ·Î¹Î¿Ï…Ï‰]';	// vowel
            $this->v2 = '[Î±ÎµÎ·Î¹Î¿Ï‰]'; //vowel without y
        }

        public function stem($word) {

            $stem='';
            $suffix='';
            $firstch='';
            
            $test1 = true;

            if( mb_strlen($word, 'utf-8') < 4 ) { 
                return( $word ); 
            }
        
            //Step1
            if( preg_match($this->step1regexp,$word,$fp) ) {
                $stem = $fp[1];
                $suffix = $fp[2];
                $word = $stem . $this->step1list[$suffix];
                $test1 = false;
            }
            
            // Step 2a
          $re = '/^(.+?)(Î±Î´ÎµÏƒ|Î±Î´Ï‰Î½)$/u';
            if( preg_match($re,$word,$fp) ) {
                $stem = $fp[1];
                $word = $stem;
              $re = '/(Î¿Îº|Î¼Î±Î¼|Î¼Î±Î½|Î¼Ï€Î±Î¼Ï€|Ï€Î±Ï„ÎµÏ|Î³Î¹Î±Î³Î¹|Î½Ï„Î±Î½Ï„|ÎºÏ…Ï|Î¸ÎµÎ¹|Ï€ÎµÎ¸ÎµÏ)$/u';
              if( !preg_match($re,$word) ) {
                $word = $word . "Î±Î´";
              }
            }

            //step 2b
            $re = '/^(.+?)(ÎµÎ´ÎµÏƒ|ÎµÎ´Ï‰Î½)$/u';			
            if( preg_match($re,$word,$fp) ) {
                $stem = $fp[1];
                $word = $stem;
                $exept2 = '/(Î¿Ï€|Î¹Ï€|ÎµÎ¼Ï€|Ï…Ï€|Î³Î·Ï€|Î´Î±Ï€|ÎºÏÎ±ÏƒÏ€|Î¼Î¹Î»)$/u';
                if( preg_match($exept2,$word) ) {
                  $word = $word . 'ÎµÎ´';
                }
            }
            
            //step 2c
            $re = '/^(.+?)(Î¿Ï…Î´ÎµÏƒ|Î¿Ï…Î´Ï‰Î½)$/u';
            if( preg_match($re,$word,$fp) ) {
                $stem = $fp[1];
                $word = $stem;
            
                $exept3 = '/(Î±ÏÎº|ÎºÎ±Î»Î¹Î±Îº|Ï€ÎµÏ„Î±Î»|Î»Î¹Ï‡|Ï€Î»ÎµÎ¾|ÏƒÎº|Ïƒ|Ï†Î»|Ï†Ï|Î²ÎµÎ»|Î»Î¿Ï…Î»|Ï‡Î½|ÏƒÏ€|Ï„ÏÎ±Î³|Ï†Îµ)$/u';
                if( preg_match($exept3,$word) ) {
                  $word = $word . 'Î¿Ï…Î´';
                }
            }

            //step 2d
            $re = '/^(.+?)(ÎµÏ‰Ïƒ|ÎµÏ‰Î½)$/u';
            if( preg_match($re,$word,$fp) ) {
                $stem = $fp[1];
                $word = $stem;
                $test1 = false;
              
                $exept4 = '/^(Î¸|Î´|ÎµÎ»|Î³Î±Î»|Î½|Ï€|Î¹Î´|Ï€Î±Ï)$/u';
                  if( preg_match($exept4,$word) ) {
                  $word = $word . 'Îµ';		
                }
            }

            //step 3
            $re = '/^(.+?)(Î¹Î±|Î¹Î¿Ï…|Î¹Ï‰Î½)$/u';
            if( preg_match($re,$word,$fp) ) {
                $stem = $fp[1];
                $word = $stem;		
                
                $re = '/'.$this->v.'$/u';
                $test1 = false;	
                if( preg_match($re,$word) ) {
                    $word = $stem . 'Î¹';	
                }
            }
            
            //step 4
            $re = '/^(.+?)(Î¹ÎºÎ±|Î¹ÎºÎ¿|Î¹ÎºÎ¿Ï…|Î¹ÎºÏ‰Î½)$/u';
            if( preg_match($re,$word,$fp) ) {
                $stem = $fp[1];
                $word = $stem;
                
                $test1 = false;						
                $re = '/'.$this->v.'$/u';
                $exept5 = '/^(Î±Î»|Î±Î´|ÎµÎ½Î´|Î±Î¼Î±Î½|Î±Î¼Î¼Î¿Ï‡Î±Î»|Î·Î¸|Î±Î½Î·Î¸|Î±Î½Ï„Î¹Î´|Ï†Ï…Ïƒ|Î²ÏÏ‰Î¼|Î³ÎµÏ|ÎµÎ¾Ï‰Î´|ÎºÎ±Î»Ï€|ÎºÎ±Î»Î»Î¹Î½|ÎºÎ±Ï„Î±Î´|Î¼Î¿Ï…Î»|Î¼Ï€Î±Î½|Î¼Ï€Î±Î³Î¹Î±Ï„|Î¼Ï€Î¿Î»|Î¼Ï€Î¿Ïƒ|Î½Î¹Ï„|Î¾Î¹Îº|ÏƒÏ…Î½Î¿Î¼Î·Î»|Ï€ÎµÏ„Ïƒ|Ï€Î¹Ï„Ïƒ|Ï€Î¹ÎºÎ±Î½Ï„|Ï€Î»Î¹Î±Ï„Ïƒ|Ï€Î¿ÏƒÏ„ÎµÎ»Î½|Ï€ÏÏ‰Ï„Î¿Î´|ÏƒÎµÏÏ„|ÏƒÏ…Î½Î±Î´|Ï„ÏƒÎ±Î¼|Ï…Ï€Î¿Î´|Ï†Î¹Î»Î¿Î½|Ï†Ï…Î»Î¿Î´|Ï‡Î±Ïƒ)$/u';
                if( preg_match($re,$word) || preg_match($exept5,$word) ) {
                  $word = $word . 'Î¹Îº';
                }
            }

            //step 5a
            $re = '/^(.+?)(Î±Î¼Îµ)$/u';
            $re2 = '/^(.+?)(Î±Î³Î±Î¼Îµ|Î·ÏƒÎ±Î¼Îµ|Î¿Ï…ÏƒÎ±Î¼Îµ|Î·ÎºÎ±Î¼Îµ|Î·Î¸Î·ÎºÎ±Î¼Îµ)$/u';
            if ($word == "Î±Î³Î±Î¼Îµ") {
              $word = "Î±Î³Î±Î¼";
            }
                
            if( preg_match($re2,$word,$fp) ) {
                $stem = $fp[1];
                $word = $stem;		
                $test1 = false;
            }	
                
            if( preg_match($re,$word,$fp) ) {
                $stem = $fp[1];
                $word = $stem;
                $test1 = false;
                
                $exept6 = '/^(Î±Î½Î±Ï€|Î±Ï€Î¿Î¸|Î±Ï€Î¿Îº|Î±Ï€Î¿ÏƒÏ„|Î²Î¿Ï…Î²|Î¾ÎµÎ¸|Î¿Ï…Î»|Ï€ÎµÎ¸|Ï€Î¹ÎºÏ|Ï€Î¿Ï„|ÏƒÎ¹Ï‡|Ï‡)$/u';			
                if( preg_match($exept6,$word) ) {
                    $word = $word . "Î±Î¼";
                }
            }	
            
            //step 5b
            $re2 = '/^(.+?)(Î±Î½Îµ)$/u';
            $re3 = '/^(.+?)(Î±Î³Î±Î½Îµ|Î·ÏƒÎ±Î½Îµ|Î¿Ï…ÏƒÎ±Î½Îµ|Î¹Î¿Î½Ï„Î±Î½Îµ|Î¹Î¿Ï„Î±Î½Îµ|Î¹Î¿Ï…Î½Ï„Î±Î½Îµ|Î¿Î½Ï„Î±Î½Îµ|Î¿Ï„Î±Î½Îµ|Î¿Ï…Î½Ï„Î±Î½Îµ|Î·ÎºÎ±Î½Îµ|Î·Î¸Î·ÎºÎ±Î½Îµ)$/u';
                
            if( preg_match($re3,$word,$fp) ) {
                $stem = $fp[1];
                $word = $stem;
                $test1 = false;
                    
                $re3 = '/^(Ï„Ï|Ï„Ïƒ)$/u';		
                if( preg_match($re3,$word) ) {
                    $word = $word .  "Î±Î³Î±Î½";
                }
            }
                    
            if( preg_match($re2,$word,$fp) ) {
                $stem = $fp[1];
                $word = $stem;
                $test1 = false;
                            
                $re2 = '/'.$this->v2.'$/u';
                $exept7 = '/^(Î²ÎµÏ„ÎµÏ|Î²Î¿Ï…Î»Îº|Î²ÏÎ±Ï‡Î¼|Î³|Î´ÏÎ±Î´Î¿Ï…Î¼|Î¸|ÎºÎ±Î»Ï€Î¿Ï…Î¶|ÎºÎ±ÏƒÏ„ÎµÎ»|ÎºÎ¿ÏÎ¼Î¿Ï|Î»Î±Î¿Ï€Î»|Î¼Ï‰Î±Î¼ÎµÎ¸|Î¼|Î¼Î¿Ï…ÏƒÎ¿Ï…Î»Î¼|Î½|Î¿Ï…Î»|Ï€|Ï€ÎµÎ»ÎµÎº|Ï€Î»|Ï€Î¿Î»Î¹Ïƒ|Ï€Î¿ÏÏ„Î¿Î»|ÏƒÎ±ÏÎ±ÎºÎ±Ï„Ïƒ|ÏƒÎ¿Ï…Î»Ï„|Ï„ÏƒÎ±ÏÎ»Î±Ï„|Î¿ÏÏ†|Ï„ÏƒÎ¹Î³Î³|Ï„ÏƒÎ¿Ï€|Ï†Ï‰Ï„Î¿ÏƒÏ„ÎµÏ†|Ï‡|ÏˆÏ…Ï‡Î¿Ï€Î»|Î±Î³|Î¿ÏÏ†|Î³Î±Î»|Î³ÎµÏ|Î´ÎµÎº|Î´Î¹Ï€Î»|Î±Î¼ÎµÏÎ¹ÎºÎ±Î½|Î¿Ï…Ï|Ï€Î¹Î¸|Ï€Î¿Ï…ÏÎ¹Ï„|Ïƒ|Î¶Ï‰Î½Ï„|Î¹Îº|ÎºÎ±ÏƒÏ„|ÎºÎ¿Ï€|Î»Î¹Ï‡|Î»Î¿Ï…Î¸Î·Ï|Î¼Î±Î¹Î½Ï„|Î¼ÎµÎ»|ÏƒÎ¹Î³|ÏƒÏ€|ÏƒÏ„ÎµÎ³|Ï„ÏÎ±Î³|Ï„ÏƒÎ±Î³|Ï†|ÎµÏ|Î±Î´Î±Ï€|Î±Î¸Î¹Î³Î³|Î±Î¼Î·Ï‡|Î±Î½Î¹Îº|Î±Î½Î¿ÏÎ³|Î±Ï€Î·Î³|Î±Ï€Î¹Î¸|Î±Ï„ÏƒÎ¹Î³Î³|Î²Î±Ïƒ|Î²Î±ÏƒÎº|Î²Î±Î¸Ï…Î³Î±Î»|Î²Î¹Î¿Î¼Î·Ï‡|Î²ÏÎ±Ï‡Ï…Îº|Î´Î¹Î±Ï„|Î´Î¹Î±Ï†|ÎµÎ½Î¿ÏÎ³|Î¸Ï…Ïƒ|ÎºÎ±Ï€Î½Î¿Î²Î¹Î¿Î¼Î·Ï‡|ÎºÎ±Ï„Î±Î³Î±Î»|ÎºÎ»Î¹Î²|ÎºÎ¿Î¹Î»Î±ÏÏ†|Î»Î¹Î²|Î¼ÎµÎ³Î»Î¿Î²Î¹Î¿Î¼Î·Ï‡|Î¼Î¹ÎºÏÎ¿Î²Î¹Î¿Î¼Î·Ï‡|Î½Ï„Î±Î²|Î¾Î·ÏÎ¿ÎºÎ»Î¹Î²|Î¿Î»Î¹Î³Î¿Î´Î±Î¼|Î¿Î»Î¿Î³Î±Î»|Ï€ÎµÎ½Ï„Î±ÏÏ†|Ï€ÎµÏÎ·Ï†|Ï€ÎµÏÎ¹Ï„Ï|Ï€Î»Î±Ï„|Ï€Î¿Î»Ï…Î´Î±Ï€|Ï€Î¿Î»Ï…Î¼Î·Ï‡|ÏƒÏ„ÎµÏ†|Ï„Î±Î²|Ï„ÎµÏ„|Ï…Ï€ÎµÏÎ·Ï†|Ï…Ï€Î¿ÎºÎ¿Ï€|Ï‡Î±Î¼Î·Î»Î¿Î´Î±Ï€|ÏˆÎ·Î»Î¿Ï„Î±Î²)$/u';
                if( preg_match($re2,$word) || preg_match($exept7,$word) ){
                  $word = $word .  "Î±Î½";
                }
            }
            
            //step 5c
            $re3 = '/^(.+?)(ÎµÏ„Îµ)$/u';
            $re4 = '/^(.+?)(Î·ÏƒÎµÏ„Îµ)$/u';
                
            if( preg_match($re4,$word,$fp) ) {
                $stem = $fp[1];
                $word = $stem;
                $test1 = false;
            }	
                
            if( preg_match($re3,$word,$fp) ) {
                $stem = $fp[1];
                $word = $stem;
                $test1 = false;
        
                $re3 = $this->v2.'$';
                $exept8 =  '/(Î¿Î´|Î±Î¹Ï|Ï†Î¿Ï|Ï„Î±Î¸|Î´Î¹Î±Î¸|ÏƒÏ‡|ÎµÎ½Î´|ÎµÏ…Ï|Ï„Î¹Î¸|Ï…Ï€ÎµÏÎ¸|ÏÎ±Î¸|ÎµÎ½Î¸|ÏÎ¿Î¸|ÏƒÎ¸|Ï€Ï…Ï|Î±Î¹Î½|ÏƒÏ…Î½Î´|ÏƒÏ…Î½|ÏƒÏ…Î½Î¸|Ï‡Ï‰Ï|Ï€Î¿Î½|Î²Ï|ÎºÎ±Î¸|ÎµÏ…Î¸|ÎµÎºÎ¸|Î½ÎµÏ„|ÏÎ¿Î½|Î±ÏÎº|Î²Î±Ï|Î²Î¿Î»|Ï‰Ï†ÎµÎ»)$/u';
                $exept9 = '/^(Î±Î²Î±Ï|Î²ÎµÎ½|ÎµÎ½Î±Ï|Î±Î²Ï|Î±Î´|Î±Î¸|Î±Î½|Î±Ï€Î»|Î²Î±ÏÎ¿Î½|Î½Ï„Ï|ÏƒÎº|ÎºÎ¿Ï€|Î¼Ï€Î¿Ï|Î½Î¹Ï†|Ï€Î±Î³|Ï€Î±ÏÎ±ÎºÎ±Î»|ÏƒÎµÏÏ€|ÏƒÎºÎµÎ»|ÏƒÏ…ÏÏ†|Ï„Î¿Îº|Ï…|Î´|ÎµÎ¼|Î¸Î±ÏÏ|Î¸)$/u';
                
                if( preg_match($re3,$word) || preg_match($exept8,$word) || preg_match($exept9,$word) ){
                  $word = $word .  "ÎµÏ„";
                }
            }
            
            //step 5d
            $re = '/^(.+?)(Î¿Î½Ï„Î±Ïƒ|Ï‰Î½Ï„Î±Ïƒ)$/u';
            if( preg_match($re,$word,$fp) ) {
                $stem = $fp[1];
                $word = $stem;
                $test1 = false;
                            
                $exept10 = '/^(Î±ÏÏ‡)$/u';
                $exept11 = '/(ÎºÏÎµ)$/u';
                if( preg_match($exept10,$word) ){
                  $word = $word . "Î¿Î½Ï„";
                }
                if( preg_match($exept11,$word) ){
                  $word = $word . "Ï‰Î½Ï„";
                }
            }
            
            //step 5e
            $re = '/^(.+?)(Î¿Î¼Î±ÏƒÏ„Îµ|Î¹Î¿Î¼Î±ÏƒÏ„Îµ)$/u';
            if( preg_match($re,$word,$fp) ) {
                $stem = $fp[1];
                $word = $stem;
                $test1 = false;
                            
                $exept11 = '/^(Î¿Î½)$/u';
                if( preg_match($exept11,$word) ){
                  $word = $word .  "Î¿Î¼Î±ÏƒÏ„";
                }
            }
            
            //step 5f
            $re = '/^(.+?)(ÎµÏƒÏ„Îµ)$/u';
            $re2 = '/^(.+?)(Î¹ÎµÏƒÏ„Îµ)$/u';
                
            if( preg_match($re2,$word,$fp) ) {
                $stem = $fp[1];
                $word = $stem;
                $test1 = false;
                            
                $re2 = '/^(Ï€|Î±Ï€|ÏƒÏ…Î¼Ï€|Î±ÏƒÏ…Î¼Ï€|Î±ÎºÎ±Ï„Î±Ï€|Î±Î¼ÎµÏ„Î±Î¼Ï†)$/u';
                if( preg_match($re2,$word) ) {
                  $word = $word . "Î¹ÎµÏƒÏ„";
                }
            }	
                    
            if( preg_match($re,$word,$fp) ) {
                $stem = $fp[1];
                $word = $stem;
                $test1 = false;
                            
                $exept12 = '/^(Î±Î»|Î±Ï|ÎµÎºÏ„ÎµÎ»|Î¶|Î¼|Î¾|Ï€Î±ÏÎ±ÎºÎ±Î»|Î±Ï|Ï€ÏÎ¿|Î½Î¹Ïƒ)$/u';
                if( preg_match($exept12,$word) ){
                  $word = $word . "ÎµÏƒÏ„";
                }
            }
            
            //step 5g
            $re = '/^(.+?)(Î·ÎºÎ±|Î·ÎºÎµÏƒ|Î·ÎºÎµ)$/u';
            $re2 = '/^(.+?)(Î·Î¸Î·ÎºÎ±|Î·Î¸Î·ÎºÎµÏƒ|Î·Î¸Î·ÎºÎµ)$/u';
            
            if( preg_match($re2,$word,$fp) ) {
                $stem = $fp[1];
                $word = $stem;
                $test1 = false;
            }
                
            if( preg_match($re,$word,$fp) ) {
                $stem = $fp[1];
                $word = $stem;
                $test1 = false;
                            
                $exept13 = '/(ÏƒÎºÏ‰Î»|ÏƒÎºÎ¿Ï…Î»|Î½Î±ÏÎ¸|ÏƒÏ†|Î¿Î¸|Ï€Î¹Î¸)$/u';
                $exept14 = '/^(Î´Î¹Î±Î¸|Î¸|Ï€Î±ÏÎ±ÎºÎ±Ï„Î±Î¸|Ï€ÏÎ¿ÏƒÎ¸|ÏƒÏ…Î½Î¸|)$/u';
                if( preg_match($exept13,$word) || preg_match($exept14,$word) ){
                  $word = $word . "Î·Îº";
                }
            }
            
            
            //step 5h
            $re = '/^(.+?)(Î¿Ï…ÏƒÎ±|Î¿Ï…ÏƒÎµÏƒ|Î¿Ï…ÏƒÎµ)$/u';
            if( preg_match($re,$word,$fp) ) {
                $stem = $fp[1];
                $word = $stem;
                $test1 = false;
                            
                $exept15 = '/^(Ï†Î±ÏÎ¼Î±Îº|Ï‡Î±Î´|Î±Î³Îº|Î±Î½Î±ÏÏ|Î²ÏÎ¿Î¼|ÎµÎºÎ»Î¹Ï€|Î»Î±Î¼Ï€Î¹Î´|Î»ÎµÏ‡|Î¼|Ï€Î±Ï„|Ï|Î»|Î¼ÎµÎ´|Î¼ÎµÏƒÎ±Î¶|Ï…Ï€Î¿Ï„ÎµÎ¹Î½|Î±Î¼|Î±Î¹Î¸|Î±Î½Î·Îº|Î´ÎµÏƒÏ€Î¿Î¶|ÎµÎ½Î´Î¹Î±Ï†ÎµÏ|Î´Îµ|Î´ÎµÏ…Ï„ÎµÏÎµÏ…|ÎºÎ±Î¸Î±ÏÎµÏ…|Ï€Î»Îµ|Ï„ÏƒÎ±)$/u';
                $exept16 = '/(Ï€Î¿Î´Î±Ï|Î²Î»ÎµÏ€|Ï€Î±Î½Ï„Î±Ï‡|Ï†ÏÏ…Î´|Î¼Î±Î½Ï„Î¹Î»|Î¼Î±Î»Î»|ÎºÏ…Î¼Î±Ï„|Î»Î±Ï‡|Î»Î·Î³|Ï†Î±Î³|Î¿Î¼|Ï€ÏÏ‰Ï„)$/u';			
                if( preg_match($exept15,$word) || preg_match($exept16,$word) ){
                  $word = $word . "Î¿Ï…Ïƒ";
                }
            }
        
            //step 5i
            $re = '/^(.+?)(Î±Î³Î±|Î±Î³ÎµÏƒ|Î±Î³Îµ)$/u';
                
            if( preg_match($re,$word,$fp) ) {
                $stem = $fp[1];
                $word = $stem;
                $test1 = false;
                
                $exept17 = '/^(ÏˆÎ¿Ï†|Î½Î±Ï…Î»Î¿Ï‡)$/u';
                $exept20 = '/(ÎºÎ¿Î»Î»)$/u';			
                $exept18 = '/^(Î±Î²Î±ÏƒÏ„|Ï€Î¿Î»Ï…Ï†|Î±Î´Î·Ï†|Ï€Î±Î¼Ï†|Ï|Î±ÏƒÏ€|Î±Ï†|Î±Î¼Î±Î»|Î±Î¼Î±Î»Î»Î¹|Î±Î½Ï…ÏƒÏ„|Î±Ï€ÎµÏ|Î±ÏƒÏ€Î±Ï|Î±Ï‡Î±Ï|Î´ÎµÏÎ²ÎµÎ½|Î´ÏÎ¿ÏƒÎ¿Ï€|Î¾ÎµÏ†|Î½ÎµÎ¿Ï€|Î½Î¿Î¼Î¿Ï„|Î¿Î»Î¿Ï€|Î¿Î¼Î¿Ï„|Ï€ÏÎ¿ÏƒÏ„|Ï€ÏÎ¿ÏƒÏ‰Ï€Î¿Ï€|ÏƒÏ…Î¼Ï€|ÏƒÏ…Î½Ï„|Ï„|Ï…Ï€Î¿Ï„|Ï‡Î±Ï|Î±ÎµÎ¹Ï€|Î±Î¹Î¼Î¿ÏƒÏ„|Î±Î½Ï…Ï€|Î±Ï€Î¿Ï„|Î±ÏÏ„Î¹Ï€|Î´Î¹Î±Ï„|ÎµÎ½|ÎµÏ€Î¹Ï„|ÎºÏÎ¿ÎºÎ±Î»Î¿Ï€|ÏƒÎ¹Î´Î·ÏÎ¿Ï€|Î»|Î½Î±Ï…|Î¿Ï…Î»Î±Î¼|Î¿Ï…Ï|Ï€|Ï„Ï|Î¼)$/u';
                $exept19 = '/(Î¿Ï†|Ï€ÎµÎ»|Ï‡Î¿ÏÏ„|Î»Î»|ÏƒÏ†|ÏÏ€|Ï†Ï|Ï€Ï|Î»Î¿Ï‡|ÏƒÎ¼Î·Î½)$/u';
                
                if( (preg_match($exept18,$word) || preg_match($exept19,$word))
                    && !(preg_match($exept17,$word) || preg_match($exept20,$word)) ) {
                  $word = $word . "Î±Î³";
                }
            }
            
            
            //step 5j
            $re = '/^(.+?)(Î·ÏƒÎµ|Î·ÏƒÎ¿Ï…|Î·ÏƒÎ±)$/u';
            if( preg_match($re,$word,$fp) ) {
                $stem = $fp[1];
                $word = $stem;
                $test1 = false;
                            
                $exept21 = '/^(Î½|Ï‡ÎµÏÏƒÎ¿Î½|Î´Ï‰Î´ÎµÎºÎ±Î½|ÎµÏÎ·Î¼Î¿Î½|Î¼ÎµÎ³Î±Î»Î¿Î½|ÎµÏ€Ï„Î±Î½)$/u';
                if( preg_match($exept21,$word) ){
                  $word = $word . "Î·Ïƒ";
                }
            }
            
            //step 5k
            $re = '/^(.+?)(Î·ÏƒÏ„Îµ)$/u';
            if( preg_match($re,$word,$fp) ) {
                $stem = $fp[1];
                $word = $stem;
                $test1 = false;
                        
                $exept22 = '/^(Î±ÏƒÎ²|ÏƒÎ²|Î±Ï‡Ï|Ï‡Ï|Î±Ï€Î»|Î±ÎµÎ¹Î¼Î½|Î´Ï…ÏƒÏ‡Ï|ÎµÏ…Ï‡Ï|ÎºÎ¿Î¹Î½Î¿Ï‡Ï|Ï€Î±Î»Î¹Î¼Ïˆ)$/u';
                if( preg_match($exept22,$word) ){
                  $word = $word . "Î·ÏƒÏ„";
                }
            }
            
            //step 5l
            $re = '/^(.+?)(Î¿Ï…Î½Îµ|Î·ÏƒÎ¿Ï…Î½Îµ|Î·Î¸Î¿Ï…Î½Îµ)$/u';
            if( preg_match($re,$word,$fp) ) {
                $stem = $fp[1];
                $word = $stem;
                $test1 = false;
                            
                $exept23 = '/^(Î½|Ï|ÏƒÏ€Î¹|ÏƒÏ„ÏÎ±Î²Î¿Î¼Î¿Ï…Ï„Ïƒ|ÎºÎ±ÎºÎ¿Î¼Î¿Ï…Ï„Ïƒ|ÎµÎ¾Ï‰Î½)$/u';
                if( preg_match($exept23,$word) ){
                  $word = $word . "Î¿Ï…Î½";
                }
            }
            
            //step 5l
            $re = '/^(.+?)(Î¿Ï…Î¼Îµ|Î·ÏƒÎ¿Ï…Î¼Îµ|Î·Î¸Î¿Ï…Î¼Îµ)$/u';
            if( preg_match($re,$word,$fp) ) {
                $stem = $fp[1];
                $word = $stem;
                $test1 = false;
                            
                $exept24 = '/^(Ï€Î±ÏÎ±ÏƒÎ¿Ï…Ïƒ|Ï†|Ï‡|Ï‰ÏÎ¹Î¿Ï€Î»|Î±Î¶|Î±Î»Î»Î¿ÏƒÎ¿Ï…Ïƒ|Î±ÏƒÎ¿Ï…Ïƒ)$/u';
                if( preg_match($exept24,$word) ){
                  $word = $word . "Î¿Ï…Î¼";
                }
            }
            
            // step 6
            $re = '/^(.+?)(Î¼Î±Ï„Î±|Î¼Î±Ï„Ï‰Î½|Î¼Î±Ï„Î¿Ïƒ)$/u';
            $re2 = '/^(.+?)(Î±|Î±Î³Î±Ï„Îµ|Î±Î³Î±Î½|Î±ÎµÎ¹|Î±Î¼Î±Î¹|Î±Î½|Î±Ïƒ|Î±ÏƒÎ±Î¹|Î±Ï„Î±Î¹|Î±Ï‰|Îµ|ÎµÎ¹|ÎµÎ¹Ïƒ|ÎµÎ¹Ï„Îµ|ÎµÏƒÎ±Î¹|ÎµÏƒ|ÎµÏ„Î±Î¹|Î¹|Î¹ÎµÎ¼Î±Î¹|Î¹ÎµÎ¼Î±ÏƒÏ„Îµ|Î¹ÎµÏ„Î±Î¹|Î¹ÎµÏƒÎ±Î¹|Î¹ÎµÏƒÎ±ÏƒÏ„Îµ|Î¹Î¿Î¼Î±ÏƒÏ„Î±Î½|Î¹Î¿Î¼Î¿Ï…Î½|Î¹Î¿Î¼Î¿Ï…Î½Î±|Î¹Î¿Î½Ï„Î±Î½|Î¹Î¿Î½Ï„Î¿Ï…ÏƒÎ±Î½|Î¹Î¿ÏƒÎ±ÏƒÏ„Î±Î½|Î¹Î¿ÏƒÎ±ÏƒÏ„Îµ|Î¹Î¿ÏƒÎ¿Ï…Î½|Î¹Î¿ÏƒÎ¿Ï…Î½Î±|Î¹Î¿Ï„Î±Î½|Î¹Î¿Ï…Î¼Î±|Î¹Î¿Ï…Î¼Î±ÏƒÏ„Îµ|Î¹Î¿Ï…Î½Ï„Î±Î¹|Î¹Î¿Ï…Î½Ï„Î±Î½|Î·|Î·Î´ÎµÏƒ|Î·Î´Ï‰Î½|Î·Î¸ÎµÎ¹|Î·Î¸ÎµÎ¹Ïƒ|Î·Î¸ÎµÎ¹Ï„Îµ|Î·Î¸Î·ÎºÎ±Ï„Îµ|Î·Î¸Î·ÎºÎ±Î½|Î·Î¸Î¿Ï…Î½|Î·Î¸Ï‰|Î·ÎºÎ±Ï„Îµ|Î·ÎºÎ±Î½|Î·Ïƒ|Î·ÏƒÎ±Î½|Î·ÏƒÎ±Ï„Îµ|Î·ÏƒÎµÎ¹|Î·ÏƒÎµÏƒ|Î·ÏƒÎ¿Ï…Î½|Î·ÏƒÏ‰|Î¿|Î¿Î¹|Î¿Î¼Î±Î¹|Î¿Î¼Î±ÏƒÏ„Î±Î½|Î¿Î¼Î¿Ï…Î½|Î¿Î¼Î¿Ï…Î½Î±|Î¿Î½Ï„Î±Î¹|Î¿Î½Ï„Î±Î½|Î¿Î½Ï„Î¿Ï…ÏƒÎ±Î½|Î¿Ïƒ|Î¿ÏƒÎ±ÏƒÏ„Î±Î½|Î¿ÏƒÎ±ÏƒÏ„Îµ|Î¿ÏƒÎ¿Ï…Î½|Î¿ÏƒÎ¿Ï…Î½Î±|Î¿Ï„Î±Î½|Î¿Ï…|Î¿Ï…Î¼Î±Î¹|Î¿Ï…Î¼Î±ÏƒÏ„Îµ|Î¿Ï…Î½|Î¿Ï…Î½Ï„Î±Î¹|Î¿Ï…Î½Ï„Î±Î½|Î¿Ï…Ïƒ|Î¿Ï…ÏƒÎ±Î½|Î¿Ï…ÏƒÎ±Ï„Îµ|Ï…|Ï…Ïƒ|Ï‰|Ï‰Î½)$/u';		
            if( preg_match($re,$word,$fp) ) {
                $stem = $fp[1];
                $word = $stem . "Î¼Î±";
            }
            
            if( preg_match($re2,$word,$fp) && $test1 ) {
                $stem = $fp[1];
                $word = $stem;
            }
            
            // step 7 (Ï€Î±ÏÎ±Î¸ÎµÏ„Î¹ÎºÎ±)
            $re = '/^(.+?)(ÎµÏƒÏ„ÎµÏ|ÎµÏƒÏ„Î±Ï„|Î¿Ï„ÎµÏ|Î¿Ï„Î±Ï„|Ï…Ï„ÎµÏ|Ï…Ï„Î±Ï„|Ï‰Ï„ÎµÏ|Ï‰Ï„Î±Ï„)$/u';
            if( preg_match($re,$word,$fp) ){
                $stem = $fp[1];
                $word = $stem;
            }		

            return( $word );
        }  
    }
    
?>