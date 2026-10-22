let mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
     | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/atlas.js', 'public/js')
	.js('resources/js/app.js', 'public/js')
	.sass('resources/sass/app.scss', 'public/css')
	.copy('node_modules/tinymce/skins', 'public/js/skins');

// Versionado SIEMPRE (no solo en production): deploy.sh compila con `npm run dev`, y sin
// hash en la URL los navegadores seguían usando el app.js viejo cacheado después de un
// deploy (oct-2026: "no aparecen los nombres de los inscriptos"). mix() en los blades
// agrega ?id=<hash> desde mix-manifest.json, que cambia solo cuando cambia el archivo.
mix.version();

if (!mix.inProduction()) {
	mix.copy('resources/js/app.js', 'public/bundle.js');
}