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
    
    window.addEventListener("message", function(event) {            
        if (event.data == "registration.save") {
            window.$registrationScope.saveRegistration().success((result) => {
                window.parent.postMessage({type: 'registration.saved'}, '*');
            }).error((errors) => {
                window.parent.postMessage({type: 'registration.saved', ...errors}, '*');
            })
        }
    });

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