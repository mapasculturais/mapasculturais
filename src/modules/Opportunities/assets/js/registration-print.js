window.addEventListener('load', () => {
    let printed = false;

    function doPrint() {
        if (printed) {
            return;
        }
        printed = true;
        window.print();
    }

    const printRoot = document.querySelector('[data-registration-print-ready]');

    if (printRoot) {
        const fallbackMs = 3000;
        const fallbackId = setTimeout(doPrint, fallbackMs);

        window.addEventListener(
            'registration-print:ready',
            () => {
                clearTimeout(fallbackId);
                setTimeout(doPrint, 300);
            },
            { once: true }
        );

        return;
    }

    const interval = setInterval(() => {
        let completed = true;
        const iframes = document.querySelectorAll('iframe');

        for (const iframe of iframes) {
            completed = completed && iframe.contentDocument?.readyState === 'complete';
        }

        if (completed) {
            setTimeout(doPrint, 1000);
            clearInterval(interval);
        }
    }, 50);
});
