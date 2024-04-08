const { defineConfig } = require("cypress");

module.exports = defineConfig({
    e2e: {
        baseUrl: 'https://redesign.testes.map.as',
        chromeWebSecurity: false,
        setupNodeEvents(on, config) {
        },
    },
});