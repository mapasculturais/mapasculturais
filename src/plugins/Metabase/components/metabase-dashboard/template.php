<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;
$this->import('
    mc-link
    
');
$base_url=$app->createUrl('metabase', 'dashboard');
?>

<div class="metabase-dashboard">
    <div class="metabase-dashboard__header">
        <h2 class="bold"><?php i::_e("Painéis de dados") ?></h2>
        <h4><?php i::_e("Abaixo você confere todos os painéis de disponíveis para serem acessados") ?></h4>
    </div>

    <div class="metabase-dashboard__content">
        <div v-for="name in names" class="metabase-dashboard__card">
            
            <h4 class="bold">{{links[name].title}}</h4>
            <p>{{links[name].text}}</p>
            <div class="metabase-dashboard__btn">
                <a :href="'<?=$base_url ?>?panelId='+name" class="button button--small button--primary"><?php i::_e("conferir painel") ?></a>
            </div>
        </div>
    </div>
</div>