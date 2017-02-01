/* Uruguyan :) initialisation for the jQuery UI date picker plugin. */
/* Traducido by Leo Trujillo by libre.coop. */
(function( factory ) {
	if ( typeof define === "function" && define.amd ) {

		// AMD. Register as an anonymous module.
		define([ "../datepicker" ], factory );
	} else {

		// Browser globals
		factory( jQuery.datepicker );
	}
}(function( datepicker ) {

datepicker.regional['es-ES'] = {
	closeText: 'Cerrar',
	prevText: '&#x3C;Anterior',
	nextText: 'Próximo&#x3E;',
	currentText: 'Hoy',
	monthNames: ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
	'Julio','Agosto','Setiembre','Octubre','Noviembre','Deciembre'],
	monthNamesShort: ['Ene','Feb','Mar','Abr','May','Jun',
	'Jul','Ago','Set','Oct','Nov','Dic'],
	dayNames: ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'],
	dayNamesShort: ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'],
	dayNamesMin: ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'],
	weekHeader: 'Sem',
	dateFormat: 'dd/mm/yy',
	firstDay: 0,
	isRTL: false,
	showMonthAfterYear: false,
	yearSuffix: ''};
datepicker.setDefaults(datepicker.regional['es-ES']);

return datepicker.regional['es-ES'];

}));
