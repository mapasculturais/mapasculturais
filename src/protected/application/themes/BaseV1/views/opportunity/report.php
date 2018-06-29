<?php
use MapasCulturais\Entities\Registration as R;
use MapasCulturais\Entities\Agent;
use MapasCulturais\i;

/**
 * Return value property of Agent
 * If registration status is DRAFT get property by agent else get property by registartion agent metadata
 * @param $registration
 * @param $agent
 * @param $agentData
 * @param $prop
 * @return string
 */
function getAgentValue($registration, $agent, $agentData, $prop) {
    $value =  (isset($agentData[$prop])) ? $agentData[$prop] : '';
    if($registration->status == R::STATUS_DRAFT){
        $value = $agent->$prop;
    }
    if ($prop === 'location' && is_array($value)) {
        $value = "{$value['latitude']},{$value['longitude']}";
    }
    return $value;
}

function echoStatus($registration) {
    switch ($registration->status){
        case R::STATUS_APPROVED:
            i::_e('selecionada');
            break;

        case R::STATUS_NOTAPPROVED:
            i::_e('não selecionada');
            break;

        case R::STATUS_WAITLIST:
            i::_e('suplente');
            break;

        case R::STATUS_INVALID:
            i::_e('inválida');
            break;

        case R::STATUS_SENT:
            i::_e('pendente');
            break;

        case R::STATUS_DRAFT:
            i::_e('rascunho');
            break;
    }
}

function showIfField($hasField, $showField) {
    if($hasField)
        echo "<th>" . $showField . "</th>";
}

$_properties = $app->config['registration.propertiesToExport'];
$custom_fields = [];
foreach($entity->registrationFieldConfigurations as $field) :
    $custom_fields[$field->displayOrder] = [
        'title' => $field->title,
        'field_name' => $field->getFieldName()
    ];
endforeach;

ksort($custom_fields);
?>
<style>
    tbody td, table th{
        text-align: left !important;
        border:1px solid black !important;
    }
</style>

<table>
    <thead>
        <tr>
            <th> <?php i::_e("Número") ?> </th>

            <?php showIfField($entity->projectName, i::__("Nome do projeto")); ?>

            <th> <?php i::_e("Avaliação") ?> </th>
            <th><?php i::_e("Status") ?></th>

            <?php showIfField($entity->registrationCategories, $entity->registrationCategTitle); ?>

            <?php
            foreach($custom_fields as $field)
                echo "<th>" . $field['title'] . "</th>";
            ?>

            <th><?php i::_e('Anexos') ?></th>
            <?php foreach($entity->getUsedAgentRelations() as $def): ?>
                <th><?php echo $def->label; ?></th>
                
                <th><?php echo $def->label; ?> - <?php i::_e("Área de Atuação") ?></th>
                
                <?php foreach($_properties as $prop): if($prop === 'name') continue; ?>
                    <th><?php echo $def->label; ?> - <?php echo Agent::getPropertyLabel($prop); ?></th>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach($registrationsList as $r): ?>
            <tr>
                <td><a href="<?php echo $r->singleUrl; ?>" target="_blank"><?php echo $r->number; ?></a></td>
                <?php if($entity->projectName): ?>
                    <td><?php echo $r->projectName ?></td>
                <?php endif; ?>
                <td><?php echo $r->getEvaluationResultString(); ?></td>
                <td><?php echoStatus($r); ?></td>

                <?php showIfField($entity->registrationCategories, $r->category); ?>

                <?php
                foreach($custom_fields as $field):
                    $_field_val = (isset($field["field_name"])) ? $r->{$field["field_name"]} : "";

                    echo "<th>";
                        echo (is_array($_field_val)) ? implode(", ", $_field_val) : $_field_val;
                    echo "</th>";

                    endforeach;
                ?>

                <td>
                    <?php if(key_exists('zipArchive', $r->files)): ?>
                        <a href="<?php echo $r->files['zipArchive']->url; ?>"><?php i::_e("zip");?></a>
                     <?php endif; ?>
                </td>

                <?php
                foreach($r->_getDefinitionsWithAgents() as $def):
                    if($def->use == 'dontUse') continue;
                    $agent = $def->agent;
                    $agentData = (!empty($r->agentsData) && isset($r->agentsData[$def->agentRelationGroupName])) ? $r->agentsData[$def->agentRelationGroupName] : [] ;
                ?>

                    <?php if($agent): ?>

                        <td><a href="<?php echo $agent->singleUrl; ?>" target="_blank"><?php echo getAgentValue($r,$agent,$agentData,'name');;?></a></td>
                        
                        <td><?php echo implode(', ', $agent->terms['area']); ?></td>

                        <?php
                        foreach($_properties as $prop):
                            if($prop === 'name') continue;
                            $val = getAgentValue($r,$agent,$agentData,$prop);
                        ?>
                        <td><?php echo $val; ?></td>

                        <?php endforeach; ?>

                    <?php else: ?>
                        <?php echo str_repeat('<td></td>', count($_properties)) ?>
                    <?php endif; ?>

                <?php endforeach ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>