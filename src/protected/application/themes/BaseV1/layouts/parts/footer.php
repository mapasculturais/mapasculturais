</section>

<?php $this->applyTemplateHook('main-footer','before'); ?>
<footer id="main-footer">
    <?php $this->applyTemplateHook('main-footer','begin'); ?>
    <?php $this->applyTemplateHook('main-footer','end'); ?>
</footer>
<?php $this->applyTemplateHook('main-footer','after'); ?>

<?php $this->part('templates'); ?>
<?php $this->bodyEnd(); ?>
<iframe id="require-authentication" src="" style="display:none; position:fixed; top:0%; left:0%; width:100%; height:100%; z-index:100000"></iframe>

<?php if ($this->isEditable()): ?>
    <div id="editbox-human-crop" class="js-editbox" title="<?php \MapasCulturais\i::esc_attr_e("Recortar imagem");?>">
        <img id="human-crop-image"/>
    </div>
<?php endif; ?>

</body>
</html>
