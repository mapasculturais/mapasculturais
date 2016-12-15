<h1> <?php $this->dict('error:500: title') ?> </h1>
<p> <?php $this->dict('error:500: message') ?> </p>
<?php if( $app->config['slim.debug'] ): ?>
    <pre class="exception"><?php echo $e ?></pre>
<?php endif; ?>