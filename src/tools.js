import { showConfirmation } from '@nextcloud/dialogs'
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
		if (!await showConfirmation({
			name: t('pdf_tools', 'You click this button'),
			text: t('pdf_tools', '[pdf_tools] clicked, in {crtime}', { crtime: nodes[0].crtime }),
			type: 'warning',
		})) {
			return
		}
		return true
	},
})
