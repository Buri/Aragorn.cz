<?php

abstract class Utilities{
    static public function string2url($string){
        $string = str_replace(array('ě','š','č','ř','ž'), array('e','s','c','r','z'), strtolower($string));
        return trim(preg_replace('~[^0-9a-z]+~i', '-', html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($string, ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8')), '-');
    }

}