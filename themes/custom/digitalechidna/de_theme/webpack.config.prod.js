//webpack.config.js
var path = require('path');

module.exports = {
  mode: "production",
  entry: {
    main: "./src/scripts/js/main"
  },
  resolve: {
    extensions: [".webpack.js", ".web.js", ".ts", ".js"]
  },
  output: {
    publicPath: "/dist/scripts/",
    path: path.join(__dirname, "/dist/scripts/"),
    filename: '[name].webpack.min.js'
  },
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: ["babel-loader"]
      }
    ]
  }
};
