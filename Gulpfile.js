'use strict';

var gulp = require('gulp');
var sass = require('gulp-sass');
var autoprefixer = require('gulp-autoprefixer');
var uglifycss = require('gulp-uglifycss');

gulp.task('sass', function ()
{
	gulp.src('./public/assets/src/game/**/*.scss')
		.pipe(sass().on('error', sass.logError))
		.pipe(autoprefixer())
		.pipe(uglifycss())
    	.pipe(gulp.dest('./public/assets/css'));
});

gulp.task('watch', function ()
{
	gulp.watch('./public/assets/src/game/**/*.scss', ['sass']);
	gulp.watch('./public/assets/src/bootstrap/**/*.scss', ['bootstrap']);
});

gulp.task('bootstrap', function ()
{
	gulp.src('./public/assets/src/bootstrap/**/*.scss')
		.pipe(sass().on('error', sass.logError))
		.pipe(autoprefixer())
		.pipe(uglifycss())
    	.pipe(gulp.dest('./public/assets/css'));
});

gulp.task('default', ['sass', 'bootstrap']);