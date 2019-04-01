var path = require('path')
var webpack = require('webpack')
var UglifyJsPlugin = require('uglifyjs-webpack-plugin')
var HtmlWebpackPlugin = require('html-webpack-plugin')

module.exports = {
    entry: './src/main.js',
    plugins: [
        new HtmlWebpackPlugin({
            template: './indextemplate.html',
            filename: './index.html' //relative to root of the application
        })
    ],
    output: {
        path: path.resolve(__dirname, './dist'),
        publicPath: '/dist/',
        filename: 'build.js'
    },
    resolve: {
        extensions: ['*', '.js', '.vue', '.json'],
        alias: {
            'vue$': 'vue/dist/vue.esm.js',
            'public': path.resolve(__dirname, './public'),
            src: path.join(__dirname, './src'),
            assets: path.join(__dirname, './src/assets'),
            components: path.join(__dirname, './src/components'),
            scss: path.join(__dirname, './src/scss')
        }
    },
    module: {
        rules: [
            {
                test: /\.vue$/,
                loader: 'vue-loader',
                options: {
                    loaders: {
                        // Since sass-loader (weirdly) has SCSS as its default parse mode, we map
                        // the "scss" and "sass" values for the lang attribute to the right configs here.
                        // other preprocessors should work out of the box, no loader config like this necessary.
                        'scss': 'vue-style-loader!css-loader!sass-loader',
                        'sass': 'vue-style-loader!css-loader!sass-loader?indentedSyntax'
                    }
                    // other vue-loader options go here
                }
            },
            {
                test: /\.js$/,
                loader: 'babel-loader',
                exclude: /node_modules/
            },
            {
                test: /\.(png|jpg|gif|svg|woff2?|eot|ttf|otf)$/,
                loader: 'file-loader',
                options: {
                    objectAssign: 'Object.assign'
                }
            },
            {
                test: /\.css$/,
                loader: ['style-loader', 'css-loader']
            },
            {
                test: /\.styl$/,
                loader: ['style-loader', 'css-loader', 'stylus-loader']
            }
        ]
    },
    devServer: {
        historyApiFallback: true,
        noInfo: true,
        overlay: true
    },
    performance: {
        hints: false
    },
    devtool: '#eval-source-map'
}

if (process.env.NODE_ENV === 'development') {
    /*
    Use environment specific files (e.g.: Config.dev.js or config.prod.js)
    To use this behavior, import a file with the __ENV__
    It will then be mapped to the correct environment.
    E.g: import Config from 'src/Config.__ENV__.js' ===> import Config from 'src/Config.dev.js'
    */
    module.exports.plugins = (module.exports.plugins || []).concat([
        new webpack.NormalModuleReplacementPlugin(/(.*)__ENV__(\.*)/, function (resource) {
            resource.request = resource.request.replace(/__ENV__/, 'dev');
        })
    ])
}

if (process.env.NODE_ENV === 'production') {
    module.exports.devtool = '(none)'
    // http://vue-loader.vuejs.org/en/workflow/production.html
    module.exports.plugins = (module.exports.plugins || []).concat([

        /*
        Use environment specific files (e.g.: Config.dev.js or config.prod.js)
        To use this behavior, import a file with the __ENV__
        It will then be mapped to the correct environment.
        E.g: import Config from 'src/Config.__ENV__.js' ===> import Config from 'src/Config.dev.js'
        */
        new webpack.NormalModuleReplacementPlugin(/(.*)__ENV__(\.*)/, function (resource) {
            resource.request = resource.request.replace(/__ENV__/, 'prod');
        }),
        new webpack.DefinePlugin({
            'process.env': {
                NODE_ENV: '"production"'
            }
        }),
        new UglifyJsPlugin({
          uglifyOptions: {
            compress: {
              warnings: false,
              drop_debugger: true,
              drop_console: true
            },
            output: {
                comments: false
            }
          },
          sourceMap: true
        }),
        new webpack.LoaderOptionsPlugin({
            minimize: true
        })
    ])
}
