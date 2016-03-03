/* Brazilian initialisation for the jQuery UI date picker plugin. */
/* Written by Leonildo Costa Silva (leocsilva@gmail.com). */
(function( factory ) {
	if ( typeof define === "function" && define.amd ) {

		// AMD. Register as an anonymous module.
		define([ "../datepicker" ], factory );
	} else {

		// Browser globals
		factory( jQuery.datepicker );
	}
}(function( datepicker ) {

datepicker.regional['pt-BR'] = {
	closeText: 'Fechar',
	prevText: '&#x3C;Anterior',
	nextText: 'Próximo&#x3E;',
	currentText: 'Hoje',
	monthNames: ['Enero','Febrei','Marzo','Abril','Mayo','Junio',
	'Julio','Agosto','Setiembre','Octubre','Noviembre','Diciembre'],
	monthNamesShort: ['Ene','Feb','Mar','Abr','May','Jun',
	'Jul','Ago','Set','Oct','Nov','Dic'],
	dayNames: ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'],
	dayNamesShort: ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'],
	dayNamesMin: ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'],
	weekHeader: 'Sm',
	dateFormat: 'dd/mm/yy',
	firstDay: 0,
	isRTL: false,
	showMonthAfterYear: false,
	yearSuffix: ''};
datepicker.setDefaults(datepicker.regional['pt-BR']);

return datepicker.regional['pt-BR'];

}));