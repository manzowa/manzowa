const path = require('path');

module.exports = {
  entry: {
    'liste': './src/pages/liste.jsx',
    'show': './src/pages/show.jsx'
  },
  output: {
    filename: '[name].bundle.js',
    path: path.resolve(__dirname, '../public/js/bundle'),
    clean: true,  
  },
  mode: 'development',
  module: {
    rules: [
      {
        test: /\.jsx?$/,
        exclude: /node_modules/,
        use: 'babel-loader',
      }
    ]
  },
  resolve: {
    extensions: ['.js', '.jsx'],
  }
};
