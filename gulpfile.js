'use strict';

var gulp = require('gulp');
var sass = require('gulp-sass');
var autoprefixer = require('gulp-autoprefixer');
var minifyCss = require("gulp-minify-css");
var rename = require("gulp-rename");
var uglify = require("gulp-uglify");

gulp.task('sass', function ()
{
	gulp.src('./public/assets/src/game/**/*.scss')
		.pipe(sass().on('error', sass.logError))
		.pipe(autoprefixer())
		.pipe(minifyCss())
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
		.pipe(minifyCss())
    	.pipe(gulp.dest('./public/assets/css'));
});

gulp.task('admin', function ()
{
	gulp.src('./public/assets/src/admin/bootstrap.scss').pipe(sass()).pipe(gulp.dest('./public/assets/admin/global/plugins/bootstrap/css/'));
	gulp.src('./public/assets/src/admin/global/*.scss').pipe(sass()).pipe(gulp.dest('./public/assets/admin/global/css'));
	gulp.src('./public/assets/src/admin/pages/*.scss').pipe(sass()).pipe(gulp.dest('./public/assets/admin/pages/css'));
	gulp.src('./public/assets/src/admin/layout/*.scss').pipe(sass()).pipe(gulp.dest('./public/assets/admin/pages/css'));
	gulp.src('./public/assets/src/admin/layout/themes/*.scss').pipe(sass()).pipe(gulp.dest('./public/assets/admin/pages/css'));

	gulp.src(['./public/assets/admin/global/css/*.css', '!./public/assets/admin/global/css/*.min.css']).pipe(minifyCss()).pipe(rename({suffix: '.min'})).pipe(gulp.dest('./public/assets/admin/global/css'));
	gulp.src(['./public/assets/admin/pages/css/*.css', '!./public/assets/admin/pages/css/*.min.css']).pipe(minifyCss()).pipe(rename({suffix: '.min'})).pipe(gulp.dest('./public/assets/admin/pages/css'));

	gulp.src(['./public/assets/admin/global/plugins/bootstrap/css/*.css', '!./public/assets/admin/global/plugins/bootstrap/css/*.min.css']).pipe(minifyCss()).pipe(rename({suffix: '.min'})).pipe(gulp.dest('./public/assets/admin/global/plugins/bootstrap/css'));

	gulp.src(['./public/assets/admin/global/js/*.js', '!./public/assets/admin/global/js/*.min.js']).pipe(uglify()).pipe(rename({suffix: '.min'})).pipe(gulp.dest('./public/assets/admin/global/js'));
	gulp.src(['./public/assets/admin/pages/js/*.js', '!./public/assets/admin/pages/js/*.min.js']).pipe(uglify()).pipe(rename({suffix: '.min'})).pipe(gulp.dest('./public/assets/admin/pages/js'));
	gulp.src(['./public/assets/admin/layout/js/*.js', '!./public/assets/admin/layout/js/*.min.js']).pipe(uglify()).pipe(rename({suffix: '.min'})).pipe(gulp.dest('./public/assets/admin/layout/js'));
});

gulp.task('default', ['sass', 'bootstrap', 'admin']);