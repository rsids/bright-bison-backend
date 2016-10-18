<?php
/**
 * Sends the actual mail
 * @author Fur - Ids Klijnsma
 * @version 2.2
 * @package Bright
 * @subpackage mailing
 */

include_once(dirname(__FILE__) . '/../Bright.php');

$mailing = new Mailing();

$mailing -> realSend();