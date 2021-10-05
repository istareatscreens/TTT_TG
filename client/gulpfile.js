const gulp = require("gulp");
const concat = require("gulp-concat");
const sourcemaps = require("gulp-sourcemaps");
const postcss = require("gulp-postcss");
const cssnano = require("cssnano");
const browserSync = require("browser-sync");
const livereload = require("gulp-livereload");
const autoprefixer = require("autoprefixer");
const { src, series, parallel, dest } = require("gulp");
const webpack = require("webpack-stream");
const sass = require("gulp-sass")(require("sass"));
const htmlmin = require("gulp-htmlmin");
const del = require("del");

const output = "./public/";
const jsPath = "./src/js/**/*.*";
const cssPath = "./src/css/**/*";
const htmlPath = "./src/html/**/*";

//Production
function jsTaskProd() {
  return src([jsPath, "!node_modules"])
    .pipe(webpack(require("./webpack.prod.js")))
    .pipe(dest(output));
}

function cleanTask() {
  return del(["public/**/*"]);
}

//develop
function jsTask() {
  return src([jsPath, "!node_modules"])
    .pipe(webpack(require("./webpack.dev.js")))
    .pipe(browserSync.stream())
    .pipe(dest(output));
}

function copyHtml() {
  return src([htmlPath])
    .pipe(htmlmin({ collapseWhitespace: true, removeComments: true }))
    .pipe(browserSync.stream())
    .pipe(gulp.dest("public"));
}

function cssTask() {
  return src([cssPath + ".scss", cssPath + ".css"])
    .pipe(sourcemaps.init())
    .pipe(webpack(require("./webpack.dev.js")))
    .pipe(browserSync.stream())
    .pipe(dest(output));
}

//function cssTask() {
//  return src([cssPath + ".scss", cssPath + ".css"])
//    .pipe(sourcemaps.init())
//    .pipe(sass({ includePaths: ["./node_modules"] }).on("error", sass.logError))
//    .pipe(concat("style.css"))
//    .pipe(postcss([autoprefixer(), cssnano()])) //not all plugins work with postcss only the ones mentioned in their documentation
//    .pipe(sourcemaps.write("."))
//    .pipe(browserSync.stream())
//    .pipe(dest(output));
//}
//
function watchTask() {
  browserSync.init({
    server: {
      baseDir: "./public/",
    },
  });
  livereload.listen();
  gulp.watch([cssPath, jsPath, htmlPath], parallel(cssTask, jsTask, copyHtml));
  gulp.watch(htmlPath).on("change", browserSync.reload);
  gulp.watch(cssPath).on("change", browserSync.reload);
  gulp.watch(jsPath).on("change", browserSync.reload);
}

//BUILD Web Production
exports.default = series(parallel(cleanTask, jsTaskProd, cssTask, copyHtml));

//Develop Web
exports.watch = series(parallel(jsTask, cssTask, copyHtml), watchTask);
