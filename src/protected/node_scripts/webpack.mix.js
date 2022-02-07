const glob = require('fast-glob')
const mix = require('laravel-mix')

require('laravel-mix-esbuild')

const CWD = process.cwd()

const GLOB_OPTIONS = {
    cwd: CWD,
}

glob.sync(['assets-src/js/*.js'], GLOB_OPTIONS).map((source) => {
    const destination = source.replace('assets-src/', 'assets/')
    mix.js(source, destination).esbuild()
})

glob.sync(['assets-src/sass/*.scss'], GLOB_OPTIONS).map((source) => {
    const destination = source.replace('assets-src/sass/', 'assets/css/').replace('.scss', '.css')
    mix.sass(source, destination)
})
