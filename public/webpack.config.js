const TerserPlugin = require('terser-webpack-plugin');
const OptimizeCSSAssetsPlugin = require("optimize-css-assets-webpack-plugin");
const { CleanWebpackPlugin } = require('clean-webpack-plugin')

const env = process.env.NODE_ENV;

const config = {
	context: __dirname,
	entry: {
		'admin': './assets/admin/src/app.js',
	},
	mode: env,
	devtool: false,
	output: {
		path: __dirname+'/assets/build',
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
			test: /\.js$/,
			exclude: /node_modules/,
			loader: 'babel-loader'
		},{
			test: /\.scss$/,
			use: [{
					loader: 'file-loader',
					options: {
						name: '[name].css',
						outputPath: '',
						publicPath: './'
					}
				}, {
				   loader: 'extract-loader'
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
		new CleanWebpackPlugin({
			verbose: true
		})
	],
	resolve: {
		alias: {
			'jquery': 'jquery/dist/jquery.js',
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