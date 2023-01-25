const path = require('path');

module.exports = {
  entry: './src/index.js',
  mode: 'development',
  output: {
    filename: 'main.js',
    path: path.resolve(__dirname, 'web'),
  },
  devServer: {
    static: {
      directory: path.join(__dirname, 'web'),
    },
    compress: true,
    port: 8080,
  },
};