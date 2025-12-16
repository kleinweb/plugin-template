import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import { resolve } from 'path';
import fs from 'fs';

// Write hot file for PHP to detect dev server
const hotFile = () => ({
    name: 'hot-file',
    configureServer(server) {
        const hotPath = resolve(__dirname, 'public/build/hot');
        
        server.httpServer?.once('listening', () => {
            const address = server.httpServer?.address();
            const protocol = server.config.server.https ? 'https' : 'http';
            const host = typeof address === 'object' ? address?.address : 'localhost';
            const port = typeof address === 'object' ? address?.port : 5173;
            
            fs.mkdirSync(resolve(__dirname, 'public/build'), { recursive: true });
            fs.writeFileSync(hotPath, `${protocol}://${host}:${port}`);
        });
        
        // Clean up on exit
        const cleanup = () => {
            if (fs.existsSync(hotPath)) {
                fs.unlinkSync(hotPath);
            }
        };
        
        process.on('exit', cleanup);
        process.on('SIGINT', () => {
            cleanup();
            process.exit();
        });
        process.on('SIGTERM', () => {
            cleanup();
            process.exit();
        });
    },
});

export default defineConfig({
    plugins: [
        react(),
        hotFile(),
    ],
    
    base: './',
    
    build: {
        outDir: 'public/build',
        emptyOutDir: true,
        manifest: true,
        rollupOptions: {
            input: {
                settings: resolve(__dirname, 'resources/js/settings/index.tsx'),
                editor: resolve(__dirname, 'resources/js/editor/index.tsx'),
                frontend: resolve(__dirname, 'resources/js/frontend/index.ts'),
            },
            output: {
                entryFileNames: 'assets/[name]-[hash].js',
                chunkFileNames: 'assets/[name]-[hash].js',
                assetFileNames: 'assets/[name]-[hash][extname]',
            },
        },
    },
    
    server: {
        host: 'localhost',
        port: 5173,
        strictPort: true,
        cors: true,
        hmr: {
            host: 'localhost',
        },
    },
    
    resolve: {
        alias: {
            '@': resolve(__dirname, 'resources/js'),
        },
    },
});
