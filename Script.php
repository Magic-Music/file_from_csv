<?php
/**
 * These are default conversion functions available to any conversion script.
 */

class Script
{
    public $placeMarker='~';
    
    protected $computed = [];

    private $languageMap = [
        'FR' => 'fr',
        'DE' => 'de',
        'ES' => 'es',
        'IT' => 'it',
        'RU' => 'ru',
        'SE' => 'sv',
    ];


    /**
     * These conversion functions take a single value.
     *
     * Placeholder syntax:  
     * 
     * ~function~
     * The function i
     *
     * ~function:header~
     * ~upper:firstname~ will return the uppercase value from the 'firstname' column
     */


    /**
     * Input 'se' or 'SE' returns 'sv_SE'
     */
    public function locale_2_to_5_char($locale){
        $locale = strtoupper($locale);
        if(array_key_exists($locale, $this->languageMap)) {
            $prefix = $this->languageMap[$locale];
        } else {
            $prefix = 'en';
        }
        return $prefix . "_" . $locale;
    }

    public function upper($string = null)
    {
        return strtoupper($string);
    }

    public function lower($string = null)
    {
        return strtolower($string);
    }

    public function usfirst($string = null)
    {
        return ucfirst(strtolower($string));
    }

    public function titlecase($string = null)
    {
        return ucwords(strtolower($string));
    }

    public function date_ymd($string = 'today'){
        if(!is_string($string) || $string == 'today' || !$string){
            return date('Y-m-d');
        } else {
            return date('Y-m-d', strtotime($string));
        }
    }

    public function date_dmy($string = 'today'){
        if(!is_string($string) || $string == 'today' || !$string){
            return date('d/m/Y');
        } else {
            return date('d/m/Y', strtotime($string));
        }
    }

    public function datetime($string = 'now'){
        if(!is_string($string) || $string=='now' || !$string){
            return date('Y-m-d H:i:s');
        } else {
            return date('Y-m-d H:i:s', strtotime($string));
        }
    }

    public function dmydatetime($string = 'now'){
        if(!is_string($string) || $string=='now' || !$string){
            return date('d/m/Y H:i:s');
        } else {
            return date('d/m/Y H:i:s', strtotime($string));
        }
    }

    public function timestamp($string = 'now'){
        if(!is_string($string) || $string=='now' || !$string){
            return date('Y-m-d\TH:i:s.00');
        } else {
            return date('Y-m-d\TH:i:s.00', strtotime($string));
        }
    }

    public function iso8601($string = 'now')
    {
        if(!is_string($string) || $string == 'now' || !$string) {
            return date("Y-m-d\TH:i:s.000\Z");
        } else {
            return date("Y-m-d\TH:i:s.000\Z", strtotime($string));
        }
    }
}