<style>
    tbody td, table th{
        text-align: left !important;
    }
</style>
<table>
    <thead>
        <tr>
            <th>Número</th>
            <?php if($entity->registrationCategories):?>
                <th>Categoria</th>
            <?php endif; ?>
            <th>Arquivos</th>
            <?php foreach($entity->getUsedAgentRelations() as $def): ?>
                <th><?php echo $def->label; ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach($entity->sentRegistrations as $r): ?>
            <tr>
                <td><a href="<?php echo $r->singleUrl; ?>" target="_blank"><?php echo $r->number; ?></a></td>
                <?php if($entity->registrationCategories):?>
                    <td><?php echo $r->category; ?></td>
                <?php endif; ?>
                <td><a href="<?php echo $r->files['zipArchive']->url; ?>">zip</a></td>
                <?php
                foreach($r->_getDefinitionsWithAgents() as $def):
                    $agent = $def->agent;
                    if(!$agent){
                        ?><td></td><?php
                        continue;
                    }else{
                        $agent = $agent->simplify('id,name,shortDescription,singleUrl');
                        foreach($r->agentsData[$def->agentRelationGroupName] as $prop => $val){
                            $agent->$prop = $val;
                        }
                    }
                ?>
                <td>
                    <a href="<?php echo $agent->singleUrl; ?>" target="_blank"><?php echo $agent->name;?></a>
                    <?php if(isset($agent->documento)): ?>
                        <br>
                        Documento: <?php echo $agent->documento;?>
                    <?php endif; ?>
                </td>
            <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
