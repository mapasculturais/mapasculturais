<?php
$app->enqueueScript('vendor', 'jquery-ui-datepicker', '/vendor/jquery-ui.datepicker.js', array('jquery'));
$app->enqueueScript('vendor', 'jquery-ui-datepicker-pt-BR', '/vendor/jquery-ui.datepicker-pt-BR.min.js', array('jquery'));
?>

<header class="entinty-calendar-header clearfix">
    <p class="alignleft">
        <strong><span id="agenda-count"></span></strong>
        evento<span id="agenda-count-plural" class="escondido">s</span>
        entre
    </p>
    <input id="agenda-from-visible" type="text" class="js-agenda-singles-dates agenda-singles-datepicker tag"
           readonly="readonly" placeholder="00/00/0000" value="<?php echo $date_from->format('d/m/Y'); ?>">
    <input id="agenda-from" name="startsOn" type="hidden" value="<?php echo $date_from->format('Y-m-d'); ?>">

    e

    <input id="agenda-to-visible" type="text" class="js-agenda-singles-dates agenda-singles-datepicker tag"
           readonly="readonly" placeholder="00/00/0000" value="<?php echo $date_to->format('d/m/Y'); ?>">
    <input id="agenda-to" name="until" type="hidden" value="<?php echo $date_to->format('Y-m-d'); ?>">

    <img src="<?php $this->asset('img/spinner-black.gif') ?>" class="spinner" />
    <!-- OCULTADO POR ENQUANTO BOTÃO DE CADSTRAR EVENTO
    <a class="botao adicionar alignright" href="#">adicionar evento</a>
    -->
</header>