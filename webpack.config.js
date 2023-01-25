const path = require('path');

module.exports = {
  entry: './src/index.js',
  mode: 'development',
  output: {
    filename: 'main.js',
    path: path.resolve(__dirname, 'examples'),
  },
  devServer: {
    static: {
      directory: path.join(__dirname, 'examples'),
    },
    compress: true,
    port: 8080,
  },
};