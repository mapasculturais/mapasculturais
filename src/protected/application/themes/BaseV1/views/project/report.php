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
            <?php if($entity->registrationCategories):?>
                <th>Categoria</th>
            <?php endif; ?>
            <th>Arquivos</th>
            <?php foreach($entity->getUsedAgentRelations() as $def): ?>
                <th><?php echo $def->label; ?></th>
                <th><?php echo $def->label; ?> CPF/CNPJ</th>
                <th><?php echo $def->label; ?> Email Privado</th>
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

                <td>
                    <?php if(key_exists('zipArchive', $r->files)): ?>
                        <a href="<?php echo $r->files['zipArchive']->url; ?>">zip</a>
                     <?php endif; ?>
                </td>

                <?php
                foreach($r->_getDefinitionsWithAgents() as $def):
                    $agent = $def->agent;

                    if($def->use == 'dontUse')
                        continue;

                    if(!$agent){
                        ?>
                        <td></td>
                        <td></td>
                        <td></td>
                        <?php
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
                </td>
                <td>
                    <?php if(isset($agent->documento)): ?>
                        <?php echo $agent->documento;?>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if(isset($agent->emailPrivado)): ?>
                        <?php echo $agent->emailPrivado;?>
                    <?php endif; ?>
                </td>
            <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
