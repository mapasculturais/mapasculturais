// moment.js locale configuration
// locale : espanol
// author : Leo Kterva

(function (factory) {
    if (typeof define === 'function' && define.amd) {
        define(['moment'], factory); // AMD
    } else if (typeof exports === 'object') {
        module.exports = factory(require('../moment')); // Node
    } else {
        factory(window.moment); // Browser global
    }
}(function (moment) {
    return moment.defineLocale('pt-br', {
        months : "enero_febrero_marzo_abril_mayo_junio_julio_agosto_setiembre_octubre_noviembre_diciembre".split("_"),
        monthsShort : "ene_feb_mar_abr_may_jun_jul_ago_set_oct_nov_dic".split("_"),
        weekdays : "domingo_lunes_martes_miércoles_jueves_viernes_sábado".split("_"),
        weekdaysShort : "dom_lun_mar_mié_jue_vie_sáb".split("_"),
        weekdaysMin : "dom_lun_mar_mié_jue_vie_sáb".split("_"),
        longDateFormat : {
            LT : "HH:mm",
            L : "DD/MM/YYYY",
            LL : "D [de] MMMM [de] YYYY",
            LLL : "D [de] MMMM [de] YYYY [a las] LT",
            LLLL : "dddd, D [de] MMMM [de] YYYY [a las] LT"
        },
        calendar : {
            sameDay: '[Hoy a las] LT',
            nextDay: '[Mañana a las] LT',
            nextWeek: 'dddd [a las] LT',
            lastDay: '[Ayer a las] LT',
            lastWeek: function () {
                return (this.day() === 0 || this.day() === 6) ?
                    '[Último] dddd [a las] LT' : // Saturday + Sunday
                    '[Última] dddd [a las] LT'; // Monday - Friday
            },
            sameElse: 'L'
        },
        relativeTime : {
            future : "en %s",
            past : "%s atrás",
            s : "segundos",
            m : "un minuto",
            mm : "%d minutos",
            h : "una hora",
            hh : "%d horas",
            d : "un día",
            dd : "%d días",
            M : "un mes",
            MM : "%d meses",
            y : "un año",
            yy : "%d años"
        },
        ordinal : '%dº'
    });
}));
