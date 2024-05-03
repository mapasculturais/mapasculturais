const mountSwagger = new Promise((resolve, reject) => {
    resolve(SwaggerUIBundle({
        url: '/docs/v2/openapi.yaml',
        dom_id: '#swagger-ui',
        deepLinking: true,
        presets: [
            SwaggerUIBundle.presets.apis,
            SwaggerUIStandalonePreset
        ],
        plugins: [
            SwaggerUIBundle.plugins.DownloadUrl,
        ],
        layout: "StandaloneLayout",
    }));
})

window.onload = function () {
    document.querySelector('.topbar').style.display = 'none';

    mountSwagger
        .then((res) => {
            window.ui = res;
        });
};