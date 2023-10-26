<?php 
use MApasCulturais\i;

$this->layout = 'nolayout';
?>
<div style="text-align: center;">
    <h1><?php i::_e('Procuração') ?></h1>

    <?php i::_e("Dono da procuração") ?>:
    <h2><?php echo $procuration->user->profile->name ?></h2>

    <?php i::_e("Procurador") ?>:
    <h2><?php echo $procuration->attorney->profile->name ?></h2>

    <?php i::_e("permissão") ?>:
    <h3><?php echo $procuration->action ?></h3>

    <?php if($procuration->until): ?>
        <h3><?php i::_e("validade") ?>: <?php echo $procuration->until->format("d/m/Y") ?></h3>
    <?php endif; ?>
    token:
    <code><h4><?php echo $procuration->token ?></h4></code>

</div>
<script>
    if(window.opener){        
        window.opener.postMessage("<?php echo $procuration->token ?>", '*');
    }
</script>