<header class="entinty-calendar-header clearfix">
    <strong><span id="agenda-count"></span></strong>
    <?php \MapasCulturais\i::_e("evento");?><span id="agenda-count-plural" class="hidden">s</span>
    <?php \MapasCulturais\i::_e("entre");?>
    <input id="agenda-from-visible" type="text" class="js-agenda-singles-dates agenda-singles-datepicker tag"
           readonly="readonly" placeholder="00/00/0000" value="<?php echo $date_from->format('d/m/Y'); ?>">
    <input id="agenda-from" name="startsOn" type="hidden" value="<?php echo $date_from->format('Y-m-d'); ?>">

    e

    <input id="agenda-to-visible" type="text" class="js-agenda-singles-dates agenda-singles-datepicker tag"
           readonly="readonly" placeholder="00/00/0000" value="<?php echo $date_to->format('d/m/Y'); ?>">
    <input id="agenda-to" name="until" type="hidden" value="<?php echo $date_to->format('Y-m-d'); ?>">

    <img src="<?php $this->asset('img/spinner-black.gif') ?>" class="spinner" />
    <!-- OCULTADO POR ENQUANTO BOTÃO DE CADSTRAR EVENTO
    <a class="btn btn-default add" href="#">adicionar evento</a>
    -->
</header>
