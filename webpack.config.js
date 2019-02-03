const { VueLoaderPlugin } = require('vue-loader');

const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;
const TerserPlugin = require('terser-webpack-plugin');
const OptimizeCSSAssetsPlugin = require("optimize-css-assets-webpack-plugin");
const CleanWebpackPlugin = require('clean-webpack-plugin')
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const webpack = require('webpack');

const env = process.env.NODE_ENV;

const config = {
	context: __dirname,
	entry: {
		'app': './public/assets/app/app.js',
		'admin': './public/assets/admin/src/app.js',
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
			new TerserPlugin({}),
			new OptimizeCSSAssetsPlugin({})
		],
		runtimeChunk: false,
		splitChunks: {
			minChunks: 2,
			cacheGroups: {
				default: false,
				commons: {
					test: /node_modules\/(.*)\.js/,
					name: "vendor",
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
			test: require.resolve('jquery'),
			use: [{
				loader: 'expose-loader',
				options: '$'
			}]
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
		}, {
			test: /\.scss$/,
			use: [{
					loader: MiniCssExtractPlugin.loader,
					options: {
						publicPath: '../'
					}
				}, {
					loader: "css-loader",
					options: {
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
					loader: "resolve-url-loader",
					options: {
						keepQuery: true
					}
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
		new MiniCssExtractPlugin({
		  	filename: '[name].css'
		}),
		new webpack.ProvidePlugin({
			$: 'jquery',
			jQuery: 'jquery',
			'window.jQuery': 'jquery',
			'window.$': 'jquery'
		}),
		new CleanWebpackPlugin(['build/*.*'], {
			root: __dirname+'/public/assets/',
			verbose: true
		}),
		/*new BundleAnalyzerPlugin({
			analyzerPort: 1234
		})*/
	],
	externals: {
		"window": "window"
	},
	resolve: {
		alias: {
			'jquery': 'jquery/dist/jquery.slim.js',
			'chart': 'chart.js/dist/Chart.js',
			'socket.io': 'socket.io-client/dist/socket.io.slim.js',
			'api': __dirname+'/public/assets/app/js/api.js',
			'router-mixin': __dirname+'/public/assets/app/router/component.js',
			'app': __dirname+'/public/assets/app/app.js',
			'helpers': __dirname+'/public/assets/app/js/helpers.js',
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