<?php
namespace MapasCulturais\ApiOutputs;
use \MapasCulturais\App;
use MapasCulturais;



class Html extends \MapasCulturais\ApiOutput{

    protected $translate = [
        'id' => 'Id',
        'name' => 'Nome',
        'singleUrl' => 'Link',
        'type' => 'Tipo',
        'shortDescription' => 'Descrição Curta',
        'name' => 'Nome',
        'terms' => 'Termos',
        'endereco' => 'Endereço',
        'classificacaoEtaria' => 'Classificação Etária',
        'project' => 'Projeto',
        'occurrences' => 'Descrição Legível do Horário',
        'tag' => 'Tags',
        'area' => 'Áreas',
        'linguagem' => 'Linguagens',
        'weekly' => 'Semanal',
        'once' => 'Uma vez',
        'daily' => 'Diariamente',
        'agent'=>'Agente',
        'space'=>'Espaço',
        'event'=>'Evento',
        'project'=>'Projeto',
        'seal'=>'Selo',
        'owner' => 'Publicado por',
        'parent' => 'Entidade pai',
        'createTimestamp' => 'Entidade pai',
    ];

    protected $occurrenceDetails = [
        'Data Inicial', 'Data Final', 'Duração', 'Frequência', 
        'Horário Inicial', 'Horário Final'
    ];

    protected $diasSemana = [
        'Repete-Domingo', 'Repete-Segunda', 'Repete-Terça', 
        'Repete-Quarta', 'Repete-Quinta', 'Repete-Sexta', 'Repete-Sábado'
    ];

    protected function getContentType() {
        return 'text/html';
    }

    protected function printTable($data){
        if(is_array($data))
            $this->printArrayTable($data);
        elseif(is_object($data))
            $this->printOneItemTable($data);
        else
            return;
    }

    /**
     * Retorna o numero do dia da semana de 0 a 6
     *
     * @param string $date data no formato Y-m-d
     * @return date
     */
    protected function getDayOfWeek($date){
        $timestamp = strtotime($date);
        return \date('w', $timestamp);
    }

    /**
     * Preenche os dias que o evento se repete
     *
     * @param string $field referente ao dia a ser preenchido
     * @param obj $occurrence
     * @return void
     */
    protected function printDaysOfEvent($field, $occurrence){
        if($occurrence->rule->frequency === 'daily'){
            ?>
            <td>Sim</td>
            <?php
        }elseif($occurrence->rule->frequency === 'once'){
            $dayOfWeek = $this->getDayOfWeek($occurrence->rule->startsOn);

            if($this->diasSemana[$dayOfWeek] === $field){
                ?>
                <td>Sim</td>
                <?php
            }else{
                ?>
                <td></td>
                <?php
            }
        }elseif($occurrence->rule->frequency === 'weekly'){
            $daysOn = array_keys((array)$occurrence->rule->day);
            $dayToPrint = array_search($field, $this->diasSemana);

            if(in_array($dayToPrint, $daysOn)){
                ?>
                <td>Sim</td>
                <?php
            }else{
                ?>
                <td></td>
                <?php
            }
        }

        return;
    }

    /**
     * Preenche os detalhes da ocorrência de acordo o $field enviado
     *
     * @param string $field    campo a ser preenchdio
     * @param obj $occurrence
     * @return void
     */
    protected function printOccurenceDetails($field, $occurrence){
        if($field === 'Horário Inicial'){
            ?>
                <td><?php echo $occurrence->rule->startsOn ?></td>
            <?php
        }elseif($field === 'Horário Final'){
            ?>
                <td><?php echo $occurrence->rule->endsAt ?></td>
            <?php
        }elseif($field === 'Data Inicial'){
            ?>
                <td><?php echo $occurrence->rule->startsOn ?></td>
            <?php
        }elseif($field === 'Data Final'){
            ?>
                <td><?php echo $occurrence->rule->until ?></td>
            <?php
        }elseif($field === 'Duração'){
            ?>
                <td><?php echo $occurrence->rule->duration ?></td>
            <?php
        }elseif($field === 'Frequência'){
            ?>
                <td><?php echo $this->translate[$occurrence->rule->frequency] ?></td>
            <?php
        }

        return;
    }

