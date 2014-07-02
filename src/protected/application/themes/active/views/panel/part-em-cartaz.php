<?php $this->part('panel/part-nav'); ?>
<?php
$app->enqueueScript('vendor', 'jquery-ui-datepicker', '/vendor/jquery-ui.datepicker.js', array('jquery'));
$app->enqueueScript('vendor', 'jquery-ui-datepicker-pt-BR', '/vendor/jquery-ui.datepicker-pt-BR.min.js', array('jquery'));
?>
<div class="main-content">
    <h1>Revista Em Cartaz</h1>
    <div class="clearfix">
            Filtrar Eventos
            <label for="data-de-inicio">De:</label>
            <input id="from-visible" type="text" class="js-emcartaz-dates data-da-ocorrencia "
                   readonly="readonly" placeholder="00/00/0000" value="<?php echo $from->format('d/m/Y'); ?>">
            <input id="from" name="startsOn" type="hidden" value="<?php echo $from->format('Y-m-d'); ?>">

            <label for="data-de-fim">Até:</label>
            <input id="to-visible" type="text" class="js-emcartaz-dates data-da-ocorrencia "
                   readonly="readonly" placeholder="00/00/0000" value="<?php echo $to->format('d/m/Y'); ?>">
            <input id="to" name="until" type="hidden" value="<?php echo $to->format('Y-m-d'); ?>">
    </div>

    <a href="#" onclick="go('<?php echo $app->createUrl('panel', 'em-cartaz-preview'); ?>')"> Visualizar  </a>
    |
    <a href="#" onclick="go('<?php echo $app->createUrl('panel', 'em-cartaz-download'); ?>')"> Baixar  </a>
    <br><br>
    <div>
        <?php echo $content; ?>
    </div>
</div>
<script>
    $(function(){
        $('.js-emcartaz-dates').each(function(){
            var fieldSelector = '#'+$(this).attr('id');
            var altFieldSelector = $(this).data('alt-field') ? $(this).data('alt-field') : fieldSelector.replace('-visible', '');
            if($(altFieldSelector).length == 0){
                return;
            }
            $(this).datepicker({
                dateFormat: $(this).data('date-format') ? $(this).data('date-format') : 'dd/mm/yy',
                altFormat: $(this).data('alt-format') ? $(this).data('alt-format') : 'yy-mm-dd',
                altField: altFieldSelector
            });
        });
    });

    function go(url){
        location.href=url+'?from='+$('#from').val()+'&to='+$('#to').val();
    }
</script>