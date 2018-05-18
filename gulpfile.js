'use strict';

var gulp = require('gulp');
var sass = require('gulp-sass');
var autoprefixer = require('gulp-autoprefixer');
var cssmin = require("gulp-clean-css");
var rename = require("gulp-rename");
var uglify = require("gulp-uglify");
var runSequence = require('run-sequence').use(gulp);

var fs = require("fs")
var browserify = require('browserify')
var vueify = require('vueify')
var babelify = require('babelify')

process.env.NODE_ENV = 'production';

gulp.task('sass-core', function ()
{
	return gulp.src('./public/assets/src/game/**/*.scss')
		.pipe(sass().on('error', sass.logError))
		.pipe(autoprefixer())
    	.pipe(gulp.dest('./public/assets/css'));
});

gulp.task('sass-plugins', function ()
{
	return gulp.src('./public/assets/src/plugins/*.scss')
		.pipe(sass().on('error', sass.logError))
		.pipe(autoprefixer({ browsers: ['last 3 versions'], cascade: false}))
    	.pipe(gulp.dest('./public/assets/css/plugins'));
});

gulp.task('vue', function ()
{
	return browserify('./public/assets/src/vue/app.js', {debug: false, bundleExternal: true})
		.transform(babelify, { presets: ['es2015'] })
		.transform(vueify)
		.bundle()
		.pipe(fs.createWriteStream("./public/assets/js/application.js"))
});

gulp.task('watch', function ()
{
	gulp.watch('./public/assets/src/game/**/*.scss', ['sass']);
	gulp.watch('./public/assets/src/bootstrap/**/*.scss', ['bootstrap']);
});

gulp.task('watch-vue', function ()
{
	gulp.watch('./public/assets/src/vue/*.js', ['vue']);
	gulp.watch('./public/assets/src/vue/*.vue', ['vue']);
	gulp.watch('./public/assets/src/vue/**/*.vue', ['vue']);
	gulp.watch('./public/assets/src/vue/**/**/*.vue', ['vue']);
});

gulp.task('bootstrap', function ()
{
	return gulp.src('./public/assets/src/bootstrap/**/*.scss')
		.pipe(sass().on('error', sass.logError))
		.pipe(autoprefixer())
		.pipe(cssmin())
    	.pipe(gulp.dest('./public/assets/css'));
});

gulp.task('admin', function ()
{
	return gulp.src('./public/assets/admin/src/scss/*.scss')
		.pipe(sass())
		.pipe(gulp.dest('./public/assets/admin/css'));
});

gulp.task('minimize', function ()
{
	gulp.src(['./public/assets/css/plugins/*.css', '!./public/assets/css/plugins/*.min.css'])
		.pipe(cssmin())
		.pipe(rename({suffix: '.min'}))
		.pipe(gulp.dest('./public/assets/css/plugins'));

	gulp.src(['./public/assets/css/*.css', '!./public/assets/css/*.min.css'])
		.pipe(cssmin())
		.pipe(rename({suffix: '.min'}))
		.pipe(gulp.dest('./public/assets/css'));

	gulp.src(['./public/assets/js/*.js', '!./public/assets/js/*.min.js'])
	    .pipe(uglify())
		.pipe(rename({suffix: '.min'}))
	    .pipe(gulp.dest('./public/assets/js'));

	gulp.src(['./public/assets/js/plugins/*.js', '!./public/assets/js/plugins/*.min.js'])
	    .pipe(uglify())
		.pipe(rename({suffix: '.min'}))
	    .pipe(gulp.dest('./public/assets/js/plugins'));
});

gulp.task('build', function ()
{
	return runSequence(['sass-core', 'sass-plugins', 'vue', 'bootstrap', 'admin'], 'minimize')
});

gulp.task('default', ['build']);