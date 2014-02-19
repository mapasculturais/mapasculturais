<?php
namespace MapasCulturais\ApiOutputs;
use \MapasCulturais\App;

class Html extends \MapasCulturais\ApiOutput{
    protected function getContentType() {
        return 'text/html';
    }

    protected function _outputArray(array $data, $singular_object_name = 'Entity', $plural_object_name = 'Entities') {
        $first = true;

        ?>
<!DOCTYPE html>
<html>
    <head>
        <title><?php echo sprintf(App::txts("%s $singular_object_name found.", "%s $plural_object_name found.", count($data)), count($data)) ?></title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>

<table border="1">
    <caption><?php echo sprintf(App::txts("%s $singular_object_name found.", "%s $plural_object_name found.", count($data)), count($data)) ?></caption>
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
            <td><?php echo $v ?></td>
            <?php endforeach; ?>
        </tr>
<?php endforeach; ?>
<?php if(!$first): ?>
    </tbody>
<?php endif; ?>
</table>

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
