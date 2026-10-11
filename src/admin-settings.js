import axios from '@nextcloud/axios'
import { translate as t } from '@nextcloud/l10n'
import { generateUrl } from '@nextcloud/router'

const urlInput = document.getElementById('stirling-url')
const keyInput = document.getElementById('stirling-api-key')
const statusEl = document.getElementById('status-msg')

/**
 * 加载已有配置
 */
async function load() {
	const { data } = await axios.get(generateUrl('/apps/pdf_tools/admin/settings'))
	urlInput.value = data.stirlingUrl || ''
	if (data.hasApiKey) {
		keyInput.placeholder = t('pdf_tools', '(unchanged)')
	}
}

// 保存
document.getElementById('save-btn').addEventListener('click', async () => {
	const payload = { stirlingUrl: urlInput.value }
	if (keyInput.value) {
		payload.stirlingApiKey = keyInput.value
	}

	try {
		await axios.post(generateUrl('/apps/pdf_tools/admin/settings'), payload)
		statusEl.textContent = t('pdf_tools', 'Settings saved')
	} catch (e) {
		statusEl.textContent = e.response?.data?.message || t('pdf_tools', 'Failed to save settings')
	}
})

// 测试
document.getElementById('test-btn').addEventListener('click', async () => {
	try {
		const { data } = await axios.post(
			generateUrl('/apps/pdf_tools/admin/settings/test'),
			{ stirlingUrl: urlInput.value },
		)
		statusEl.textContent = data.message
	} catch (e) {
		statusEl.textContent = e.response?.data?.message || t('pdf_tools', 'Connection failed')
	}
})

document.getElementById('test-api-key-btn').addEventListener('click', async () => {
	try {
		const { data } = await axios.post(
			generateUrl('/apps/pdf_tools/admin/settings/test-api-key'),
			{ stirlingUrl: urlInput.value, stirlingApiKey: keyInput.value },
		)
		statusEl.textContent = data.message
	} catch (e) {
		statusEl.textContent = e.response?.data?.message || t('pdf_tools', 'Api Key test failed')
	}
})

load()
