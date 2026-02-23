<h1> <?php $this->dict('error:500: title') ?> </h1>
<p> <?php $this->dict('error:500: message') ?> </p>
<?php if ($display_details ?? false): ?>
    <pre class="exception"><?php echo $exception ?></pre>
<?php endif; ?>