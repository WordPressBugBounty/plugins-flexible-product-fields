const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

module.exports = (env, argv) => ({
  devtool: argv.mode === 'development' ? 'source-map' : false,
  entry: {
    'admin': './assets-src/scss/Admin.scss',
    'front': ['./assets-src/js/Front.js', './assets-src/scss/Front.scss'],
  },
  output: {
    path: path.resolve(__dirname, 'assets'),
    filename: 'js/[name].js',
  },
  resolve: {
    extensions: ['.js', '.jsx', '.scss'],
  },
  module: {
    rules: [
      {
        test: /\.(js|jsx)$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/preset-env', '@babel/preset-react'],
          },
        },
      },
      {
        test: /\.scss$/,
        exclude: /node_modules/,
        use: [
          MiniCssExtractPlugin.loader,
          'css-loader',
          {
            loader: 'sass-loader',
            options: {
              sassOptions: {
                includePaths: [path.resolve(__dirname, 'assets-src/scss')],
                importer: function(url, prev) {
                  if (url.endsWith('/*')) {
                    const glob = require('glob');
                    const path = require('path');
                    const fs = require('fs');
                    
                    const dir = url.slice(0, -2);
                    const fullPath = path.resolve(path.dirname(prev), dir);
                    
                    if (fs.existsSync(fullPath)) {
                      const files = glob.sync('*.scss', { cwd: fullPath });
                      const imports = files.map(file => `@import "${dir}/${file.replace('.scss', '')}";`).join('\n');
                      return { contents: imports };
                    }
                  }
                  return null;
                }
              },
            },
          },
        ],
      },
    ],
  },
  plugins: [
    new MiniCssExtractPlugin({
      filename: 'css/[name].css',
    }),
  ],
});
