// Faz a abertura da tela de impressão apos 1,5 segundos
window.onload = function(){
    setTimeout(function(){
        window.print();
    },1500);
}

//Escuta o scroll e seta o top do header para que fique fixo na tela
document.addEventListener('scroll', function(e) {
    document.getElementById('main-header').style.top = 0;
});