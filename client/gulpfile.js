const gulp = require("gulp");
const sourcemaps = require("gulp-sourcemaps");
const browserSync = require("browser-sync");
const livereload = require("gulp-livereload");
const { src, series, parallel, dest } = require("gulp");
const webpack = require("webpack-stream");
const rename = require("gulp-rename");
const del = require("del");

const output = "./public/";
const jsPath = "./src/js/**/*.*";
const cssPath = "./src/css/**/*";
const htmlPath = "./src/php/home.php";
const phpPath = "./src/php/**/*";
const imagePath = "./src/images/**/*";

function jsTask() {
  return src([jsPath, "!node_modules"])
    .pipe(webpack(require("./webpack.prod.js")))
    .pipe(dest(output));
}

function cleanTask() {
  return del(["public/**/*"]);
}

function jsDevTask() {
  return src([jsPath, "!node_modules"])
    .pipe(webpack(require("./webpack.dev.js")))
    .pipe(browserSync.stream())
    .pipe(dest(output));
}

function copyDevHtml() {
  return src([htmlPath])
    .pipe(rename({ basename: "index", extname: ".html" }))
    .pipe(browserSync.stream())
    .pipe(gulp.dest("public"));
}

function cssDevTask() {
  return src([cssPath + ".scss", cssPath + ".css"])
    .pipe(sourcemaps.init())
    .pipe(webpack(require("./webpack.dev.js")))
    .pipe(browserSync.stream())
    .pipe(dest(output));
}

function cssTask() {
  return src([cssPath + ".scss", cssPath + ".css"])
    .pipe(sourcemaps.init())
    .pipe(webpack(require("./webpack.prod.js")))
    .pipe(dest(output));
}

function copyPHP() {
  return src([phpPath]).pipe(dest(output));
}

function copyAssets() {
  return src([imagePath]);
}

function watchTask() {
  browserSync.init({
    server: {
      baseDir: "./public/",
      ghostMode: false,
    },
  });
  livereload.listen();
  gulp.watch(
    [cssPath, jsPath, htmlPath],
    parallel(cssDevTask, jsDevTask, copyDevHtml)
  );
  gulp.watch(htmlPath).on("change", browserSync.reload);
  gulp.watch(cssPath).on("change", browserSync.reload);
  gulp.watch(jsPath).on("change", browserSync.reload);
}

//BUILD Web Production
exports.default = series(
  parallel(cleanTask, jsTask, cssTask, copyPHP, copyAssets)
);

//Develop Web
exports.watch = series(
  parallel(jsDevTask, cssDevTask, copyDevHtml, copyAssets),
  watchTask
);
