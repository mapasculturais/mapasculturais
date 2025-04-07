<?php
function env(string $name, $default = null) {
    if(defined('GENERATING_CONFIG_DOCUMENTATION')){
        __log_env($name, $default);
    }

    $result = isset($_ENV[$name]) ? trim($_ENV[$name]) : $default;

    if(is_string($result)) {
        if (strtolower($result) == 'true') {
            $result = true;
        } else if (strtolower($result) == 'false') {
            $result = false;
        } else if (filter_var($result, FILTER_VALIDATE_FLOAT)) {
            $result = (float) $result;
        } else if (filter_var($result, FILTER_VALIDATE_INT)) {
            $result = (int) $result;
        }
    }

    return $result;
}

function __env_not_false($var_name){
    return strtolower(env($var_name, 0)) !== 'false';
}


function __log_env($name,$default){
    $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    
    $filename = $bt[1]['file'];
    $fileline = $bt[1]['line'];
    $lines = file($filename);
    $line = trim($lines[$fileline - 1]);

    if(preg_match("#'([\w\d\.]+)' *=> *env\('{$name}', *(.*?)\),#", $line, $matches)){
        $config = $matches[1];
        $default = $matches[2];
    }
    if(!$config){
        return;
    }
    $_lines = implode("", array_slice($lines, max($fileline - 20, 0), min(19, $fileline-1)));
    $_preg_line = preg_quote($line, '#');
    $_pattern = "#\/\*((\*(?!\/)|[^*])*)\*\/$#";
    
    $description = '';
    $matches = false;
    if(preg_match($_pattern, $_lines, $matches)){
        $description = $matches[1];
    }

    if(empty(strpos($config, '.'))){
        $_line_number = $fileline;

        while($_line_number > 0){
            $_current_line = $lines[--$_line_number];
            // buscando linha comom essa: 'app.apiCache.lifetimeByController' => [
            if(preg_match("#'([\w\d\.]+)' *=> *(\[|array\() *$#", $_current_line, $matches)){
                $config = $matches[1] . ' => ' . $config;
                break;
            }
        }    
    }

    $filename = str_replace(BASE_PATH, '', $filename);

    $description = implode("\n", array_map(function($l) { return trim($l); }, explode("\n", $description)));

    $doc = "\n\n\n## $config";
    $doc .= $description ? "\n{$description}": '';
    $doc .= "\n\n - definível pela variável de ambiente **{$name}**";
    $doc .= $default ? "\n - o valor padrão é `{$default}`" : '';
    $doc .= "\n - definido em `{$filename}:$fileline`";
    
    echo "$doc";
}

function dump_closure(\Closure $c) {
    $str = 'function (';
    $r = new \ReflectionFunction($c);
    $params = array();
    foreach($r->getParameters() as $p) {
        $s = '';
        if($p->isArray()) {
            $s .= 'array ';
        } else if($p->getClass()) {
            $s .= $p->getClass()->name . ' ';
        }
        if($p->isPassedByReference()){
            $s .= '&';
        }
        $s .= '$' . $p->name;
        if($p->isOptional()) {
            $s .= ' = ' . var_export($p->getDefaultValue(), TRUE);
        }
        $params []= $s;
    }
    $str .= implode(', ', $params);
    $str .= '){' . PHP_EOL;
    $lines = file($r->getFileName());
    for($l = $r->getStartLine(); $l < $r->getEndLine(); $l++) {
        $str .= $lines[$l];
    }
    return $str;
}