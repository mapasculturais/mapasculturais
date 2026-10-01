app.component('opportunity-reports', {
    template: $TEMPLATES['opportunity-reports'],

    props: {
        entity: {
            type: Entity,
            required: true,
        },
        phaseLabel: {
            type: String,
            default: '',
        },
    },

    data() {
        return {
            filters: {
                status: 'all',
            },
            printing: false,
            printCleanup: null,
        };
    },

    beforeUnmount() {
        this.printCleanup?.();
    },

    methods: {
        async print() {
            if (this.printing || document.querySelector('body > .opportunity-reports--print')) {
                return;
            }

            this.printing = true;
            const report = this.$el.cloneNode(true);
            const cleanup = () => {
                report.remove();
                document.body.classList.remove('opportunity-reports-printing');
                window.removeEventListener('afterprint', cleanup);
                this.printing = false;
                this.printCleanup = null;
            };
            this.printCleanup = cleanup;

            try {
                // cloneNode não copia o conteúdo dos canvas. Preserva os gráficos
                // exibidos antes que o layout de impressão redimensione a página.
                const canvases = this.$el.querySelectorAll('canvas');
                const images = Array.from(report.querySelectorAll('canvas'), (canvas, index) => {
                    const image = document.createElement('img');
                    image.src = canvases[index].toDataURL('image/png');
                    image.alt = canvas.closest('.opportunity-reports-chart-card')?.querySelector('h4')?.textContent || '';
                    canvas.replaceWith(image);
                    return image.decode();
                });

                report.classList.add('opportunity-reports--print');
                document.body.appendChild(report);
                await Promise.all(images);

                if (!report.isConnected) {
                    return;
                }

                document.body.classList.add('opportunity-reports-printing');
                window.addEventListener('afterprint', cleanup, { once: true });
                window.print();
            } catch (error) {
                cleanup();
                throw error;
            }
        },
    },
});
