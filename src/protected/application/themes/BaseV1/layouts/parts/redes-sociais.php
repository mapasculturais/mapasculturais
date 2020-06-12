<div class="widget">
	<h3><?php \MapasCulturais\i::_e("Compartilhar");?></h3>
	<div class="btn-social-share" data-href="<?php echo $entity->singleUrl; ?>" data-type="button_count"></div>
	<div class="btn-social-share">
		<a href="https://twitter.com/share?url=<?php echo $entity->singleUrl; ?>" class="twitter-share-button" data-lang="en"><?php \MapasCulturais\i::_e("Tweet");?></a>
		<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="https://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
	</div>
	<div class="btn-social-share">
		<div class="g-plus" data-action="share" data-annotation="bubble" data-href="<?php echo $entity->singleUrl; ?>"></div>
		<script type="text/javascript">
		  window.___gcfg = {lang: '<?php echo $app->getCurrentLCode(); ?>'};

		  (function() {
			var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
			po.src = 'https://apis.google.com/js/platform.js';
			var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
		  })();
		</script>
	</div>
</div>

<?php if ($this->isEditable() || $entity->twitter || $entity->facebook || $entity->googleplus || $entity->instagram): ?>
    <div class="widget">
        <h3><?php \MapasCulturais\i::_e("Seguir");?></h3>

        <?php if ($this->isEditable() || $entity->twitter): ?>
        <span <?php if($this->isEditable()):?> class="editable-social" <?php endif; ?> >
            <a class="icon icon-twitter js-editable" data-edit="twitter" data-notext="true" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Perfil no Twitter");?>"
               href="<?php echo $entity->twitter ? $entity->twitter : '#" onclick="return false; ' ?>"
               data-value="<?php echo $entity->twitter ?>"></a>
        </span>
        <?php endif; ?>

        <?php if ($this->isEditable() || $entity->facebook): ?>
        <span <?php if($this->isEditable()):?> class="editable-social" <?php endif; ?> >
            <a class="icon icon-facebook js-editable" data-edit="facebook" data-notext="true" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Perfil no Facebook");?>"
               href="<?php echo $entity->facebook ? $entity->facebook : '#" onclick="return false; ' ?>"
               data-value="<?php echo $entity->facebook ?>"></a>
        </span>
        <?php endif; ?>

        <?php if ($this->isEditable() || $entity->googleplus): ?>
        <span <?php if($this->isEditable()):?> class="editable-social" <?php endif; ?> >
            <a class="icon icon-googleplus js-editable" data-edit="googleplus" data-notext="true" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Perfil no Google Plus");?>"
               href="<?php echo $entity->googleplus ? $entity->googleplus : '#" onclick="return false; ' ?>"
               data-value="<?php echo $entity->googleplus ?>"></a>
        </span>
        <?php endif; ?>

        <?php if ($this->isEditable() || $entity->instagram): ?>
        <span <?php if($this->isEditable()):?> class="editable-social" <?php endif; ?> >
            <a class="icon icon-instagram js-editable" data-edit="instagram" data-notext="true" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Perfil no Instagram");?>"
               href="<?php echo $entity->instagramUrl; ?>"
               data-value="<?php echo $entity->instagram; ?>"></a>
        </span>
        <?php endif; ?>
    </div>
<?php endif; ?>
