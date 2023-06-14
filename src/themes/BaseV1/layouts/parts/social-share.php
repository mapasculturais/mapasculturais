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