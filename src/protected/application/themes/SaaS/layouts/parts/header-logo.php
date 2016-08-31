<?php if($this->saasCfg->getLogo()): ?>
  <h1 id="brand-logo"><a href="<?php echo $app->getBaseUrl() ?>"><img src="<?php echo $this->saasCfg->logo->transform('logoHeader')->url; ?>"/></a></h1>
<?php else: ?>
  <h1 id="brand-logo"><a href="<?php echo $app->getBaseUrl() ?>"><img src="<?php $this->asset('img/logo-site.png'); ?>" /></a></h1>
<?php endif; ?>
