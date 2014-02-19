window.onload = function(){

    var jquery = document.createElement('script');
    jquery.src = 'js/jquery-2.0.3.min.js';
    jquery.type = 'text/javascript';
    document.head.appendChild(jquery);
    jquery.onload = function(){
        var esquemao = document.createElement('script');
        esquemao.src = 'js/esquemao.js';
        esquemao.type = 'text/javascript';
        document.head.appendChild(esquemao);
    }
};