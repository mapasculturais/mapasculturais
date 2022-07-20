<?php
namespace MapasCulturais\ApiOutputs;
use \MapasCulturais\App;
use MapasCulturais;



class TextTable extends \MapasCulturais\ApiOutput{

    protected function getContentType() {
        return 'text/plain; charset=utf-8';
    }


    protected function _outputArray(array $data, $singular_object_name = 'Entity', $plural_object_name = 'Entities') {
        echo $this->arr2textTable($data);
    }

    function arr2textTable($table) {
        function clean($var) { 
            $search=array("`((?:https?|ftp)://\S+[[:alnum:]]/?)`si","`((?<!//)(www\.\S+[[:alnum:]]/?))`si");
            $replace=array("<a href=\"$1\" rel=\"nofollow\">$1</a>","<a href=\"http://$1\" rel=\"nofollow\">$1</a>");
            $var = preg_replace($search, $replace, $var);
            return $var;
        }
        foreach ($table AS $row) {
            $cell_count = 0;
            foreach ($row AS $key=>$cell) {
                $cell_length = mb_strlen($cell);
                $key_length = mb_strlen($key);
                $cell_length = $key_length > $cell_length ? $key_length : $cell_length;
                $cell_count++;
                if (!isset($cell_lengths[$key]) || $cell_length > $cell_lengths[$key])
                    $cell_lengths[$key] = $cell_length;
            }   
        }
        $bar = "+";
        $header = "|";
        foreach ($cell_lengths AS $fieldname => $length) {
            $bar .= $this->mb_str_pad("", $length+2, "-")."+";
            $name = $fieldname;
            if (mb_strlen($name) > $length) {
                $name = substr($name, 0, $length-1);
            }
            $header .= " ".$this->mb_str_pad($name, $length, " ", STR_PAD_RIGHT) . " |";
        }
        $output = "${bar}\n${header}\n${bar}\n";
        foreach ($table AS $row) {
            $output .= "|";
            foreach ($row AS $key=>$cell) {
                $output .= " ".$this->mb_str_pad($cell, $cell_lengths[$key], " ", STR_PAD_RIGHT) . " |";
            }
            $output .= "\n";
        }
        $output .= $bar."\n";
        return clean($output);
    }

    function mb_str_pad($str, $pad_len, $pad_str = ' ', $dir = STR_PAD_RIGHT, $encoding = NULL)
        {
            $encoding = $encoding === NULL ? mb_internal_encoding() : $encoding;
            $padBefore = $dir === STR_PAD_BOTH || $dir === STR_PAD_LEFT;
            $padAfter = $dir === STR_PAD_BOTH || $dir === STR_PAD_RIGHT;
            $pad_len -= mb_strlen($str, $encoding);
            $targetLen = $padBefore && $padAfter ? $pad_len / 2 : $pad_len;
            $strToRepeatLen = mb_strlen($pad_str, $encoding);
            $repeatTimes = ceil($targetLen / $strToRepeatLen);
            $repeatedString = str_repeat($pad_str, max(0, $repeatTimes)); // safe if used with valid utf-8 strings
            $before = $padBefore ? mb_substr($repeatedString, 0, floor($targetLen), $encoding) : '';
            $after = $padAfter ? mb_substr($repeatedString, 0, ceil($targetLen), $encoding) : '';
            return $before . $str . $after;
        }

    function _outputItem($data, $object_name = 'entity') {
        \dump($data); 
    }

    protected function _outputError($data) {
        \dump($data);
    }
}
