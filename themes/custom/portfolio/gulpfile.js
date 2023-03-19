 // dont' forget config stuff in settings.local.php

'use strict';
var git = require('gulp-git');
var gulp = require('gulp');
var sass = require('gulp-sass');
var sourcemaps = require('gulp-sourcemaps');
var uglify = require('gulp-uglify');
var browserSync = require('browser-sync');
var runSequence = require('run-sequence');
var rename = require('gulp-rename');
var plumber = require ('gulp-plumber');
var gutil = require('gulp-util');
var del = require('del');
var autoprefixer = require('gulp-autoprefixer');
var urlAdjuster = require('gulp-css-url-adjuster');
var imagemin = require('gulp-imagemin');
var sassLint = require('gulp-sass-lint');

// Config defaults.
var config = {
  proxy_url: undefined
};

// Include local file for overriding config, don't fail if it doesn't exist.
try {
  config = require('./config/proxy_url.json');
} catch (error) {
}

// report errors but continue gulpin'
var onError = function (err) {
  gutil.beep();
  console.log(err.messageFormatted);
  this.emit('end'); // super crit
};

//////////////////////////////////////
// Begin Gulp Tasks
//////////////////////////////////////

////// DEVELOPMENT
//////////////////

// SASS compile task
var sassBuild = function() {
  return gulp
    .src('./src/sass/**/*.scss')
    .pipe(plumber({
      errorHandler: onError
    }))
    .pipe(sourcemaps.init({largeFile: true}))
    .pipe(sass())
    .pipe(autoprefixer({
      browsers: ['last 2 versions']
    }))
    .pipe(sourcemaps.write('./maps'))
    .pipe(gulp.dest('./dist/css'));
};

gulp.task('sass', sassBuild);

// Rename and move Javascript task
var jsBuild = function() {
  return gulp.src('./src/scripts/js/**/*.js')
    .pipe(plumber({
      errorHandler: onError
    }))
    .pipe(rename(function (path) {
      path.basename += ".min";
      path.extname = ".js";
    }))
    .pipe(gulp.dest('./dist/scripts/js'));
};

gulp.task('js', jsBuild);

// Move image assets task
var imageBuild = function() {
  return gulp.src('./src/img/**/*')
    .pipe(gulp.dest('./dist/img'));
};

gulp.task('images', imageBuild);


// Move font assets task
var fontBuild = function() {
  return gulp.src('./src/fonts/**/*')
    .pipe(gulp.dest('./dist/fonts'));
};

gulp.task('fonts', fontBuild);

// Watch Task (for new changes from the following)
gulp.task('watch', function() {
  gulp.watch('./src/sass/**/*.scss', ['sass']);
  gulp.watch('./src/scripts/js/**/*.js', ['js']);
  gulp.watch('./src/img/**/*', ['images']);
  gulp.watch('./src/fonts/**/*', ['fonts']);
});

// BrowserSync Task (refresh browser if files change)
gulp.task('browserSync', function() {
  browserSync.init([
    ('./dist/css/*.css'),
    ('./dist/scripts/js/**/*.js'),
    ('./dist/img/**/*'),
    ('./dist/fonts/**/*')
  ],{
     proxy: "http://10.18.14.80/napoleon.localhost/docroot"
  });
});

// Lint Sass
gulp.task('lint-sass', function () {
  return gulp.src('./src/sass/**/*.scss')
    .pipe(sassLint())
    .pipe(sassLint.format())
    // .pipe(sassLint.failOnError())
});

// Git rebase, fixcss and fixjs have to be completed first.
gulp.task('rebase-continue', ['fixcss', 'fixjs'], function() {
  git.exec({
    args: 'rebase --continue'
  });
});

// Recompile Sass and git add the files.
gulp.task('fix-css', function() {
  return sassBuild().pipe(git.add());
});

// Recompile JS and git add the files.
// Wait until fixcss runs, we don't want two git add operations happening at the
// same time.
// @todo Combine streams.
gulp.task('fix-js', ['fix-css'], function() {
  return jsBuild().pipe(git.add());
});

gulp.task('fix', ['fix-css', 'fix-js', 'rebase-continue']);


// Server Tasks
gulp.task('default', function(callback) {
  runSequence('watch','browserSync',callback);
});


////// PRODUCTION
/////////////////

// Delete all files in dist directory in prep for final build
gulp.task('clean', function() {
  del([
      './dist/**/*'
  ]).then(paths => {
    console.log('Deleted files and folders: \n', paths.join('\n'));
  });
});

//  Build Task for production
gulp.task('build', function(){

  // rebuild images and mimify
  gulp.src('./src/img/**/*')
    .pipe(imagemin())
    .pipe(gulp.dest('./dist/img/'));

  // rebuild css
  gulp.src('./src/sass/**/*.scss')
    .pipe(sass({outputStyle: 'compressed'}))
    .pipe(autoprefixer({
      browsers: ['last 2 versions']
    }))
    .pipe(urlAdjuster({
      replace: ['../../src/img/','../../dist/img/'],
    }))
    .pipe(gulp.dest('./dist/css'));

  // rebuild and uglify js
  gulp.src('./src/scripts/js/**/*.js')
    .pipe(rename(function (path) {
      path.basename += ".min";
      path.extname = ".js"
    }))
    .pipe(uglify())
    .pipe(gulp.dest('./dist/scripts/js'));
});


// final task for production site
// gulp.task('build-prod', function(callback) {
//   runSequence('build-clean','build-all',callback);
// });
