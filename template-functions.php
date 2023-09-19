<?php

if ( !function_exists('biblio_print_lang_value') ) {
    function biblio_print_lang_value($value, $lang_code){
        $lang_code = substr($lang_code,0,2);
        if ( is_array($value) ){
            foreach($value as $current_value){
                $print_values[] = biblio_get_lang_value($current_value, $lang_code);
            }
            echo implode(', ', $print_values);
        }else{
            echo biblio_get_lang_value($value, $lang_code);
        }
        return;
    }
}

if ( !function_exists('biblio_get_lang_value') ) {
    function biblio_get_lang_value($string, $lang_code, $default_lang_code = 'en'){
        $lang_value = array();
        $occs = preg_split('/\|/', $string);

        foreach ($occs as $occ){
            $re_sep = (strpos($occ, '~') !== false ? '/\~/' : '/\^/');
            $lv = preg_split($re_sep, $occ);
            $lang = substr($lv[0],0,2);
            $value = $lv[1];
            $lang_value[$lang] = $value;
        }

        if ( isset($lang_value[$lang_code]) ){
            $translated = $lang_value[$lang_code];
        } elseif ( isset($lang_value[$default_lang_code]) ){
            $translated = $lang_value[$default_lang_code];
        } else {
            $translated = ltrim(strstr($string, '^'), '^');
            if ( !$translated ) $translated = $string;
        }

        return $translated;
    }
}

if ( !function_exists('print_formated_date') ) {
    function print_formated_date($string){
        echo substr($string,6,2)  . '/' . substr($string,4,2) . '/' . substr($string,0,4);
    }
}

if ( !function_exists('isUTF8') ) {
    function isUTF8($string){
        return (utf8_encode(utf8_decode($string)) == $string);
    }
}

if ( !function_exists('translate_label') ) {
    function translate_label($texts, $label, $group=NULL) {
        // labels on texts.ini must be array key without spaces
        $label_norm = preg_replace('/[&,\'\s]+/', '_', $label);
        if($group == NULL) {
            if(isset($texts[$label_norm]) and $texts[$label_norm] != "") {
                return $texts[$label_norm];
            }
        } else {
            if(isset($texts[$group][$label_norm]) and $texts[$group][$label_norm] != "") {
                return $texts[$group][$label_norm];
            }
        }
        // case translation not found return original label ucfirst
        return ucfirst($label);
    }
}

if ( !function_exists('real_site_url') ) {
    function real_site_url($path = ''){

        $site_url = get_site_url();

        // check for multi-language-framework plugin
        if ( function_exists('mlf_parseURL') ) {
            global $mlf_config;

            $current_language = substr( strtolower(get_bloginfo('language')),0,2 );

            if ( $mlf_config['default_language'] != $current_language ){
                $site_url .= '/' . $current_language;
            }
        }
        // check for polylang plugin
        elseif ( defined('POLYLANG_VERSION') ) {
            $defLang = pll_default_language();
            $curLang = pll_current_language();

            if ($defLang != $curLang) {
                $site_url .= '/' . $curLang;
            }
        }

        if ($path != ''){
            $site_url .= '/' . $path;
        }
        $site_url .= '/';


        return $site_url;
    }
}

if ( !function_exists('get_publication_language') ) {
    function get_publication_language($labels, $lang){
        $publication_language = '';

        if ( $labels ) {
            foreach ($labels as $label) {
                if (strpos($label, $lang) === 0) {
                    $arr = explode('^', $label);
                    $publication_language = $arr[1];
                }
            }
        }

        return $publication_language;
    }
}

?>
