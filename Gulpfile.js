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
	return gulp.src('web_resource/vue/dist.js')
		.pipe(uglify())
		.pipe(rename({suffix: '.min'}))
		.pipe(gulp.dest('vue'));
});

/**
 * Vue JS 输出
 */
gulp.task("vue_js", ['vue_mini_js'], function () {
	return gulp.src(['web_resource/vue/vue.min.js', 'web_resource/vue/dist.min.js'])
		.pipe(concat('vue.js'))
		.pipe(gulp.dest('web/style/default/'));
});

gulp.task('admin_v2_plugin_js', function () {
	var list = [
		'fastclick/fastclick.js',
		'sparkline/jquery.sparkline.js',
		'jvectormap/jquery-jvectormap-1.2.2.min.js',
		'jvectormap/jquery-jvectormap-world-mill-en.js',
		'slimScroll/jquery.slimscroll.js',
		'chartjs/Chart.js'
	];
	for (var i = 0; i < list.length; i++) {
		list[i] = "bower_components/admin-lte/plugins/" + list[i];
	}
	return gulp.src(list)
		.pipe(concat('plugin.js'))
		.pipe(gulp.dest('web/style/default/admin-v2'))
		.pipe(uglify())
		.pipe(rename({suffix: '.min'}))
		.pipe(gulp.dest('web/style/default/admin-v2'));
});

gulp.task('admin_v2_plugin_css', function () {
	var list = [
		'jvectormap/jquery-jvectormap-1.2.2.css'
	];
	for (var i = 0; i < list.length; i++) {
		list[i] = "bower_components/admin-lte/plugins/" + list[i];
	}
	return gulp.src(list)
		.pipe(concat('plugin.css'))
		.pipe(gulp.dest('web/style/default/admin-v2'))
		.pipe(minifyCss())
		.pipe(rename({suffix: '.min'}))
		.pipe(gulp.dest('web/style/default/admin-v2'));
});

gulp.task('admin_v2_plugin', ['admin_v2_plugin_css', 'admin_v2_plugin_js']);
