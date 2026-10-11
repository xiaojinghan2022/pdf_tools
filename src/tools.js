import { registerFileAction } from '@nextcloud/files'
import { translate as t } from '@nextcloud/l10n'
import iconSvg from '../img/app.svg?raw'

registerFileAction({
	id: 'pdf-tools-extract',
	displayName: () => t('pdf_tools', 'Extract with PDF Tools'),
	iconSvgInline: () => iconSvg,
	enabled: ({ nodes }) => {
		return nodes.length === 1 && nodes[0].mime === 'application/pdf'
	},
	async exec({ nodes }) {
		console.log('[pdf_tools] clicked', nodes[0])
		return true
	},
})
