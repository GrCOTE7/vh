var gulp = require('gulp'),
  browserSync = require('browser-sync'),
  sass = require('gulp-sass'),
  concat = require('gulp-concat'),
  shorthand = require('gulp-shorthand'),
  cleanCss = require('gulp-clean-css'),
  minify = require('gulp-minifier'),
  connectphp = require('gulp-connect-php');
imagemin = require('gulp-imagemin');

gulp.task('serve', ['css'], function () {
  connectphp.server({}, function () {
    browserSync({
      proxy: '127.0.0.1:8000',
    });
  });
  gulp.watch('app/sass/**/*.scss', ['css']);
  gulp.watch('app/**/*.php', browserSync.reload);
  gulp.watch('app/**/*.html', browserSync.reload);
  gulp.watch('app/img/**/*', ['img']);
});

gulp.task('css', function () {
  return gulp.src('./app/sass/**/*.scss')
    .pipe(sass.sync().on('error', sass.logError))
    .pipe(sass())
    .pipe(gulp.dest('./assets/css'))

    .pipe(shorthand())

    .pipe(concat('combined.css'))

    .pipe(cleanCss())

    .pipe(minify({
      minify: true,
      minifyHTML: {
        collapseWhitespace: true,
        conservativeCollapse: true,
      },
      minifyJS: {
        sourceMap: true
      },
      minifyCSS: true,
      getKeptComment: function (content, filePath) {
        var m = content.match(/\/\*![\s\S]*?\*\//img);
        return m && m.join('\n') + '\n' || '';
      }
    }))

    .pipe(gulp.dest('assets/css'))

    .pipe(browserSync.reload({
      stream: true
    }))
});

gulp.task('img', function () {
  gulp.src('app/img/**/*')
    .pipe(imagemin({ verbose: true }))
    .pipe(gulp.dest('assets/img'))
});

gulp.task('default', ['serve', 'img']);