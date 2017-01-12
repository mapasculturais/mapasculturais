<nav id="about-nav" class="alignright clearfix">
    <h1 id="organization-logo"><a href="#"><img src="<?php $this->asset('img/marca-da-org.png'); ?>" /></a></h1>
    <ul id="secondary-menu">
        <li><a class="icon icon-about hltip" href="<?php echo $app->createUrl('site', 'page', array('sobre')) ?>" title="<?php \MapasCulturais\i::esc_attr_e("Sobre os Mapas Culturais");?>"></a></li>
        <li><a class="icon icon-help hltip" href="<?php echo $app->createUrl('site', 'page', array('como-usar')) ?>" title="<?php \MapasCulturais\i::esc_attr_e("Como usar");?>"></a></li>
    </ul>
</nav>
