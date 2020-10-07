<?php
use MapasCulturais\Entities\Registration as R;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Space as SpaceRelation;
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
$space_properties = $app->config['registration.spaceProperties'];

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

            <!-- Cabeçalho com labels das informações dos espaços cadastrados-->
            <?php foreach($space_properties as $prop): ?>
                <th><?php echo 'Espaço ' ?> - <?php echo SpaceRelation::getPropertyLabel($prop); ?></th>
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

                <?php showIfField($entity->registrationCategories, $r->category); ?>

                <?php
                foreach($custom_fields as $field):
                    $_field_val = (isset($field["field_name"])) ? $r->{$field["field_name"]} : "";

                    if(is_array($_field_val) && isset($_field_val[0]) && $_field_val[0] instanceof stdClass) {
                        $_field_val = (array)$_field_val[0];
                    }

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

                 <!--Informações dos espaços cadastrados-->
                 <?php foreach($r->getSpaceData() as $field): ?>
                    <?php if(is_array($field)): ?>
                        <td><?php echo implode(', ', $field); ?></td>
                    <?php else: ?>
                        <td><?php echo $field; ?></td>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
