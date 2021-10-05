const path = require("path");
const webpack = require("webpack");
const autoprefixer = require("autoprefixer");

module.exports = {
  entry: ["./src/js/index.tsx", "./src/css/main.scss"],
  output: {
    filename: "[name].js",
  },
  devtool: "inline-source-map",
  target: "web",
  module: {
    rules: [
      {
        test: /\.(tsx?|jsx?)$/,
        use: "ts-loader",
        exclude: /node_modules/,
      },
      {
        test: /\.(png|jpe?g|svg|gif)$/i,
        use: [
          {
            loader: "file-loader",
          },
        ],
      },
      {
        test: /\.(sass|less|css|scss)$/,
        test: /\.scss$/,
        use: [
          {
            loader: "file-loader",
            options: {
              name: "bundle.css",
            },
          },
          { loader: "extract-loader" },
          { loader: "css-loader" },
          {
            loader: "postcss-loader",
            options: {
              postcssOptions: {
                plugins: [autoprefixer()],
              },
            },
          },
          {
            loader: "sass-loader",
            options: {
              // Prefer Dart Sass
              implementation: require("sass"),

              // See https://github.com/webpack-contrib/sass-loader/issues/804
              webpackImporter: false,
              sassOptions: {
                includePaths: ["./node_modules"],
              },
            },
          },
        ],
      },
    ],
  },
  resolve: {
    extensions: [".tsx", ".ts", ".js", "jsx"],
  },
};
