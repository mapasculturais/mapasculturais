<?php
namespace MapasCulturais\ApiOutputs;
use \MapasCulturais\App;

class Html extends \MapasCulturais\ApiOutput{
    protected function getContentType() {
        return 'text/html';
    }
    
    protected function printTable($data){
        $data = json_decode(json_encode($data));
        if(is_array($data))
            $this->printArrayTable($data);
        elseif(is_object($data))
            $this->printOneItemTable($data);
        else
            return;
    }
    
    protected function printArrayTable($data){
        $first = true;
        
        ?>
<table border="1">
<?php foreach($data as $item): ?>
    <?php if($first): $first=false;?>
    <thead>
        <tr>
            <?php foreach($item as $k => $v): ?>
            <th><?php echo $k ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
    <?php endif; ?>
        <tr>
            <?php foreach($item as $k => $v): ?>
            <td><?php 
            if(is_string($v) || is_numeric($v)){            
                echo $v;
            }elseif(is_array($v) || is_object($v)){
                $this->printTable($v);
                
            }else{
                var_dump($v);
            }
            ?></td>
            <?php endforeach; ?>
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
            if(is_object($v) || is_array($v)) 
                $this->printTable($v);
            elseif(is_string($v) || is_numeric($v))
                echo $v;
            else
                var_dump($v);
            ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php
    }
    protected function _outputArray(array $data, $singular_object_name = 'Entity', $plural_object_name = 'Entities') {
        ?>
<!DOCTYPE html>
<html>
    <head>
        <title><?php echo sprintf(App::txts("%s $singular_object_name found.", "%s $plural_object_name found.", count($data)), count($data)) ?></title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            table table th {text-align: left; white-space: nowrap; }
        </style>
    </head>
    <body>
        <h1><?php echo sprintf(App::txts("%s $singular_object_name found.", "%s $plural_object_name found.", count($data)), count($data)) ?></h1>
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
