var gulp = require('gulp'),
	minifyCss = require('gulp-minify-css'),
	concat = require('gulp-concat'),
	uglify = require('gulp-uglify'),
	rename = require('gulp-rename'),
	clean = require('gulp-clean'),
	htmlMin = require('gulp-htmlmin'),
	fs = require('fs'),
	child_process = require('child_process');

/**
 * Vue 的数据压缩
 */
gulp.task("vue_mini_js", function () {
	child_process.execSync("php make_vue_script.php");

	var path = 'web_resource/vue/';
	if (fs.existsSync(path + "dist.min.js")) {
		fs.unlinkSync(path + 'dist.min.js');
	}
	return gulp.src(path + 'dist.js')
		.pipe(uglify())
		.pipe(rename({suffix: '.min'}))
		.pipe(gulp.dest(path));
});

/**
 * Vue JS 输出
 */
gulp.task("vue_js", ['vue_mini_js'], function () {
	gulp.src([
		'web_resource/director/director.min.js',
		'web_resource/vue/vue.min.js',
		'web_resource/vue/dist.min.js'
	]).pipe(concat('vue.min.js'))
		.pipe(gulp.dest('web/style/default/'));

	gulp.src([
		'web_resource/director/director.js',
		'web_resource/vue/vue.js',
		'web_resource/vue/dist.js'
	]).pipe(concat('vue.js'))
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
