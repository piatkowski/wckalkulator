const fs = require('fs');
const path = require('path');
const sass = require('sass');
const CleanCSS = require('clean-css');
const terser = require('terser');

const root = path.resolve(__dirname, '..');
const cssDirectory = path.join(root, 'assets', 'css');
const jsDirectory = path.join(root, 'assets', 'js');

function sourceFiles(directory, extension) {
    return fs.readdirSync(directory)
        .filter((filename) => filename.endsWith(extension))
        .sort();
}

function compileSass() {
    sourceFiles(cssDirectory, '.sass').forEach((filename) => {
        const input = path.join(cssDirectory, filename);
        const output = path.join(cssDirectory, filename.replace(/\.sass$/, '.css'));
        const result = sass.compile(input, {
            style: 'expanded',
            sourceMap: true,
            sourceMapIncludeSources: true
        });

        fs.writeFileSync(output, result.css.trimEnd() + '\n/*# sourceMappingURL=' + path.basename(output) + '.map */\n');
        fs.writeFileSync(output + '.map', JSON.stringify(result.sourceMap));
        console.log('Sass:', path.relative(root, input), '->', path.relative(root, output));
    });
}

function minifyCss() {
    const minifier = new CleanCSS({ level: 2 });

    sourceFiles(cssDirectory, '.css')
        .filter((filename) => !filename.endsWith('.min.css'))
        .forEach((filename) => {
            const input = path.join(cssDirectory, filename);
            const output = path.join(cssDirectory, filename.replace(/\.css$/, '.min.css'));
            const result = minifier.minify(fs.readFileSync(input, 'utf8'));

            if (result.errors.length > 0) {
                throw new Error(filename + ': ' + result.errors.join('; '));
            }

            fs.writeFileSync(output, result.styles + '\n');
            console.log('CSS:', path.relative(root, input), '->', path.relative(root, output));
        });
}

async function minifyJavaScript() {
    for (const filename of sourceFiles(jsDirectory, '.js').filter((name) => !name.endsWith('.min.js'))) {
        const input = path.join(jsDirectory, filename);
        const output = path.join(jsDirectory, filename.replace(/\.js$/, '.min.js'));
        const result = await terser.minify(fs.readFileSync(input, 'utf8'), {
            compress: true,
            mangle: true,
            format: {
                comments: /@license|@preserve|^!/
            }
        });

        if (result.error) {
            throw result.error;
        }

        fs.writeFileSync(output, result.code + '\n');
        console.log('JS:', path.relative(root, input), '->', path.relative(root, output));
    }
}

async function build() {
    compileSass();
    minifyCss();
    await minifyJavaScript();
}

build().catch((error) => {
    console.error(error);
    process.exitCode = 1;
});
