"use strict";

var config = require('./config.json');
var project = require('./package.json');

var gulp = require("gulp");
var gm = require("gulp-gm");
var gulpif = require("gulp-if");
var less = require("gulp-less");
var newer = require("gulp-newer");
var sftp = require("gulp-sftp");
var spritesmith = require("gulp.spritesmith");

gulp.task("img", ["img-cards", "img-cards-sprite", "img-game-box", "img-game-icon"], function() {
  return gulp.src(["src/img/help_click.png", "src/img/help_info.png", "src/img/rounded_b.png"])
    .pipe(newer("build/img/"))
    .pipe(gulp.dest("build/img/"))
    .pipe(sftp({
      host: config.boardgamearena.sftp_host,
      port: config.boardgamearena.sftp_port,
      remotePath: "/pleasant/img/",
      auth: "boardgamearena",
      authFile: "sftp.json"
    }));
});

gulp.task("img-cards", function() {
  return gulp.src(["src/img/card_*.png"])
    .pipe(newer("build/img/"))
    .pipe(gulpif(false, gm(function(img) {
      return img.type("Grayscale");
    })))
    .pipe(gm(function(img) {
      return img.resize(config.pleasant.card_width, config.pleasant.card_height);
    }))
    .pipe(gulp.dest("build/img/"));
});

gulp.task("img-cards-sprite", ["img-cards"], function() {
  return gulp.src(["build/img/card_*.png"])
    .pipe(spritesmith({
      imgName: 'img/cards.png',
      cssName: 'cards.less',
      algorithm: "left-right",
      algorithmOpts: { sort: false }
    }))
    .pipe(gulp.dest("build/"));
});

gulp.task("img-game-box", function() {
  return gulp.src(["src/img/game_box.png"])
    .pipe(newer("build/img/"))
    .pipe(gm(function(img) {
      return img.resize(config.boardgamearena.game_box_size, config.boardgamearena.game_box_size)
        .background("transparent")
        .gravity("Center")
        .extent(config.boardgamearena.game_box_size, config.boardgamearena.game_box_size);
    }))
    .pipe(gulp.dest("build/img/"))
    .pipe(sftp({
      host: config.boardgamearena.sftp_host,
      port: config.boardgamearena.sftp_port,
      remotePath: "/pleasant/img/",
      auth: "boardgamearena",
      authFile: "sftp.json"
    }));
});

gulp.task("img-game-icon", function() {
  return gulp.src(["src/img/game_icon.png"])
    .pipe(newer("build/img/"))
    .pipe(gm(function(img) {
      return img.resize(config.boardgamearena.game_icon_size);
    }))
    .pipe(gulp.dest("build/img/"))
    .pipe(sftp({
      host: config.boardgamearena.sftp_host,
      port: config.boardgamearena.sftp_port,
      remotePath: "/pleasant/img/",
      auth: "boardgamearena",
      authFile: "sftp.json"
    }));
});

gulp.task("js", function() {
  return gulp.src(["src/*.js"])
    .pipe(newer("build/"))
    .pipe(gulp.dest("build/"))
    .pipe(sftp({
      host: config.boardgamearena.sftp_host,
      port: config.boardgamearena.sftp_port,
      remotePath: "/pleasant/",
      auth: "boardgamearena",
      authFile: "sftp.json"
    }));
});

gulp.task("less", ["img-cards-sprite"], function() {
  return gulp.src(["src/*.less"])
    .pipe(newer({
       dest: "build/",
       ext: ".css"
     }))
    .pipe(less())
    .pipe(gulp.dest("build/"))
    .pipe(sftp({
      host: config.boardgamearena.sftp_host,
      port: config.boardgamearena.sftp_port,
      remotePath: "/pleasant/",
      auth: "boardgamearena",
      authFile: "sftp.json"
    }));
});

gulp.task("php", function() {
  return gulp.src(["src/*.php"])
    .pipe(newer("build/"))
    .pipe(gulp.dest("build/"))
    .pipe(sftp({
      host: config.boardgamearena.sftp_host,
      port: config.boardgamearena.sftp_port,
      remotePath: "/pleasant/",
      auth: "boardgamearena",
      authFile: "sftp.json"
    }));
});

gulp.task("sql", function() {
  return gulp.src(["src/*.sql"])
    .pipe(newer("build/"))
    .pipe(gulp.dest("build/"))
    .pipe(sftp({
      host: config.boardgamearena.sftp_host,
      port: config.boardgamearena.sftp_port,
      remotePath: "/pleasant/",
      auth: "boardgamearena",
      authFile: "sftp.json"
    }));
});

gulp.task("tpl", function() {
  return gulp.src(["src/*.tpl"])
    .pipe(newer("build/"))
    .pipe(gulp.dest("build/"))
    .pipe(sftp({
      host: config.boardgamearena.sftp_host,
      port: config.boardgamearena.sftp_port,
      remotePath: "/pleasant/",
      auth: "boardgamearena",
      authFile: "sftp.json"
    }));
});

gulp.task("default", ["img", "js", "less", "php", "sql", "tpl"]);
