'use strict';

var gulp = require('gulp');
var sass = require('gulp-sass');

gulp.task('xnova-sass', function ()
{
	gulp.src('./xnova/scss/**/*.scss')
		.pipe(sass({outputStyle: 'compressed'}).on('error', sass.logError))
    	.pipe(gulp.dest('../css'));
});

gulp.task('xnova-sass:watch', function ()
{
	gulp.watch('./xnova/scss/**/*.scss', ['xnova-sass']);
});

gulp.task('default', ['xnova-sass']);