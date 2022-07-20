<?php
use MapasCulturais\i;
?>
<a class="btn btn-primary alignright hltip" title="<?php i::esc_attr_e('Importar as novas inscrições selecionadas da última fase.') ?>" style="margin-left:1em;" href="<?= $app->createUrl('opportunity', 'importLastPhaseRegistrations', [$entity->id]) ?>"><?php i::_e("Importar novas inscrições");?></a>