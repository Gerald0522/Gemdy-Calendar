import { readdirSync } from 'node:fs';
import { join } from 'node:path';
import { fileURLToPath } from 'node:url';
import { defineConfig } from 'vite';

const root = fileURLToPath(new URL('../public/frontend/', import.meta.url));
const pagesCommit = process.env.CF_PAGES_COMMIT_SHA;

export default defineConfig({
    root,
    base: './',
    publicDir: false,
    plugins: [{
        name: 'pages-deployment-version',
        generateBundle() {
            if (process.env.CF_PAGES === '1' && pagesCommit) {
                this.emitFile({
                    type: 'asset',
                    fileName: 'deployment-version.json',
                    source: JSON.stringify({ commit: pagesCommit }),
                });
            }
        },
    }],
    build: {
        outDir: '../../dist-web',
        emptyOutDir: true,
        rollupOptions: {
            input: readdirSync(root)
                .filter((file) => file.endsWith('.html'))
                .map((file) => join(root, file)),
        },
    },
});
