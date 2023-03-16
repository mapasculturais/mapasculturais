(function($){
    MapasCulturais.Messages.showMessage = function(type, message) {
        if (type == "danger") {
            type = "error";
        }

        let sendMessage = {
            type: 'message',
            data: {
                type,
                message
            }
        }

        window.parent.postMessage(sendMessage, '*');
    }

    function sendSizes() {
        window.parent.postMessage({
            type: 'resize',
            data: {
                height: document.body.offsetHeight+1,
            }
        }, '*');
    }

    setInterval(sendSizes, 200);
})(jQuery)