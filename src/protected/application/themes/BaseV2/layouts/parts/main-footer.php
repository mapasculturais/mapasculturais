<div class="main-footer">


    <div class="main-footer__links">
        <ul>
            <li>
                <a>Acesse</a>
            </li>
            <li>
                <a>Lista de editais e oportunidades</a>
            </li>
            <li>
                <a>Lista de eventos</a>
            </li>
            <li>
                <a>Lista de agentes</a>
            </li>
            <li>
                <a>Lista de espaços</a>
            </li>
            <li>
                <a>Lista de projetos</a>
            </li>
        </ul>
    </div>

    <div class="main-footer__links">
        <ul>
            <li>
                <a href="<?= $app->createUrl('panel', 'index') ?>">Painel</a>
            </li>
            <li>
                <a  href="<?= $app->createUrl('panel', 'opportunities') ?>">Editais e oportunidades</a>
            </li>
            <li>
                <a href="<?= $app->createUrl('panel', 'events') ?>">Meus eventos</a>
            </li>
            <li>
                <a href="<?= $app->createUrl('panel', 'agents') ?>">Meus agentes</a>
            </li>
            <li>
                <a href="<?= $app->createUrl('panel', 'spaces') ?>">Meus espaços</a>
            </li>
            <li>
                <a href="<?= $app->createUrl('auth', 'logout') ?>">Sair</a>
            </li>
        </ul>
    </div>

    <div class="main-footer__links">
        <ul>
            <li>
                <a>Ajuda e Privacidade</a>
            </li>
            <li>
                <a>Como utilizar o mapa?</a>
            </li>
            <li>
                <a>Dúvidas frequentes (FAQ)</a>
            </li>
            <li>
                <a>Termos de uso</a>
            </li>
            <li>
                <a>Política de privacidade</a>
            </li>
            <li>
                <a>Autorização de uso de imagem</a>
            </li>
        </ul>
    </div>
    <div class="main-footer__logo">
        <?php $this->part('site-logo') ?>
        <div class="main-footer__logo--share">
            <a><iconify icon="cib:facebook-f" /></a>
            <a><iconify icon="fa-brands:twitter" /></a>
            <a><iconify icon="brandico:vimeo" /></a>
            <a><iconify icon="akar-icons:youtube-fill" /></a>
            
        </div>
    </div>
</div>