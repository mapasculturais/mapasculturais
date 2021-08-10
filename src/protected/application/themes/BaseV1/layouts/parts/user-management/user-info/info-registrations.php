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
            <td><?php \MapasCulturais\i::_e("Responsável"); ?></td>
            <td><?php \MapasCulturais\i::_e("Data de envio"); ?></td>
        </tr>
    </thead>
    <tbody>
        <?php if ($registrations) { ?>
            <?php foreach ($registrations as $registration) { ?>
                <?php
                $date = ($registration->sentTimestamp ? $registration->sentTimestamp->format("d/m/y H:m:s") : null);
                $url = App::i()->createUrl('inscricao', '', [$registration->id]);
                $agent = App::i()->createUrl('agente', '', [$registration->getRegistrationOwner()->id]);
                ?>
                <tr>
                    <td><a target="_blank" href="<?=$url?>"><?= $registration->number ?></a></td>
                    <td><a target="_blank" href="<?= $agent ?>"><?= $registration->getRegistrationOwner()->name ?></a></td>
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