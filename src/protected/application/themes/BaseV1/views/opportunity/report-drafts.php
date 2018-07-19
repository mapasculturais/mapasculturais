<?php

use MapasCulturais\Entities\Registration as R;
use MapasCulturais\Entities\Agent;
use MapasCulturais\i;

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
            <th> <?php i::_e("Agente") ?> </th>
            <th> <?php i::_e("Email publico") ?> </th>
            <th> <?php i::_e("Email privado") ?> </th>
            <th> <?php i::_e("Email de usuário") ?> </th>
            <th> <?php i::_e("Telefone Publico") ?> </th>
            <th> <?php i::_e("Telefone 1") ?> </th>
            <th> <?php i::_e("Telefone 2") ?> </th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($registrationsDraftList as $r): ?>
            <tr>
                <td><a href="<?php echo $r->singleUrl; ?>" target="_blank"><?php echo $r->number; ?></a></td>
                <?php $agent = $r->owner;  ?>
                <?php if(!empty($agent)): ?>
                    <td><a href="<?php echo $agent->singleUrl; ?>" target="_blank"><?php echo $agent->name; ?></a></td>
                    <td><?php echo $agent->emailPublico; ?></td>
                    <td><?php echo $agent->emailPrivado; ?></td>
                    <td><?php echo $agent->user->email; ?></td>
                    <td><?php echo $agent->telefonePublico; ?></td>
                    <td><?php echo $agent->telefone1; ?></td>
                    <td><?php echo $agent->telefone2; ?></td>
                <?php else: ?>
                    str_repeat('<td></td>', 7);
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>