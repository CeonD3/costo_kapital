//HTML
import htmlmin from 'gulp-htmlmin'

//CSS
import sass from 'gulp-sass'
import autoprefixer from 'gulp-autoprefixer'

//JS
import gulp from 'gulp'
import babel from 'gulp-babel'
import terser from 'gulp-terser'

//Common
import concat from 'gulp-concat'


//variables/constantes

gulp.task('style', () => {
    return gulp
        .src('./public/assets-gulp/scss/**/*.scss')
        .pipe(sass({
            outputStyle: 'expanded',
            sourceComments: true
        }))
        .pipe(autoprefixer({
            versions: ['last 2 browser']
        }))
        .pipe(gulp.dest('./public/assets/css'))
})

gulp.task('babel-home',() => {
    return gulp
        .src([
            './public/assets-gulp/es6/utilitarian/**/*.js',
            './public/assets-gulp/es6/home/**/*.js'
        ])
        .pipe(concat('main.min.js'))
        .pipe(babel())
        .pipe(terser())
        .pipe(gulp.dest('./public/assets/js'))
})

gulp.task('babel-admin',() => {
    return gulp
        .src([
            './public/assets-gulp/es6/utilitarian/**/*.js',
            './public/assets-gulp/es6/admin/**/*.js'
        ])
        .pipe(concat('admin_landing.min.js'))
        .pipe(babel())
        .pipe(terser())
        .pipe(gulp.dest('./public/assets/js'))
})

gulp.task('default',() => {
    gulp.watch('./public/assets-gulp/es6/**/*.js',gulp.parallel('babel-home'))
    gulp.watch('./public/assets-gulp/es6/**/*.js',gulp.parallel('babel-admin'))
    gulp.watch('./public/assets-gulp/scss/**/*.scss',gulp.parallel('style'))
})

gulp.task('html-min',() => {
    return gulp
        .src('./public/assets-gulp/html/*.html')
        .pipe(concat('main.html'))
        .pipe(htmlmin({
            collapseWhitespace: true,
            removeComments: true
        }))
        .pipe(gulp.dest('./views/'))
})