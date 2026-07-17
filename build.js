const fs = require('fs');
const path = require('path');
const { ZipArchive } = require('archiver');

const root = __dirname;
const out = path.join(root, 'advik-optimizer.zip');
const excludeDirs = new Set(['node_modules', 'tests', '.git', '.vscode']);
const excludeFiles = new Set(['.gitignore', '.editorconfig', 'phpcs.xml', 'phpunit.xml',
  '.wp-env.json', 'package-lock.json', 'build-zip.ps1', 'build.js',
  'advik-optimizer.zip']);

const output = fs.createWriteStream(out);
const archive = new ZipArchive();
archive.pipe(output);

output.on('close', () => console.log(`Created ${out} (${archive.pointer()} bytes)`));
archive.on('error', err => { throw err; });

function walk(dir) {
  const entries = fs.readdirSync(dir, { withFileTypes: true });
  for (const e of entries) {
    const full = path.join(dir, e.name);
    const rel = path.relative(root, full).replace(/\\/g, '/');
    if (e.isDirectory()) {
      if (!excludeDirs.has(e.name) && !rel.startsWith('.')) walk(full);
    } else {
      if (!excludeFiles.has(e.name) && !rel.startsWith('.')) {
        archive.file(full, { name: rel });
      }
    }
  }
}

walk(root);
archive.finalize();
