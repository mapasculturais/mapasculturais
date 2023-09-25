window.addEventListener('load', () => {
    let interval = setInterval(() => {
        let completed = true;

        let iframes = document.querySelectorAll('iframe');

        for (let iframe of iframes) {
            completed = completed && iframe.contentDocument.readyState == 'complete';
        };

        if (completed) {
            setTimeout(() => {
                window.print();
            }, 1000);

            clearInterval(interval);
        };

    }, 50);

});