<div class="bloco">
	<h3 class="subtitulo">Compartilhamentos</h3>
	<div class="compartilhamentos">000</div>
</div>

<div class="bloco">
	<h3 class="subtitulo">Compartilhar</h3>
	<div class="fb-share-button botoes-de-compartilhar" data-href="<?php echo $entity->singleUrl; ?>" data-type="button_count"></div>
	<div class="botoes-de-compartilhar">
		<a href="https://twitter.com/share?url=<?php echo $entity->singleUrl; ?>" class="twitter-share-button" data-lang="en">Tweet</a>
		<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="https://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
	</div>
	<div class="botoes-de-compartilhar">
		<div class="g-plus" data-action="share" data-annotation="bubble" data-href="<?php echo $entity->singleUrl; ?>"></div>
		<script type="text/javascript">
		  window.___gcfg = {lang: 'pt-BR'};

		  (function() {
			var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
			po.src = 'https://apis.google.com/js/platform.js';
			var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
		  })();
		</script>
	</div>
</div>

<?php if (is_editable() || $entity->twitter || $entity->facebook || $entity->googleplus): ?>
    <div class="bloco">
        <h3 class="subtitulo">Seguir</h3>

        <?php if (is_editable() || $entity->twitter): ?>
        <a class="icone social_twitter js-editable" data-edit="twitter" data-notext="true" data-original-title="Perfil no Twitter"
           href="<?php echo $entity->twitter ? $entity->twitter : '#" onclick="return false; ' ?>"
           data-value="<?php echo $entity->twitter ?>"></a>
        <?php endif; ?>

        <?php if (is_editable() || $entity->facebook): ?>
        <a class="icone social_facebook js-editable" data-edit="facebook" data-notext="true" data-original-title="Perfil no Facebook"
           href="<?php echo $entity->facebook ? $entity->facebook : '#" onclick="return false; ' ?>"
           data-value="<?php echo $entity->facebook ?>"></a>
        <?php endif; ?>

        <?php if (is_editable() || $entity->googleplus): ?>
        <a class="icone social_googleplus js-editable" data-edit="googleplus" data-notext="true" data-original-title="Perfil no Google Plus"
           href="<?php echo $entity->googleplus ? $entity->googleplus : '#" onclick="return false; ' ?>"
           data-value="<?php echo $entity->googleplus ?>"></a>
        <?php endif; ?>

    </div>
<?php endif; ?>
