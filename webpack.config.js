const { VueLoaderPlugin } = require('vue-loader');

const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;
const UglifyJsPlugin = require('uglifyjs-webpack-plugin');
const ConcatPlugin = require('webpack-concat-plugin');
const CleanWebpackPlugin = require('clean-webpack-plugin')

const env = process.env.NODE_ENV;

const config = {
	context: __dirname,
	entry: {
		'app/app': './public/assets/src/vue/app.js',
		'admin/app': './public/assets/admin/src/app.js',
	},
	mode: env,
	devtool: false,
	output: {
		path: __dirname+'/public/assets/build',
		filename: '[name].js',
		publicPath: '/assets/build/',
		chunkFilename: '[name].js'
	},
	optimization: {
	    minimize: env === 'production',
		minimizer: [
			new UglifyJsPlugin({
				uglifyOptions: {
					output: {
						comments: false
					}
				}
			})
		],
		runtimeChunk: 'single',
		splitChunks: {
			minChunks: 2,
			cacheGroups: {
				default: false,
				commons: {
					test: /node_modules/,
					name: "app/_1_vendor",
					chunks: "initial",
					enforce: true,
				}
			}
		}
	},
	performance: {
		hints: false
	},
	module: {
		rules: [{
			test: /vue-router(.*?)\.js$/,
			loader: 'string-replace-loader',
			options: {
				search: 'isSameRoute(route, current)',
				replace: 'false',
			}
		}, {
			test: /\.vue$/,
			use: [{
				loader: 'vue-loader',
				options: {
					compilerOptions: {
						preserveWhitespace: true
					}
				}
			}],
		}, {
			test: /\.js$/,
			exclude: /node_modules/,
			loader: 'babel-loader'
		},{
			test: /\.scss$/,
			use: [{
					loader: 'file-loader',
					options: {
						name: '[name].css',
						outputPath (file) {
							return (file.includes('admin') ? 'admin' : 'app')+'/'+file
						},
						publicPath: './'
					}
				}, {
				   loader: 'extract-loader'
				}, {
					loader: "css-loader",
					options: {
						minimize: env === 'production',
						url: false
					}
				}, {
					loader: 'postcss-loader',
					options: {
						config: {
							path: 'postcss.config.js'
						}
					}
				}, {
					loader: "resolve-url-loader"
				}, {
					loader: 'sass-loader',
					options: {
						sourceMap: true
					}
				},
			]
		}]
	},
	plugins: [
		new VueLoaderPlugin(),
		new ConcatPlugin({
		    uglify: env === 'production',
		    sourceMap: false,
		    name: 'global',
		    outputPath: '',
		    fileName: 'app/_0_[name].js',
		    filesToConcat: [
		    	'jquery',
				'jquery-confirm',
				'jquery-toast-plugin',
				'jquery-touchswipe',
				'tooltipster',
				'./public/assets/js/game.js'
			],
		    attributes: {
		        async: true
		    }
		}),
		new CleanWebpackPlugin(['build/*.*', 'build/app/*.*', 'build/admin/*.*'], {
			root: __dirname+'/public/assets/',
			verbose: true
		}),
		/*new BundleAnalyzerPlugin({
			analyzerPort: 1234
		})*/
	],
	resolve: {
		alias: {
			'jquery': 'jquery/dist/jquery.js',
			'chart': 'chart.js/dist/Chart.js',
			'socket.io': 'socket.io-client/dist/socket.io.slim.js',
			'api': __dirname+'/public/assets/src/vue/js/api.js',
			'router-mixin': __dirname+'/public/assets/src/vue/router/component.js',
			'app': __dirname+'/public/assets/src/vue/app.js',
			'helpers': __dirname+'/public/assets/src/vue/js/helpers.js',
		}
	},
	stats: {
		colors: true,
		modules: false,
		children: false,
		chunks: false,
		chunkModules: false
	}
}

module.exports = config;