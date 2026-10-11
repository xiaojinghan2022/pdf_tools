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
	const { data } = await axios.get(generateUrl('/apps/pdf_tools/api/settings'))
	urlInput.value = data.url || ''
	if (data.hasApiKey) {
		keyInput.placeholder = t('pdf_tools', '(unchanged)')
	}
}

// 保存
document.getElementById('save-btn').addEventListener('click', async () => {
	const payload = { url: urlInput.value }
	if (keyInput.value) {
		payload.apiKey = keyInput.value
	}

	try {
		await axios.post(generateUrl('/apps/pdf_tools/api/settings'), payload)
		statusEl.textContent = t('pdf_tools', '设置已保存')
	} catch {
		statusEl.textContent = t('pdf_tools', '保存失败')
	}
})

// 测试
document.getElementById('test-btn').addEventListener('click', async () => {
	try {
		const { data } = await axios.post(
			generateUrl('/apps/pdf_tools/api/settings/test'),
			{ url: urlInput.value, apiKey: keyInput.value },
		)
		statusEl.textContent = data.message
	} catch (e) {
		statusEl.textContent = e.response?.data?.message || t('pdf_tools', '连接失败')
	}
})

load()
