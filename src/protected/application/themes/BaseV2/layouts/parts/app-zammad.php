<?php if($app->config['zammad.url']): ?>
<script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
<script src="<?=$app->config['zammad.url']?>"></script>
<script>
    $(function() {
        new ZammadChat({
            background: <?=json_encode($app->config['zammad.background'])?>,
            fontSize: '12px',
            chatId: 1
        });
    });
</script>
<?php endif ?>
