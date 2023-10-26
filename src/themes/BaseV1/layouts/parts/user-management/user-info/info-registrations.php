<?php

use \MapasCulturais\i;
use MapasCulturais\App;

?>
<table class="projects-table entity-table">
    <caption>
        <?php echo \MapasCulturais\i::_e("Minhas inscrições"); ?>
    </caption>
    <thead>
        <tr>
            <td><?php \MapasCulturais\i::_e("Inscrição"); ?></td>
            <td><?php \MapasCulturais\i::_e("Oportunidade"); ?></td>
            <td><?php \MapasCulturais\i::_e("Responsável"); ?></td>
            <td><?php \MapasCulturais\i::_e("Data de envio"); ?></td>
        </tr>
    </thead>
    <tbody>
        <?php if ($registrations) { ?>
            <?php foreach ($registrations as $registration) { ?>
                <?php
                $date = ($registration->sentTimestamp ? $registration->sentTimestamp->format("d/m/y H:m:s") : null);
                $url_reg = App::i()->createUrl('inscricao', '', [$registration->id]);
                $url_opp = App::i()->createUrl('oportunidade', '', [$registration->opportunity->id]);
                $url_agent = App::i()->createUrl('agente', '', [$registration->getRegistrationOwner()->id]);
                ?>
                <tr>
                    <td><a target="_blank" href="<?=$url_reg?>"><?= $registration->id ?></a></td>
                    <td><a target="_blank" href="<?=$url_opp?>"><?= $registration->opportunity->name ?></a></td>
                    <td><a target="_blank" href="<?= $url_agent ?>"><?= $registration->getRegistrationOwner()->name ?></a></td>
                    <td><?= $date ?: i::_e("Não enviada") ?></td>
                </tr>
            <?php } ?>
        <?php } else { ?>
            <tr>
                <td colspan="3"><?php i::_e("Não foram encontradas inscrições"); ?></td>
            </tr>
        <?php } ?>
    </tbody>
</table>