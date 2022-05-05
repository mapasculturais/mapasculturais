const { join } = require('path')

const { sync: glob } = require('fast-glob')
const mix = require('laravel-mix')

const CWD = process.cwd()
const pkg = require(`${CWD}/package.json`)

function resolveDestination (source, type) {
    const sourceId = source.split('/').pop().split('.').shift()
    const exportName = pkg.mapas?.assets?.[type]?.[sourceId]
    if (exportName) {
        return `assets/${type}/${exportName}.${type}`
    } else {
        return `assets/${type}/${sourceId}.${type}`
    }
}

const GLOB_OPTIONS = {
    cwd: CWD,
}

mix.alias({
    vue$: join(CWD, 'node_modules/vue/dist/vue.esm-bundler.js')
})
mix.webpackConfig((webpack) => ({
    plugins: [
        new webpack.DefinePlugin({
            '__VUE_OPTIONS_API__': true,
            '__VUE_PROD_DEVTOOLS__': false,
        }),
    ],
}))

glob(['assets-src/js/*.js'], GLOB_OPTIONS).map((source) => {
    const destination = resolveDestination(source, 'js')
    mix.js(source, destination)
})

glob(['assets-src/sass/*.scss'], GLOB_OPTIONS).map((source) => {
    const destination = resolveDestination(source, 'css')
    mix.sass(source, destination)
})
