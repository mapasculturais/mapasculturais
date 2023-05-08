<?php 
/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 */

$this->import('panel--nav'); 
?>
<?php $this->part('header', $render_data); ?>
<?php $this->part('main-header', $render_data) ?>
<article class="panel">
    <aside class="panel__sidebar">
        <div class="panel-sidebar">
            <panel--nav sidebar></panel--nav>
        </div>
    </aside>
    <main class="panel__main">
        <?= $TEMPLATE_CONTENT ?>
    </main>
</article>
<?php $this->part('main-footer', $render_data) ?>
<?php $this->part('footer', $render_data); ?>