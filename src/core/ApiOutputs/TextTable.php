<?php
namespace MapasCulturais\ApiOutputs;
use \MapasCulturais\App;
use MapasCulturais;

/**
 * Saída de API em formato de tabela de texto (ASCII)
 * 
 * Esta classe gera tabelas de texto formatadas com caracteres ASCII,
 * útil para visualização de dados em terminais ou logs.
 * 
 * @package MapasCulturais\ApiOutputs
 */
class TextTable extends \MapasCulturais\ApiOutput{

    /**
     * Retorna o tipo de conteúdo HTTP para esta saída
     * 
     * @return string Tipo de conteúdo (text/plain; charset=utf-8)
     */
    protected function getContentType() {
        return 'text/plain; charset=utf-8';
    }


    /**
     * Gera a saída de tabela de texto para um array de dados
     * 
     * @param array $data Dados a serem formatados como tabela
     * @param string $singular_object_name Nome no singular para a entidade (não utilizado)
     * @param string $plural_object_name Nome no plural para a entidade (não utilizado)
     */
    protected function _outputArray(array $data, $singular_object_name = 'Entity', $plural_object_name = 'Entities') {
        echo $this->arr2textTable($data);
    }

    /**
     * Converte um array em uma tabela de texto formatada
     * 
     * @param array $table Array de dados a serem convertidos
     * @return string Tabela de texto formatada
     */
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

    /**
     * Função de preenchimento de string com suporte a multibyte (UTF-8)
     * 
     * @param string $str String original
     * @param int $pad_len Comprimento total desejado
     * @param string $pad_str String de preenchimento (padrão: espaço)
     * @param int $dir Direção do preenchimento (STR_PAD_RIGHT, STR_PAD_LEFT, STR_PAD_BOTH)
     * @param string|null $encoding Codificação (padrão: mb_internal_encoding)
     * @return string String preenchida
     */
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

    /**
     * Gera a saída para um único item (usa dump para debug)
     * 
     * @param mixed $data Dados a serem exibidos
     * @param string $object_name Nome do objeto
     */
    function _outputItem($data, $object_name = 'entity') {
        \dump($data); 
    }

    /**
     * Gera a saída para um erro (usa dump para debug)
     * 
     * @param mixed $data Dados do erro
     */
    protected function _outputError($data) {
        \dump($data);
    }
}
