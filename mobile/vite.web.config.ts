import { readdirSync } from 'node:fs';
import { join } from 'node:path';
import { fileURLToPath } from 'node:url';
import { defineConfig } from 'vite';

const root = fileURLToPath(new URL('../public/frontend/', import.meta.url));

export default defineConfig({
    root,
    base: './',
    publicDir: false,
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
