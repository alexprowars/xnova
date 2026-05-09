import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import inertia from '@inertiajs/vite';
import svgLoader from 'vite-svg-loader';
import { resolve } from 'path';

export default defineConfig({
	build: {
		chunkSizeWarningLimit: 5000,
		target: 'es2022',
	},
	resolve: {
		alias: {
			'~': resolve(__dirname, 'resources/app'),
		},
	},
	plugins: [
		laravel({
			input: ['resources/app/app.js'],
			refresh: true,
		}),
		tailwindcss(),
		vue({
			template: {
				transformAssetUrls: {
					base: null,
					includeAbsolute: false,
				},
				compilerOptions: {
					whitespace: 'preserve'
				}
			},
			features: {
				prodDevtools: true
			},
		}),
		inertia(),
		svgLoader({
			defaultImport: 'url',
			svgoConfig: {
				plugins: [{
					name: 'preset-default',
					params: {
						overrides: {
							removeViewBox: false,
						},
					},
				}],
			}
		}),
	],
});
