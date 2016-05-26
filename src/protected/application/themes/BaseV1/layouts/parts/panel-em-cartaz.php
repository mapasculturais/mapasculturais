<style>
    .emcartaz-datepicker{
        border:none;
        width:90px;
        cursor:pointer;
        font-size: 1rem;
        font-weight: bold;
        padding:0;
    }

    .main-content a{color:#880000}
</style>
<div class="main-content">
    <h1>Revista En Cartel</h1>
    <div class="clearfix">
            Filtrar Eventos
            <label for="data-de-inicio">de</label>
            <input id="from-visible" type="text" class="js-emcartaz-dates emcartaz-datepicker"
                   readonly="readonly" placeholder="00/00/0000" value="<?php echo $from->format('d/m/Y'); ?>">
            <input id="from" name="startsOn" type="hidden" value="<?php echo $from->format('Y-m-d'); ?>">

            <label for="data-de-fim">a</label>
            <input id="to-visible" type="text" class="js-emcartaz-dates emcartaz-datepicker"
                   readonly="readonly" placeholder="00/00/0000" value="<?php echo $to->format('d/m/Y'); ?>">
            <input id="to" name="until" type="hidden" value="<?php echo $to->format('Y-m-d'); ?>">
    </div>

    <a href="#" onclick="go('<?php echo $app->createUrl('panel', 'em-cartaz-preview'); ?>')"> Visualizar  </a>
    |
    <a href="#" onclick="go('<?php echo $app->createUrl('panel', 'em-cartaz-download'); ?>')"> Bajar  </a>
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
                altField: altFieldSelector,
                beforeShow: function() {
                    setTimeout(function(){
                        $('.ui-datepicker').css('z-index', 99999999999999);
                    }, 0);
                }
            });
        });
    });

    function go(url){
        location.href=url+'?from='+$('#from').val()+'&to='+$('#to').val();
    }
</script>