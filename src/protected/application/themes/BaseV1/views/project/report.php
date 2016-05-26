<?php
use MapasCulturais\Entities\Registration as R;
use MapasCulturais\Entities\Agent;

function echoStatus($registration){
    switch ($registration->status){
        case R::STATUS_APPROVED:
            echo 'seleccionada';
            break;

        case R::STATUS_NOTAPPROVED:
            echo 'no seleccionada';
            break;

        case R::STATUS_WAITLIST:
            echo 'suplente';
            break;

        case R::STATUS_INVALID:
            echo 'inválida';
            break;

        case R::STATUS_SENT:
            echo 'pendiente';
            break;
    }
}

$_properties = $app->config['registration.propertiesToExport'];

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
            <th>Número</th>
            <th>URL</th>
            <th>Status</th>
            <?php if($entity->registrationCategories):?>
                <th><?php echo $entity->registrationCategTitle ?></th>
            <?php endif; ?>
            <th>Arquivos</th>
            <?php foreach($entity->getUsedAgentRelations() as $def): ?>
                <th><?php echo $def->label; ?></th>
                <?php foreach($_properties as $prop): if($prop === 'name') continue; ?>
                    <th><?php echo $def->label; ?> - <?php echo Agent::getPropertyLabel($prop); ?></th>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach($entity->sentRegistrations as $r): ?>
            <tr>
                <td><?php echo $r->number; ?></td>
                <td><?php echo $r->singleUrl; ?></td>
                <td><?php echo echoStatus($r); ?></td>

                <?php if($entity->registrationCategories):?>
                    <td><?php echo $r->category; ?></td>
                <?php endif; ?>

                <td>
                    <?php if(key_exists('zipArchive', $r->files)): ?>
                        <a href="<?php echo $r->files['zipArchive']->url; ?>">zip</a>
                     <?php endif; ?>
                </td>

                <?php
                foreach($r->_getDefinitionsWithAgents() as $def):
                    if($def->use == 'dontUse') continue;
                    $agent = $def->agent;
                ?>

                    <?php if($agent): ?>
                        <td><a href="<?php echo $agent->singleUrl; ?>" target="_blank"><?php echo $r->agentsData[$def->agentRelationGroupName]['name'];?></a></td>

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
