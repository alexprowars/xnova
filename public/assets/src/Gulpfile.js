'use strict';

var gulp = require('gulp');
var sass = require('gulp-sass');
var autoprefixer = require('gulp-autoprefixer');
var uglifycss = require('gulp-uglifycss');

gulp.task('xnova-sass', function ()
{
	gulp.src('./xnova/scss/**/*.scss')
		.pipe(sass().on('error', sass.logError))
		.pipe(autoprefixer())
		.pipe(uglifycss())
    	.pipe(gulp.dest('../css'));
});

gulp.task('xnova-sass:watch', function ()
{
	gulp.watch('./xnova/scss/**/*.scss', ['xnova-sass']);
});

gulp.task('xnova-bootstrap', function ()
{
	gulp.src('./bootstrap/scss/**/*.scss')
		.pipe(sass().on('error', sass.logError))
		.pipe(autoprefixer())
		.pipe(uglifycss())
    	.pipe(gulp.dest('../css'));
});

gulp.task('default', ['xnova-sass', 'xnova-bootstrap']);