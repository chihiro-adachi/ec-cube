"use strict";

const gulp = require("gulp");
const $ = require("gulp-load-plugins")();

const src = "assets/Eccube-Styleguide/assets/scss/**/*.scss";
const dest = "html/template/default/assets/css";

gulp.task("build", () => {
    let options = {
        sourceMap: true,
        includePaths: [
            "node_modules"
        ]
    };

    return gulp.src(src)
        .pipe($.plumber({
            errorHandler: $.notify.onError('<%= error.message %>')
        }))
        .pipe($.sourcemaps.init())
        .pipe($.sass(options))
        .pipe($.pleeease({
            autoprefixer: true,
            minifier: true,
            mqpacker: true
        }))
        .pipe($.sourcemaps.write())
        .pipe(gulp.dest(dest));
});
