import * as Vue from 'vue'

export default {
    install(app) {
        const mediaQueries = Vue.reactive([]);
        app.config.globalProperties.$media = (query) => {
            if (!mediaQueries[query]) {
                const mql = globalThis.matchMedia(`(${query})`)
                mediaQueries[query] = mql.matches

                mql.addEventListener("change", (event) => {
                    mediaQueries[query] = event.matches
                });
            }
            return mediaQueries[query]
        }
    }
}