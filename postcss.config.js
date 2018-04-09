const pkg = require('./composer.json');

module.exports = (ctx) => ({
    plugins: {
        'autoprefixer': {},
        'postcss-assets': { loadPaths: ['./templates/default/src'] },
        'postcss-replace': { data: pkg },
        'cssnano': ctx.env === 'prod' ? {} : false
    }
});
