<?php
use MapasCulturais\Entities\Registration as R;
use MapasCulturais\Entities\Agent;
use MapasCulturais\i;

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
        <?php foreach($entity->sentRegistrations as $r): ?>
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
                    $_field_name = $field["field_name"];
                    $_field_val = $r->$_field_name;

                    echo "<td>";
                        echo (is_array($_field_val)) ? implode(", ", $_field_val) : $_field_val;
                    echo "</td>";

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
                ?>

                    <?php if($agent): ?>
                        <td><a href="<?php echo $agent->singleUrl; ?>" target="_blank"><?php echo $r->agentsData[$def->agentRelationGroupName]['name'];?></a></td>
                        
                        <td><?php echo implode(', ', $agent->terms['area']); ?></td>

                        <?php
                        foreach($_properties as $prop):
                            if($prop === 'name') continue;
                        $val = isset($r->agentsData[$def->agentRelationGroupName][$prop]) ? $r->agentsData[$def->agentRelationGroupName][$prop] : '';
                        ?>
                        <td><?php echo $prop === 'location' ? "{$val['latitude']},{$val['longitude']}" : $val ?></td>

                        <?php endforeach; ?>

                    <?php else: ?>
                        <?php echo str_repeat('<td></td>', count($_properties)) ?>
                    <?php endif; ?>

                <?php endforeach ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>