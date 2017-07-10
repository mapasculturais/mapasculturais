<?php
use MapasCulturais\i;
?>
<a class="btn btn-primary alignright hltip" title="<?php i::esc_attr_e('importar como rascunho: Utilize esta opção se for necessária interação com os agentes inscritos. A inscrição deverá ser reenviada para esta fase.') ?>" style="margin-left:1em;" href="<?= $app->createUrl('opportunity', 'importLastPhaseRegistrations', [$entity->id]) ?>"><?php i::_e("como rascunho");?></a>
<a class="btn btn-primary alignright hltip" title="<?php i::esc_attr_e('importar como enviada: Utilize esta opção se NÃO for necessária interação com os agentes inscritos.') ?>" style="margin-left:1em;" href="<?= $app->createUrl('opportunity', 'importLastPhaseRegistrations', [$entity->id, 'sent' => 1]) ?>"><?php i::_e("importar inscrições");?></a>