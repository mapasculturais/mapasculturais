$(function () {
    $('input[type="date"]').each(function() {
        let $this = $(this);
        if (!$this.data('flatpickr')) {
            $this.attr('type', 'text');
            $this.flatpickr({
                locale: MapasCulturais.lcode.substr(0,2),
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d/m/Y",
            });
        }
    });
});