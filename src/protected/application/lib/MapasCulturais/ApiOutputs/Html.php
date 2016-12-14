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

        'tag' => 'Tags',
        'area' => 'Áreas',
        'linguagem' => 'Linguagens',

        'agent'=>'Agente',
        'space'=>'Espaço',
        'event'=>'Evento',
        'project'=>'Projeto',
        'seal'=>'Selo'
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

    protected function printArrayTable($data){
    	$app = App::i();
    	$entity = $app->view->controller->entityClassName;
    	$label = $entity::getPropertiesLabels();
        $first = true; 
        
        ?>
        <table border="1">
        <?php foreach($data as $item): ?>
            <?php
            $item = json_encode($item);
            $item = json_decode($item);
            
            ?>
            <?php if(isset($item->occurrences)) : //Occurrences to the end
                $occs = $item->occurrences; unset($item->occurrences); $item->occurrences = $occs; ?>
            <?php endif; ?>
            
            <?php 
            if($first): $first=false;
            ?>
            <thead>
                <tr>
                    <?php foreach($item as $k => $v): ?><?php
                        if($k==='terms'){
                            if(property_exists($v, 'area')){ ?><th><?php echo mb_convert_encoding($this->translate['area'],"HTML-ENTITIES","UTF-8"); ?></th><?php }
                            if(property_exists($v, 'tag')){ ?><th><?php echo mb_convert_encoding($this->translate['tag'],"HTML-ENTITIES","UTF-8"); ?></th><?php }
                            if(property_exists($v, 'linguagem')){ ?><th><?php echo mb_convert_encoding($this->translate['linguagem'],"HTML-ENTITIES","UTF-8"); ?></th><?php }

                        }elseif(strpos($k,'@files')===0){
                            continue;
                        }elseif($k==='occurrences'){ ?>
                            <th>
                                <table><thead><tr> <th><?php \MapasCulturais\i::_e("Quando");?></th> <th><?php \MapasCulturais\i::_e("Onde");?></th> <th><?php \MapasCulturais\i::_e("Quanto");?></th> </tr></thead></table>
                            </th>
                            <?php
                        }else{
                            if(in_array($k,['singleUrl','occurrencesReadable','spaces'])){
                                continue;
                            }
                            ?>
                            <th>
                            	<?php echo isset($label[$k])? $label[$k]: $k ;?>
                            </th>
                        <?php
                        }
                    ?><?php endforeach; ?>
                    <th></th>
                </tr>

            </thead>
            <tbody>
            <?php endif; ?>
                <tr>
                    <?php foreach($item as $k => $v):  ?>
                        <?php if($k==='terms'): ?>
                            <?php if(property_exists($v, 'area')): ?>
                                <td><?php echo mb_convert_encoding(implode(', ', $v->area),"HTML-ENTITIES","UTF-8"); ?></td>
                            <?php endif; ?>
                            <?php if(property_exists($v, 'tag')): ?>
                                <td><?php echo mb_convert_encoding(implode(', ', $v->tag),"HTML-ENTITIES","UTF-8"); ?></td>
                            <?php endif; ?>
                            <?php if(property_exists($v, 'linguagem')): ?>
                                <td><?php echo mb_convert_encoding(implode(', ', $v->linguagem),"HTML-ENTITIES","UTF-8"); ?></td>
                            <?php endif; ?>
                        <?php elseif(strpos($k,'@files')===0):  continue; ?>
                        <?php elseif($k==='occurrences'): ?>
                            <td>
                                <table>
                                    <?php foreach($v as $occ): ?>
                                        <tr>
                                            <td><?php echo mb_convert_encoding($occ->rule->description,"HTML-ENTITIES","UTF-8");?></td>
                                            <td><a href="<?php echo $occ->space->singleUrl?>"><?php echo mb_convert_encoding($occ->space->name,"HTML-ENTITIES","UTF-8");?></a></td>
                                            <td><?php echo mb_convert_encoding($occ->rule->price,"HTML-ENTITIES","UTF-8");?></td>
                                        </tr>
                                    <?php  endforeach; ?>
                                </table>
                            </td>
                        <?php elseif($k==='project'): ?>
                            <td><a href="<?php echo $v->singleUrl?>"><?php echo mb_convert_encoding($v->name,"HTML-ENTITIES","UTF-8");?></a></td>
                        <?php else:
                            if($k==='name' && !empty($item->singleUrl)){
                                $v = '<a href="'.$item->singleUrl.'">'.mb_convert_encoding($v,"HTML-ENTITIES","UTF-8").'</a>';
                            }elseif(in_array($k,['singleUrl','occurrencesReadable','spaces'])){
                                continue;
                            }
                            ?>
                            <td>
                                <?php
                                
                                if(is_object($v) && $k==='type'){
                                    echo mb_convert_encoding($v->name,"HTML-ENTITIES","UTF-8");
                                }elseif(is_string($v) || is_numeric($v)){
                                    echo mb_convert_encoding($v,"HTML-ENTITIES","UTF-8");
                                }elseif(is_object($v) && isset($v->date)){
									echo date_format(date_create($v->date),'Y-m-d H:i:s');
                                }elseif(is_object($v) && isset($v->latitude) && isset($v->longitude) ){
									echo $v->latitude . ',' . $v->longitude;
                                }elseif(is_array($v) || is_object($v)){
                                    if(is_array($v) && count($v) > 0 && !is_array($v[0]) && !is_object($v[0]) ) {
                                    	echo implode(', ',$v);	
                                    } else {
                                    	$this->printTable($v);
	                                }
                                }else{
                                    //var_dump($v);
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
        <?php endforeach; ?>
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
                    }else{
                        //var_dump($v);
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
            $singular_object_name = mb_convert_encoding($this->translate[$uriExplode[2]],"HTML-ENTITIES","UTF-8");
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
