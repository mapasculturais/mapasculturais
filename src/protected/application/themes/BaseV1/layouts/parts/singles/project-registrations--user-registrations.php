<?php if ($registrations = $app->repo('Registration')->findByProjectAndUser($entity, $app->user)): ?>
    <table class="my-registrations">
        <caption><?php \MapasCulturais\i::_e("Minhas inscrições");?></caption>
        <thead>
            <tr>
                <th class="registration-id-col">
                    <?php \MapasCulturais\i::_e("Inscrição");?>
                </th>
                <th class="registration-agents-col">
                    <?php \MapasCulturais\i::_e("Agentes");?>
                </th>
                <th class="registration-status-col">
                    <?php \MapasCulturais\i::_e("Status");?>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($registrations as $registration): ?>
                <tr>
                    <td class="registration-id-col">
                        <a href="<?php echo $registration->singleUrl ?>"><?php echo $registration->number ?></a>
                    </td>
                    <td class="registration-agents-col">
                        <p>
                            <span class="label"><?php \MapasCulturais\i::_e("Responsável");?></span><br>
                            <?php echo $registration->owner->name ?>
                        </p>
                        <?php
                        foreach ($app->getRegisteredRegistrationAgentRelations() as $def):
                            if (!$entity->useRegistrationAgentRelation($def))
                                continue;
                            ?>
                            <?php if ($agents = $registration->getRelatedAgents($def->agentRelationGroupName)): ?>
                                <p>
                                    <span class="label"><?php echo $def->label ?></span><br>
                                    <?php echo $agents[0]->name ?>
                                </p>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </td>
                    <td class="registration-status-col">
                        <?php if ($registration->status > 0): ?>
                            <?php \MapasCulturais\i::_e("Enviada em");?> <?php echo $registration->sentTimestamp ? $registration->sentTimestamp->format('d/m/Y à\s H:i'): ''; ?>.
                        <?php else: ?>
                            <?php \MapasCulturais\i::_e("Não enviada.");?><br>
                            <a class="btn btn-small btn-primary" href="<?php echo $registration->singleUrl ?>"><?php \MapasCulturais\i::_e("Editar e enviar");?></a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>