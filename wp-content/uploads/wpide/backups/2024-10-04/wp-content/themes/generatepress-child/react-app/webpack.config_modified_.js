const path = require('path');

module.exports = {
  // Other configurations...
  entry: './src/index.js',
  output: {
    path: path.resolve(__dirname, 'build'), // This will create a 'build' folder in the react-app directory
    filename: 'bundle.js',
  },
  module: {
    rules: [
      {
        test: /\.(js|jsx)$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
        },
      },
    ],
  },
  resolve: {
    extensions: ['.js', '.jsx'],
  },
  mode: 'production',
};
