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

    function postToParent() {
        window.parent.postMessage({
            type: 'resize',
            data: {
                height: document.body.offsetHeight + 30,
            }
        }, '*');

        const registrationFields = {};
        for (let key in MapasCulturais.registration) {
            if (key.indexOf('field_') === 0) {
                registrationFields[key] = MapasCulturais.registration[key];
            } 
        }

        window.parent.postMessage({
            type: 'registration.update',
            data: registrationFields,
        }, '*');
    }

    setInterval(postToParent, 50);

    $(document).ready(function() {
        $("body").on("click", "a[href]", function(event) {
            event.preventDefault();
            window.open($(this).attr("href"), "_top");
        });
    });
  
})(jQuery)