var gulp = require('gulp'),
	minifyCss = require('gulp-minify-css'),
	concat = require('gulp-concat'),
	uglify = require('gulp-uglify'),
	rename = require('gulp-rename'),
	clean = require('gulp-clean'),
	htmlMin = require('gulp-htmlmin');

/**
 * Vue 的数据压缩
 */
gulp.task("vue_mini_js", function () {
	return gulp.src('vue/dist.js')
		.pipe(uglify())
		.pipe(rename({suffix: '.min'}))
		.pipe(gulp.dest('vue'));
});

/**
 * Vue JS 输出
 */
gulp.task("vue_js", ['vue_mini_js'], function () {
	return gulp.src(['vue/vue.min.js', 'vue/dist.min.js'])
		.pipe(concat('vue.js'))
		.pipe(gulp.dest('web/style/default/'));
});
