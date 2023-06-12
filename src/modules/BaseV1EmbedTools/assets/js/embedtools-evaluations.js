
window.addEventListener("message", function(event) {
    if(event?.data?.type?.startsWith('evaluationForm.')){
        const iframe = document.getElementById('evaluation-form');
        iframe.contentWindow.postMessage(event.data);
    }

    if(event?.data?.type?.startsWith('evaluationRegistration.')){
        const iframe = document.getElementById('evaluation-registration');
        iframe.contentWindow.postMessage(event.data);
    }
});