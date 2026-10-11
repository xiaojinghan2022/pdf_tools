import { defineConfig } from 'vite'

export default defineConfig(({ mode }) => ({
	build: {
		outDir: 'js',
		emptyOutDir: false,
		rollupOptions: {
			input: {
				'admin-settings': 'src/admin-settings.js',
				tools: 'src/tools.js',
			},
			output: {
				entryFileNames: '[name].mjs',
				chunkFileNames: '[name]-[hash].mjs',
				assetFileNames: '[name].[ext]',
			},
			external: ['OC', 'OCP', 'OCA'],
		},
		minify: mode === 'production',
		sourcemap: mode !== 'production',
	},
}))
