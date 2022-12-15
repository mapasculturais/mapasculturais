import * as Vue from 'vue'

export default {
    install(app) {
        app.config.globalProperties.mediaQueries = Vue.reactive([]);
        app.config.globalProperties.$media = (query) => {
            if (!app.config.globalProperties.mediaQueries[query]) {
                const mql = globalThis.matchMedia(`(${query})`)
                app.config.globalProperties.mediaQueries[query] = mql.matches

                mql.addEventListener("change", (event) => {
                    app.config.globalProperties.mediaQueries[query] = event.matches
                });
            }
            return app.config.globalProperties.mediaQueries[query]
        }
    }
}