$(function () {
    let flatpack = MapasCulturais.flatpickr;
    flatpack.locale = MapasCulturais.lcode.substr(0,2);
    flatpack.dateFormat = "Y-m-d";
    flatpack.altInput= true;

    $('input[type="date"]').each(function() {
        let $this = $(this);
        if (!$this.data('flatpickr')) {
            $this.attr('type', 'text');
            $this.flatpickr({
                allowInput: true,
                locale: MapasCulturais.lcode.substr(0,2),
                dateFormat: $this.data('enableTime') ? "Y-m-d H:i" : "Y-m-d",
                enableTime: $this.data('enableTime'),
                altInput: true,
                altFormat: $this.data('enableTime') ? MapasCulturais.flatpickr.altFormat+ ' H:i' : MapasCulturais.flatpickr.altFormat,
                onChange: function(selectedDates, dateStr, instance) {
                    setTimeout(() => {
                        $this.trigger('blur')
                    }, 10);
                },
            });

            setTimeout(function () { 
                $this.next().mask('99/99/9999');
            });
        }
    });
});