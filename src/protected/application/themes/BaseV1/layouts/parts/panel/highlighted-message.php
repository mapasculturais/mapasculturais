<p class="highlighted-message">
    <?php $linkProfile = '<a href="' . $app->user->profile->singleUrl . '">' . $app->user->profile->name . '</a>'; ?>
    <?php printf(\MapasCulturais\i::__("Olá, %s, bem-vindo ao painel do %s!"), $linkProfile, $this->dict('site: name', false));?>
</p>
