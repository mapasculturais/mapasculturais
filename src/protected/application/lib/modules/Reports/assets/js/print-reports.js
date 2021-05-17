// Faz a abertura da tela de impressão apos 1,5 segundos
window.onload = function(){
    setTimeout(function(){
        window.print();
    }, 3000);
}


//Escuta o scroll e seta o top do header para que fique fixo na tela
document.addEventListener('scroll', function(e) {
    document.getElementById('main-header').style.top = 0;
});

/**
 * Ajusta o gráfico durante a impressão
 */
 function beforePrint() {
    setPrinting(true);
 };

 function afterPrint() {
    setPrinting(false);
    window.close();
};

function setPrinting(printing) {
    Chart.helpers.each(Chart.instances, function(chart) {
        chart._printing = printing;
        chart.resize();
        chart.update();
    });
}

(function() {
    if (window.matchMedia) {
        var mediaQueryList = window.matchMedia('print');
        mediaQueryList.addListener(function(args) {
            if (args.matches) {
                beforePrint();
            } else {
                afterPrint();
            }
        });
    }

    window.onbeforeprint = beforePrint;
    window.onafterprint  = afterPrint;
}());