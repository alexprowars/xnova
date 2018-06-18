const { VueLoaderPlugin } = require('vue-loader');

const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;
const UglifyJsPlugin = require('uglifyjs-webpack-plugin');
const ConcatPlugin = require('webpack-concat-plugin');

const env = process.env.NODE_ENV;

const config = {
	context: __dirname,
	entry: {
		'js/app': './public/assets/src/vue/app.js',
		'js/admin': './public/assets/admin/src/app.js',
	},
	mode: env,
	devtool: false,
	output: {
		path: __dirname+'/public/assets/build',
		filename: '[name].js',
		publicPath: '/'
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
		runtimeChunk: {
			name: 'vendor'
		},
		splitChunks: {
			cacheGroups: {
				default: false,
				commons: {
					test: /node_modules/,
					name: "vendor",
					chunks: "all",
					enforce: true,
					minSize: 1
				}
			}
		}
	},
	performance: {
		hints: false
	},
	module: {
		rules: [{
			test: /\.vue$/,
			use: [{
				loader: 'vue-loader',
				options: {
					compilerOptions: {
						preserveWhitespace: false
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
						outputPath: 'css/',
						publicPath: './'
					}
				},
				{
				   loader: 'extract-loader'
				},
				{
					loader: "css-loader",
					options: {
						minimize: env === 'production',
						url: false
					}
				},
				{
					loader: 'postcss-loader',
					options: {
						config: {
							path: 'postcss.config.js'
						}
					}
				},
				{
					loader: "resolve-url-loader"
				},
				{
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
		    fileName: '[name].js',
		    filesToConcat: [
		    	'jquery',
				'jquery-confirm',
				'jquery-toast-plugin',
				'jquery-touchswipe',
				'tooltipster',
				'jquery-validation',
				'./public/assets/js/game.js'
			],
		    attributes: {
		        async: true
		    }
		})
		//new BundleAnalyzerPlugin()
	],
	resolve: {
		alias: {
			'chart': 'chart.js/dist/Chart.js',
			'socket.io': 'socket.io-client/dist/socket.io.slim.js',
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