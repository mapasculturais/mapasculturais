<?php
namespace MapasCulturais\ApiOutputs;
use \MapasCulturais\App;
use MapasCulturais;



class Html extends \MapasCulturais\ApiOutput{

    protected $occurrenceDetails = array();
    protected $diasSemana = array();
    protected $spaceDetails = array();

    public function __construct(){
        array_push($this->occurrenceDetails, \MapasCulturais\i::__('Data Inicial'));
        array_push($this->occurrenceDetails, \MapasCulturais\i::__('Data Final'));
        array_push($this->occurrenceDetails, \MapasCulturais\i::__('Duração'));
        array_push($this->occurrenceDetails, \MapasCulturais\i::__('Frequência'));
        array_push($this->occurrenceDetails, \MapasCulturais\i::__('Horário Inicial'));
        array_push($this->occurrenceDetails, \MapasCulturais\i::__('Horário Final'));

        array_push($this->diasSemana, \MapasCulturais\i::__('Repete-Domingo'));
        array_push($this->diasSemana, \MapasCulturais\i::__('Repete-Segunda'));
        array_push($this->diasSemana, \MapasCulturais\i::__('Repete-Terça'));
        array_push($this->diasSemana, \MapasCulturais\i::__('Repete-Quarta'));
        array_push($this->diasSemana, \MapasCulturais\i::__('Repete-Quinta'));
        array_push($this->diasSemana, \MapasCulturais\i::__('Repete-Sexta'));
        array_push($this->diasSemana, \MapasCulturais\i::__('Repete-Sábado'));

        array_push($this->spaceDetails, \MapasCulturais\i::__('Espaço'));
        array_push($this->spaceDetails, \MapasCulturais\i::__('CEP'));
        array_push($this->spaceDetails, \MapasCulturais\i::__('Logradouro'));
        array_push($this->spaceDetails, \MapasCulturais\i::__('Número'));
        array_push($this->spaceDetails, \MapasCulturais\i::__('Complemento'));
        array_push($this->spaceDetails, \MapasCulturais\i::__('Bairro'));
        array_push($this->spaceDetails, \MapasCulturais\i::__('Município'));
        array_push($this->spaceDetails, \MapasCulturais\i::__('Estado'));
    }

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
     * Traduz os textos da tabela de acordo com o padrão
     * de internacionalização configurado
     *
     * @param string $text
     * @return void
     */
    protected function translate($text){
        $translated = '';

        switch($text){
            case 'id':
                $translated = \MapasCulturais\i::__('Id');
            break;
            case 'name':
                $translated = \MapasCulturais\i::__('Nome');
            break;
            case 'singleUrl':
                $translated = \MapasCulturais\i::__('Link');
            break;
            case 'type':
                $translated = \MapasCulturais\i::__('Tipo');
            break;
            case 'shortDescription':
                $translated = \MapasCulturais\i::__('Descrição Curta');
            break;
            case 'terms':
                $translated = \MapasCulturais\i::__('Termos');
            break;
            case 'endereco':
                $translated = \MapasCulturais\i::__('Endereço');
            break;
            case 'classificacaoEtaria':
                $translated = \MapasCulturais\i::__('Classificação Etária');
            break;
            case 'project':
                $translated = \MapasCulturais\i::__('Projeto');
            break;
            case 'occurrences':
                $translated = \MapasCulturais\i::__('Descrição Legível do Horário');
            break;
            case 'tag':
                $translated = \MapasCulturais\i::__('Tags');
            break;
            case 'area':
                $translated = \MapasCulturais\i::__('Áreas');
            break;
            case 'linguagem':
                $translated = \MapasCulturais\i::__('Linguagens');
            break;
            case 'weekly':
                $translated = \MapasCulturais\i::__('Semanal');
            break;
            case 'once':
                $translated = \MapasCulturais\i::__('Uma vez');
            break;
            case 'daily':
                $translated = \MapasCulturais\i::__('Diariamente');
            break;
            case 'agent':
                $translated = \MapasCulturais\i::__('Agente');
            break;
            case 'space':
                $translated = \MapasCulturais\i::__('Espaço');
            break;
            case 'event':
                $translated = \MapasCulturais\i::__('Evento');
            break;
            case 'project':
                $translated = \MapasCulturais\i::__('Projeto');
            break;
            case 'seal':
                $translated = \MapasCulturais\i::__('Selo');
            break;
            case 'seals':
                $translated = \MapasCulturais\i::__('Selos');
            break;
            case 'owner':
                $translated = \MapasCulturais\i::__('Publicado por');
            break;
            case 'parent':
                $translated = \MapasCulturais\i::__('Entidade pai');
            break;
            case 'createTimestamp':
                $translated = \MapasCulturais\i::__('Data de Criação');
            break;
            case 'isVerified':
                $translated = \MapasCulturais\i::__('Verificado');
            break;
            case 'verifiedSeals':
                $translated = \MapasCulturais\i::__('Selos Verificadores');
            break;
            case 'isVerificationSeal':
                $translated = \MapasCulturais\i::__('Selo Verificador');
            break;
        }

        return $translated;
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
            
            $inicio = strtotime($occurrence->rule->startsOn);
            $fim = strtotime($occurrence->rule->until);
            
            /**
             * Em caso de repetição diária, iteramos em todos os dias
             * e checamos se o dia da semana atual ($field) existe em qq uma das repetições.
             * Se sim, break. Ou seja, Esse loop vai iterar no máximo 7 vezes.
             */ 
            
            echo '<td>';
            while ($inicio <= $fim) {
                
                $dayOfWeek = \date('w', $inicio);

                if($this->diasSemana[$dayOfWeek] === $field){ 
                    
                    echo \MapasCulturais\i::__('Sim');
                    break;
                }
                  
                $inicio += 24 * 60 * 60;
                
            }
            echo '</td>';

        }elseif($occurrence->rule->frequency === 'once'){
            $dayOfWeek = $this->getDayOfWeek($occurrence->rule->startsOn);

            if($this->diasSemana[$dayOfWeek] === $field){
                ?>
                <td><?php \MapasCulturais\i::_e('Sim'); ?></td>
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
                <td><?php \MapasCulturais\i::_e('Sim'); ?></td>
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
     * @param string $field    campo a ser preenchido
     * @param obj $occurrence
     * @return void
     */
    protected function printOccurenceDetails($field, $occurrence){
        if($field === 'Horário Inicial'){
            ?>
                <td><?php echo $occurrence->rule->startsOn ? $occurrence->rule->startsAt : ''; ?></td>
            <?php
        }elseif($field === 'Horário Final'){
            ?>
                <td><?php echo $occurrence->rule->endsAt ? $occurrence->rule->endsAt : ''; ?></td>
            <?php
        }elseif($field === 'Data Inicial'){
            ?>
                <td><?php echo $occurrence->rule->startsOn ? $occurrence->rule->startsOn : ''; ?></td>
            <?php
        }elseif($field === 'Data Final'){
            ?>
                <td><?php echo $occurrence->rule->until ? $occurrence->rule->until : ''; ?></td>
            <?php
        }elseif($field === 'Duração'){
            ?>
                <td><?php echo $occurrence->rule->duration ? $occurrence->rule->duration : ''; ?></td>
            <?php
        }elseif($field === 'Frequência'){
            ?>
                <td><?php echo $occurrence->rule->duration ? $this->translate($occurrence->rule->frequency) : ''; ?></td>
            <?php
        }

        return;
    }

    /**
     * Preenche as informações do espaço da ocorrência de acordo o $field enviado
     *
     * @param string $field    campo a ser preenchido
     * @param obj $occurrence
     * @return void
     */
    protected function printSpaceDetails($field, $occurrence){
        if($field === 'Espaço'){
            ?>
                <td><a href="<?php echo $occurrence->space->singleUrl ? $occurrence->space->singleUrl : ''; ?>"><?php echo $occurrence->space->name ? $occurrence->space->name : ''; ?></a></td>
            <?php
        }elseif($field === 'CEP'){
            ?>
                <td><?php echo $occurrence->space->En_CEP ? $occurrence->space->En_CEP : ''; ?></td>
            <?php
        }elseif($field === 'Logradouro'){
            ?>
                <td><?php echo $occurrence->space->En_Nome_Logradouro ? $this->convertToUTF16($occurrence->space->En_Nome_Logradouro) : ''; ?></td>
            <?php
        }elseif($field === 'Número'){
            ?>
                <td><?php echo $occurrence->space->En_Num ? $occurrence->space->En_Num : ''; ?></td>
            <?php
        }elseif($field === 'Complemento'){
            ?>
                <td><?php echo $occurrence->space->En_Complemento ? $this->convertToUTF16($occurrence->space->En_Complemento) : ''; ?></td>
            <?php
        }elseif($field === 'Bairro'){
            ?>
                <td><?php echo $occurrence->space->En_Bairro ? $this->convertToUTF16($occurrence->space->En_Bairro) : ''; ?></td>
            <?php
        }elseif($field === 'Município'){
            ?>
                <td><?php echo $occurrence->space->En_Municipio ? $this->convertToUTF16($occurrence->space->En_Municipio) : ''; ?></td>
            <?php
        }elseif($field === 'Estado'){
            ?>
                <td><?php echo $occurrence->space->En_Estado ? $occurrence->space->En_Estado : ''; ?></td>
            <?php
        }

        return;
    }

    /**
     * Seta o cabeçalho a ser impresso na tabela de eventos
     *
     * @param array $item
     * @return array
     */
    protected function setEventKeys($item){
        $itemKeys = array_keys($item);

        foreach($this->occurrenceDetails as $o){
            array_push($itemKeys, $o);
        }

        foreach($this->diasSemana as $d){
            array_push($itemKeys, $d);
        }

        foreach($this->spaceDetails as $s){
            array_push($itemKeys, $s);
        }

        return $itemKeys;
    }

    /**
     * Converte os caracteres para UTF-16 para
     * não haver quebra dos caracteres
     * @todo Verificar possibilidade de remover a função
     * @param string $text
     * @return string
     */
    protected function convertToUTF16($text){
        return $text;
    }

    /**
     * Checa se a requisição foi feita a partir da agenda da single do espaço
     * e retorna seu id. Se não foi, retorna falso.
     *
     * @param array $app
     * @return int | false
     */
    protected function getSpaceSingleAgendaRequest(){
        $app = App::i();
        if(in_array('space', array_keys($app->view->controller->getData))){
            $spaceId = $app->view->controller->getData['space'];
            preg_match_all('!\d+!', $spaceId, $matches);
            
            return implode($matches[0]);
        }
        
        return false;
    }

    /**
     * Filtra as ocorrências apenas pelas quais ocorrem no espaço selecionado
     * 
     * Isto é útil para quando estamos filtrando eventos por um espaço e não queremos que apareçam suas ocorrências em outros espaços
     * 
     * @param string $space_id id do espaço
     * @param array $data array com os eventos
     * @return array
     */
    protected function filterOccurrencesBySpace($space_id, $data){
        //iterador eventos
        for($i=0; $i<=count($data) -1; $i++){
            //iterador ocorrencias
            $counter = count($data[$i]['occurrences']) -1;

            for($j=0; $j<=$counter; $j++)
                if($data[$i]['occurrences'][$j]['rule']->spaceId !== $space_id){
                    unset($data[$i]['occurrences'][$j]);
                }
        }

        return $data;
    }

    protected function printArrayTable($data){
    	$app = App::i();
    	$entity = $app->view->controller->entityClassName;
    	$label = $entity::getPropertiesLabels();
        $first = true; 
        $isRequestFromAgenda = $this->getSpaceSingleAgendaRequest();

        if($isRequestFromAgenda){
            $data = $this->filterOccurrencesBySpace($isRequestFromAgenda, $data);
        }
        
        ?>
        <table border="1">
        <?php foreach($data as $item):
            $item = (array) $item;
            unset($item['rules']);
            unset($item['@entityType']);

            if($first){
                if($entity === 'MapasCulturais\Entities\Event' && isset($item['ocurrences'])){
                    $first_item_keys = $this->setEventKeys($item);
                }else{
                    $first_item_keys = array_keys($item);
                }
            } 
            
            $item = json_decode(json_encode($item));
            $occs = [];
            ?>
            <?php if(isset($item->occurrences)) : //Occurrences to the end
                $occs = $item->occurrences; 
                unset($item->occurrences); 
                $item->occurrences = $occs; 
            ?>
            <?php endif; ?>
            
            <?php 
            if($first): 
                $first=false;
            ?>
            <thead>
                <tr>
                    <?php foreach($first_item_keys as $k):
                        if($k==='terms'){
                            $v = $item->$k;
                                            
                            foreach ($v as $term => $item1) {
                                if($term == 'area' || $term == 'tag' || $term == 'linguagem')
                                    continue;
                                
                                $name_taxo = App::i()->getRegisteredTaxonomyBySlug($term)->description;
                                echo "<th>" . $this->convertToUTF16($name_taxo) . "</th>";
                            }

                            if(property_exists($v, 'area')){ ?><th><?php echo $this->convertToUTF16($this->translate('area')); ?></th><?php }
                            if(property_exists($v, 'tag')){ ?><th><?php echo $this->convertToUTF16($this->translate('tag')); ?></th><?php }
                            if(property_exists($v, 'linguagem')){ ?><th><?php echo $this->convertToUTF16($this->translate('linguagem')); ?></th><?php }

                        } elseif(strpos($k,'@files')===0) {
                            continue;
                        } elseif($k==='occurrences') { ?>
                            <th><?php echo $this->convertToUTF16($this->translate('occurrences')); ?></th> 
                            <?php
                        } else {
                            if (in_array($k,['singleUrl','occurrencesReadable','spaces'])) {
                                continue;
                            }
                            ?>
                            <th> 
                                <?php 
                                if(isset($label[$k]) && $label[$k]) {
                                    echo $this->convertToUTF16($label[$k]);
                                } else if(!empty($this->translate($k))){
                                    echo $this->convertToUTF16( $this->translate($k));
                                } else {
                                    echo $this->convertToUTF16($k);  
                                }
                                ?>
                            </th>
                        <?php
                        }
                        endforeach;

                        // Permite acrescentar novos headers (th) no output html/excel
                        $app->applyHookBoundTo($this, 'API.(space).result.extra-header-fields');
                        ?>
                </tr>
            </thead>
            <tbody>
            <?php endif; ?>
                <?php if($entity === 'MapasCulturais\Entities\Event' && $occs){
                    $this->printEventsBodyTable($occs, $first_item_keys, $item);
                }else{
                    $this->printBodyTable($first_item_keys, $item);
                }
                ?>
        <?php endforeach; ?> <!-- end foreach $item -->
        <?php if(!$first): ?>
            </tbody>
        <?php endif; ?>
        </table>
    <?php
    }

    protected function printOneItemTable($item){
        $item = (array)$item;
        if(count($item) === 3){
            unset($item['id'], $item['@entityType']);
            echo implode("", $item);
            return;
        }

        $item = (object) $item;

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

    protected function _outputArray(array $data, $singular_object_name = 'Entity', $plural_object_name = 'Entidades') {
        $uriExplode = explode('/',$_SERVER['REQUEST_URI']);
        if($data && key_exists(2,$uriExplode) ){
            $singular_object_name = $this->convertToUTF16($this->translate($uriExplode[2]));
            $plural_object_name = $singular_object_name.'s';
        }
        ?>
        <!DOCTYPE html>
        <html>
            <head>
                <?php if(count($data) === 1):?>
                    <title><?php echo sprintf("%s $singular_object_name encontrado.", count($data)) ?></title>
                <?php else:?>
                    <title><?php echo sprintf("%s $plural_object_name encontrados.", count($data)) ?></title>
                <?php endif?>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <style>
                    table table th {text-align: left; white-space: nowrap; }
                </style>
            </head>
            <body>
                <h1>
                    <?php
                        if(count($data) === 1){
                            echo sprintf("%s $singular_object_name encontrado.", count($data));
                        }else {
                            echo sprintf("%s $plural_object_name encontrados.", count($data));
                        }
                    ?>
                </h1>
                
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

    /**
     * Corpo da tabela gerado na exportação dos eventos
     *
     * @param obj $occs ocorrências
     * @param array $first_item_keys cabeçalhos da tabela
     * @param obj $item
     * @return void
     */
    protected function printEventsBodyTable($occs, $first_item_keys, $item){
        foreach($occs as $occ): ?>
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
                                    <?php echo $this->convertToUTF16($occ->rule->description);?>
                                </td>
                            <?php elseif($k==='project'):?>
                                <?php if(is_object($v)): ?>
                                    <td><a href="<?php echo $v->singleUrl?>"><?php echo $this->convertToUTF16($v->name);?></a></td>
                                <?php else: ?>
                                    <td></td>
                                <?php endif; ?>
                            <?php elseif($k==='preco'): ?>
                                <td><?php echo $occ->rule->price ?></td>
                            <?php elseif(in_array($k, $this->diasSemana)): ?>
                                <?php $this->printDaysOfEvent($k, $occ); 
                                      continue;
                                ?>
                            <?php elseif(in_array($k, $this->occurrenceDetails)): ?>
                                <?php $this->printOccurenceDetails($k, $occ);
                                      continue;
                                ?>
                            <?php elseif(in_array($k, $this->spaceDetails)): ?>
                                <?php $this->printSpaceDetails($k, $occ);
                                      continue;
                                ?>
                            <?php elseif($k==='name' && !empty($item->singleUrl)): ?>
                                <td><a href="<?php echo $item->singleUrl; ?>"><?php echo $v; ?></a></td>
                            <?php else:
                                if(in_array($k,['singleUrl','occurrencesReadable','spaces'])){
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
                                                echo "<a  rel='noopener noreferrer' href=\"$v->singleUrl\">$v->name</a>";
                                            } else if(isset($v->number) && isset($v->singleUrl)){
                                                echo "<a href=\"$v- rel='noopener noreferrer'>singleUrl\">$v->number</a>";
                                            } else {
                                                $this->printTable($v);
                                            }
                                        }
                                    }
                                    ?>
                                </td>
                            <?php endif; ?>
                        <?php endforeach; ?> <!-- end foreach first_item_keys -->
                        <td>
                            <?php
                            $vars = get_object_vars($item);
                            if(!empty($vars['@files:avatar.avatarMedium'])){
                                ?><img src="<?php echo $vars['@files:avatar.avatarMedium']->url; ?>" width="80"><?php
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php return; 
    }

    /**
     * Corpo da tabela gerado para espaço e agente
     *
     * @param array $first_item_keys cabeçalhos da tabela
     * @param obj $item
     * @return void
     */
    protected function printBodyTable($first_item_keys, $item){
        ?>
        <tr>
                    <?php foreach($first_item_keys as $k): $v = isset($item->$k) ? $item->$k : null;?>
                        <?php 
                        
                            if($k === 'terms'){
                            foreach ($item->terms as $term => $item1) {
                                if($term == 'area' || $term == 'tag' || $term == 'linguagem')
                                    continue;
                                echo "<td>" . $this->convertToUTF16(implode(", ",$item1)) . "</td>";
                            }
                        }
                        ?>
                        <?php if($k==='terms'): ?>
                            <?php if(property_exists($v, 'area')): ?>
                                <td><?php echo htmlentities(implode(', ', $v->area)); ?></td>
                            <?php endif; ?>
                            <?php if(property_exists($v, 'tag')): ?>
                                <td><?php echo htmlentities(implode(', ', $v->tag)); ?></td>
                            <?php endif; ?>
                            <?php if(property_exists($v, 'linguagem')): ?>
                                <td><?php echo htmlentities(implode(', ', $v->linguagem)); ?></td>
                            <?php endif; ?> 
                        <?php elseif(strpos($k,'@files')===0):  continue; ?>
                        <?php elseif($k==='occurrences'): ?>
                            <td>
                                <?php foreach($v as $occ): $occ->rule = $occ->rule;?>
                                    <?php echo htmlentities($occ->rule->description);?>,
                                    <a href="<?php echo $occ->space->singleUrl?>"><?php echo htmlentities($occ->space->name);?></a>
                                    <?php if($occ->rule->price): ?>
                                        <?php echo htmlentities($occ->rule->price);?> <br>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </td>
                        <?php elseif($k==='project'):?>
                            <?php if(is_object($v)): ?>
                                <td><a href="<?php echo $v->singleUrl?>"><?php echo htmlentities($v->name);?></a></td>
                            <?php else: ?>
                                <td></td>
                            <?php endif; ?>
                        <?php else:
                            if($k==='name' && !empty($item->singleUrl)){
                                $v = '<a rel="noopener noreferrer" href="'.$item->singleUrl.'">'.htmlentities($v).'</a>';
                            }else if($k==='number' && !empty($item->singleUrl)){
                                    $v = '<a rel="noopener noreferrer" href="'.$item->singleUrl.'">'.htmlentities($v).'</a>';
                            }else if(in_array($k,['singleUrl','occurrencesReadable','spaces'])){
                                continue;
                            }
                            ?>
                            <td>
                                <?php
                                if(is_bool($v)){
                                    echo $v ? 'true' : 'false';
                                }elseif(is_object($v) && $k==='type'){
                                    echo htmlentities($v->name);
                                }elseif(is_string($v) || is_numeric($v)){
                                    echo htmlentities($v);
                                }elseif(is_object($v) && isset($v->date)){
									echo date_format(date_create($v->date),'Y-m-d H:i:s');
                                }elseif(is_object($v) && isset($v->latitude) && isset($v->longitude) ){
									echo $v->latitude . ',' . $v->longitude;
                                }elseif(is_array($v) || is_object($v)){
                                    if(is_array($v) && count($v) > 0 && !is_array($v[0]) && !is_object($v[0]) ) {
                                    	echo implode(', ',$v);	
                                    } else {
                                        
                                        if(isset($v->name) && isset($v->singleUrl)){
                                            echo "<a href=\"$v->singleUrl\" rel='noopener noreferrer'>$v->name</a>";
                                        } else if(isset($v->number) && isset($v->singleUrl)){
                                            echo "<a href=\"$v->singleUrl\" rel='noopener noreferrer'>$v->number</a>";
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
<?php return;
    }
}