    /**
     * Seta o cabeçalho a ser impresso na tabela
     *
     * @param array $item
     * @return array
     */
    protected function setItemKeys($item){
        $itemKeys = array_keys($item);

        foreach($this->occurrenceDetails as $o){
            array_push($itemKeys, $o);
        }

        foreach($this->diasSemana as $d){
            array_push($itemKeys, $d);
        }

        return $itemKeys;
    }

    /**
     * Converte os caracteres para UTF-16 para
     * não haver quebra dos caracteres
     *
     * @param string $text
     * @return string
     */
    protected function convertToUTF16($text){
        return mb_convert_encoding($text,'utf-16','utf-8');
    }

    protected function printArrayTable($data){
    	$app = App::i();
    	$entity = $app->view->controller->entityClassName;
    	$label = $entity::getPropertiesLabels();
        $first = true; 
        if(count($data)){
            $keys = array_keys($data[0]);
        }
        ?>
        <table border="1">
        <?php foreach($data as $item):
            if($first){
                $first_item_keys = $this->setItemKeys($item);
            } 
            
            $item = json_decode(json_encode($item));
            ?>
            <?php if(isset($item->occurrences)) : //Occurrences to the end
                $occs = $item->occurrences; unset($item->occurrences); $item->occurrences = $occs; ?>
            <?php endif; ?>
            
            <?php 
            if($first): 
                $first=false;
            ?>
            <thead>
                <tr>
                    <?php foreach($first_item_keys as $k): ?><?php
                        if($k==='terms'){
                            $v = $item->$k;

                            if(property_exists($v, 'area')){ ?><th><?php echo $this->convertToUTF16($this->translate['area']); ?></th><?php }
                            if(property_exists($v, 'tag')){ ?><th><?php echo $this->convertToUTF16($this->translate['tag']); ?></th><?php }
                            if(property_exists($v, 'linguagem')){ ?><th><?php echo $this->convertToUTF16($this->translate['linguagem']); ?></th><?php }

                        }elseif(strpos($k,'@files')===0){
                            continue;
                        }elseif($k==='occurrences'){ ?>
                            <th><?php echo $this->convertToUTF16($this->translate['occurrences']); ?></th> 
                            <?php
                        }else{
                            if(in_array($k,['singleUrl','occurrencesReadable','spaces'])){
                                continue;
                            }
                            ?>
                            <th> 
                                <?php 
                                if(isset($label[$k]) && $label[$k]) {
                                    echo $this->convertToUTF16($label[$k]);
                                } else if(isset($this->translate[$k])){
                                    echo $this->convertToUTF16( $this->translate[$k]);
                                } else {
                                    echo $this->convertToUTF16($k);  
                                }
                                ?>
                            </th>
                        <?php
                        }
                    ?><?php endforeach; ?>
                    <th></th>
                </tr>

            </thead>
            <tbody>
            <?php endif; ?>
                <?php foreach($occs as $occ): ?>
                    <tr>
                        <?php foreach($first_item_keys as $k): $v = isset($item->$k) ? $item->$k : null;?>
                            <?php if($k==='terms'): ?>
                                <?php if(property_exists($v, 'area')): ?>
                                    <td><?php echo $this->convertToUTF16(implode(', ', $v->area)); ?></td>
                                <?php endif; ?>
                                <?php if(property_exists($v, 'tag')): ?>
                                    <td><?php echo $this->convertToUTF16(implode(', ', $v->tag)); ?></td>
                                <?php endif; ?>
                                <?php if(property_exists($v, 'linguagem')): ?>
                                    <td><?php echo $this->convertToUTF16(implode(', ', $v->linguagem)); ?></td>
                                <?php endif; ?> 
                            <?php elseif(strpos($k,'@files')===0):  continue; ?>
                            <?php elseif($k==='occurrences'): ?>
                                <td>
                                    <?php echo $this->convertToUTF16($occ->rule->description);?>,
                                    <a href="<?php echo $occ->space->singleUrl?>"><?php echo $this->convertToUTF16($occ->space->name);?></a>
                                    <?php if($occ->rule->price): ?>
                                        <?php echo $this->convertToUTF16($occ->rule->price);?> <br>
                                    <?php endif; ?>
                                </td>
                            <?php elseif($k==='project'):?>
                                <?php if(is_object($v)): ?>
                                    <td><a href="<?php echo $v->singleUrl?>"><?php echo $this->convertToUTF16($v->name);?></a></td>
                                <?php else: ?>
                                    <td></td>
                                <?php endif; ?>
                            <?php elseif(in_array($k, $this->diasSemana)): ?>
                                <?php $this->printDaysOfEvent($k, $occ); 
                                      continue;
                                ?>
                            <?php elseif(in_array($k, $this->occurrenceDetails)): ?>
                                <?php $this->printOccurenceDetails($k, $occ);
                                      continue;
                                ?>
                            <?php else:
                                if($k==='name' && !empty($item->singleUrl)){
                                    $v = '<a href="'.$item->singleUrl.'">'.$this->convertToUTF16($v).'</a>';
                                }elseif(in_array($k,['singleUrl','occurrencesReadable','spaces'])){
                                    continue;
                                }
                                ?>
                                <td>
                                    <?php
                                    if(is_bool($v)){
                                        echo $v ? 'true' : 'false';
                                    }elseif(is_object($v) && $k==='type'){
                                        echo $this->convertToUTF16($v->name);
                                    }elseif(is_string($v) || is_numeric($v)){
                                        echo $this->convertToUTF16($v);
                                    }elseif(is_object($v) && isset($v->date)){
                                        echo date_format(date_create($v->date),'Y-m-d H:i:s');
                                    }elseif(is_object($v) && isset($v->latitude) && isset($v->longitude) ){
                                        echo $v->latitude . ',' . $v->longitude;
                                    }elseif(is_array($v) || is_object($v)){
                                        if(is_array($v) && count($v) > 0 && !is_array($v[0]) && !is_object($v[0]) ) {
                                            echo implode(', ',$v);	
                                        } else {
                                            
                                            if(isset($v->name) && isset($v->singleUrl)){
                                                echo "<a href=\"$v->singleUrl\">$v->name</a>";
                                            } else {
                                                $this->printTable($v);
                                            }
                                        }
                                    }
                                    ?>
                                </td>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <td>
                            <?php
                            $vars = get_object_vars($item);
                            if(!empty($vars['@files:avatar.avatarMedium'])){
                                ?><img src="<?php echo $vars['@files:avatar.avatarMedium']->url; ?>" width="80"><?php
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?> <!-- end foreach occorencias -->
        <?php endforeach; ?> <!-- end foreach $item -->
        <?php if(!$first): ?>
            </tbody>
        <?php endif; ?>
        </table>
    <?php
    }

    protected function printOneItemTable($item){
        ?>
        <table>
            <?php foreach($item as $p => $v): ?>
            <tr>
                <th><?php echo $p ?></th>
                <td><?php
                    if(is_object($v) && $p==='type'){
                        echo $v->name;
                    }elseif($p==='tag' || $p==='area'){
                        echo implode(', ',$v);
                        
                    }elseif(is_object($v) || is_array($v)){
                        $this->printTable($v);
                    }elseif(is_string($v) || is_numeric($v)){
                        echo $v;
                    }
                    ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php
    }

    protected function _outputArray(array $data, $singular_object_name = 'Entity', $plural_object_name = 'Entities') {
        $uriExplode = explode('/',$_SERVER['REQUEST_URI']);
        if($data && key_exists(2,$uriExplode) ){
            $singular_object_name = $this->convertToUTF16($this->translate[$uriExplode[2]]);
            $plural_object_name = $singular_object_name.'s';
        }
        ?>
        <!DOCTYPE html>
        <html>
            <head>
                <title><?php echo sprintf(App::txts("%s $singular_object_name encontrado.", "%s $plural_object_name encontrados.", count($data)), count($data)) ?></title>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <style>
                    table table th {text-align: left; white-space: nowrap; }
                </style>
            </head>
            <body>
                <h1><?php

                echo sprintf(App::txts("%s $singular_object_name encontrado.", "%s $plural_object_name encontrados.", count($data)), count($data)) ?></h1>
                
                <h4><?php echo \MapasCulturais\i::__('Planilha gerada em: ') . \date("d/m/Y H:i") ?></h4>
                <?php $this->printTable($data) ?>
            </body>
        </html>
        <?php
    }

    function _outputItem($data, $object_name = 'entity') {
        var_dump($data);
    }

    protected function _outputError($data) {
        var_dump('ERROR', $data);
    }
}
