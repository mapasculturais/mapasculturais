<?php if(false && $this->subsiteInstance->logo): ?>
    <h1 id="small-brand"><a href="<?php echo $app->getBaseUrl() ?>"><img src="<?php echo $this->subsiteInstance->logo->transform('logoHeader')->url; ?>" /></a></h1>
<?php else: ?>
    <h1 id="small-brand"><a href="<?php echo $app->getBaseUrl() ?>"><?php $this->dict('site: name'); ?></a></h1>
<?php endif; ?>
