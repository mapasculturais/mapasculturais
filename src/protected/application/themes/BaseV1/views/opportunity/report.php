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
            <th><?php i::_e("Inscrição - Data de envio") ?></th>
            <th><?php i::_e("Inscrição - Hora de envio") ?></th>
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
                <?php $dataHoraEnvio = $r->sentTimestamp; ?>
                <td><?php echo (!is_null($dataHoraEnvio))? $dataHoraEnvio->format('d-m-Y') : '';?></td>
                <td><?php echo (!is_null($dataHoraEnvio))? $dataHoraEnvio->format('H:i:s'): '';?></td>

                <?php if($entity->registrationCategories): ?>
                <td><?php echo $r->category ?></td>
                <?php endif; ?>

                <?php
                foreach($custom_fields as $field) {
                    $_field_val = (isset($field["field_name"])) ? $r->{$field["field_name"]} : "";
                    echo "<td>";
                    echo (is_array($_field_val)) ? implode(", ", $_field_val) : $_field_val;
                    echo "</td>";                
                }
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
                    $agentsData = $r->agentsData;
                    $agentsDataGroup = [];
                    if(!empty($agent) && !empty($agentsData)){
                        $agentsDataGroup = (isset($r->agentsData[$def->agentRelationGroupName])) ? $r->agentsData[$def->agentRelationGroupName] : [];
                    }
                ?>

                    <?php if($agent): ?>
                        <td><a href="<?php echo $agent->singleUrl; ?>" target="_blank"><?php echo (isset($agentsDataGroup['name']))? $agentsDataGroup['name'] : 'Agente';?></a></td>
                        
                        <td><?php echo implode(', ', $agent->terms['area']); ?></td>

                        <?php
                        foreach($_properties as $prop):
                            if($prop === 'name') continue;
                            $val = isset($agentsDataGroup[$prop]) ? $agentsDataGroup[$prop] : '';
                        ?>
                        <td>
                            <?php
                                if ($prop === 'location')
                                    echo (isset($val['latitude']) && isset($val['longitude'])) ? "{$val['latitude']},{$val['longitude']}" : '';
                                else
                                    echo $val;
                            ?>
                        </td>

                        <?php endforeach; ?>

                    <?php else: ?>
                        <?php 
                            // total de propriedades + 1 coluna que corresponde a $agent->terms['area']
                            echo str_repeat('<td></td>', count($_properties)+1) 
                        ?>
                    <?php endif; ?>

                <?php endforeach ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
