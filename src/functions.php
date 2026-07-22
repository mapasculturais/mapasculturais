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


function arrayToAsciiTable($data, $maxTableWidth = 80, $maxColumnWidth = 30) {
    if (empty($data)) {
        return "No data\n";
    }
    
    // Se for array associativo, usar as chaves como cabeçalho
    $headers = [];
    $isAssociative = false;
    
    if (!isset($data[0]) || !is_array($data[0])) {
        return "Invalid data format\n";
    }
    
    $firstRow = $data[0];
    if (array_keys($firstRow) !== range(0, count($firstRow) - 1)) {
        $isAssociative = true;
        $headers = array_keys($firstRow);
    } else {
        $headers = array_keys($firstRow);
        // Se não há headers explícitos, criar headers numéricos
        $headers = array_map(function($key) {
            return "col_" . ($key + 1);
        }, $headers);
    }
    
    $numColumns = count($headers);
    
    // Calcular largura máxima para cada coluna
    $columnWidths = [];
    foreach ($headers as $index => $header) {
        $maxWidth = strlen($header);
        
        foreach ($data as $row) {
            $cellValue = $isAssociative ? $row[$headers[$index]] : $row[$index];
            $cellString = (string)$cellValue;
            $maxWidth = max($maxWidth, min($maxColumnWidth, strlen($cellString)));
        }
        
        $columnWidths[$index] = $maxWidth;
    }
    
    // Ajustar larguras para caber na largura máxima da tabela
    $totalWidth = array_sum($columnWidths) + (3 * $numColumns) + 1;
    
    if ($totalWidth > $maxTableWidth) {
        $excessWidth = $totalWidth - $maxTableWidth;
        $totalColumnWidth = array_sum($columnWidths);
        
        foreach ($columnWidths as $index => &$width) {
            $reduceBy = (int)($excessWidth * ($width / $totalColumnWidth));
            $width = max(5, $width - $reduceBy);
        }
    }
    
    // Processar dados: quebrar células longas em múltiplas linhas
    $processedData = [];
    
    foreach ($data as $rowIndex => $row) {
        $cellLines = [];
        $maxLines = 1;
        
        // Processar cada célula e quebrar se necessário
        foreach ($headers as $colIndex => $header) {
            $cellValue = $isAssociative ? $row[$header] : $row[$colIndex];
            $cellString = (string)$cellValue;
            $cellWidth = $columnWidths[$colIndex];
            
            if (strlen($cellString) <= $cellWidth) {
                $cellLines[$colIndex] = [$cellString];
            } else {
                $cellLines[$colIndex] = wordwrap($cellString, $cellWidth, "\n", true);
                $cellLines[$colIndex] = explode("\n", $cellLines[$colIndex]);
            }
            
            $maxLines = max($maxLines, count($cellLines[$colIndex]));
        }
        
        // Adicionar linhas em branco para células menores
        for ($lineNum = 0; $lineNum < $maxLines; $lineNum++) {
            $newRow = [];
            foreach ($headers as $colIndex => $header) {
                $lineContent = isset($cellLines[$colIndex][$lineNum]) 
                    ? $cellLines[$colIndex][$lineNum] 
                    : '';
                $newRow[$colIndex] = $lineContent;
            }
            $processedData[] = $newRow;
        }
    }
    
    // Construir a tabela
    $output = "";
    
    // Linha superior
    $output .= "+";
    foreach ($columnWidths as $width) {
        $output .= str_repeat("-", $width + 2) . "+";
    }
    $output .= "\n";
    
    // Cabeçalho
    $output .= "|";
    foreach ($headers as $index => $header) {
        $output .= " " . str_pad($header, $columnWidths[$index]) . " |";
    }
    $output .= "\n";
    
    // Separador cabeçalho/dados
    $output .= "+";
    foreach ($columnWidths as $width) {
        $output .= str_repeat("-", $width + 2) . "+";
    }
    $output .= "\n";
    
    // Dados
    foreach ($processedData as $row) {
        $output .= "|";
        foreach ($row as $index => $cell) {
            $output .= " " . str_pad($cell, $columnWidths[$index]) . " |";
        }
        $output .= "\n";
    }
    
    // Linha inferior
    $output .= "+";
    foreach ($columnWidths as $width) {
        $output .= str_repeat("-", $width + 2) . "+";
    }
    $output .= "\n";

    $num = count((array) $data);
    $output .= "($num rows)\n";
    
    return $output;
}

function query(string $sql) {
    $result = \MapasCulturais\App::i()->conn->fetchAll($sql);
    echo arrayToAsciiTable($result, exec('tput cols'), exec('tput cols') / 2);
}