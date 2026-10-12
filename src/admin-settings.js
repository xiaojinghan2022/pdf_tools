import axios from '@nextcloud/axios'
import { confirm } from '@nextcloud/dialogs'
import { translate as t } from '@nextcloud/l10n'
import { generateUrl } from '@nextcloud/router'

import '@nextcloud/dialogs/style.css'

const urlInput = document.getElementById('stirling-url')
const keyInput = document.getElementById('stirling-api-key')
const statusEl = document.getElementById('status-msg')
const saveBtn = document.getElementById('save-btn')
const testBtn = document.getElementById('test-btn')
const testApiKeyBtn = document.getElementById('test-api-key-btn')
const clearBtn = document.getElementById('clear-api-key-btn')

let hasApiKey = false
/**
 * 更新Api Key输入框显示
 */
function updateKeyUi() {
	if (hasApiKey) {
		clearBtn.hidden = false
		keyInput.placeholder = t('pdf_tools', '(unchanged)')
	} else {
		clearBtn.hidden = true
		keyInput.placeholder = t('pdf_tools', 'Input your Api Key')
	}
}
/**
 * 加载已有配置
 */
async function load() {
	try {
		const { data } = await axios.get(generateUrl('/apps/pdf_tools/api/admin/settings'))
		urlInput.value = data.stirlingUrl || ''
		hasApiKey = data.hasApiKey
		updateKeyUi()
	} catch (e) {
		statusEl.textContent = e.response?.data?.message || t('pdf_tools', 'Settings load failed')
	}
}

// 保存
saveBtn.addEventListener('click', async () => {
	const payload = { stirlingUrl: urlInput.value }
	const newKey = keyInput.value
	if (newKey) {
		payload.stirlingApiKey = newKey
	}

	try {
		await axios.post(generateUrl('/apps/pdf_tools/api/admin/settings'), payload)
		statusEl.textContent = t('pdf_tools', 'Settings saved')
		if (newKey) {
			hasApiKey = true
			keyInput.value = ''
			updateKeyUi()
		}
	} catch (e) {
		statusEl.textContent = e.response?.data?.message || t('pdf_tools', 'Failed to save settings')
	}
})

// 测试连接
testBtn.addEventListener('click', async () => {
	try {
		const { data } = await axios.post(
			generateUrl('/apps/pdf_tools/api/admin/settings/test'),
			{ stirlingUrl: urlInput.value },
		)
		statusEl.textContent = data.message
	} catch (e) {
		statusEl.textContent = e.response?.data?.message || t('pdf_tools', 'Connection failed')
	}
})

// 测试 API Key
testApiKeyBtn.addEventListener('click', async () => {
	try {
		const { data } = await axios.post(
			generateUrl('/apps/pdf_tools/api/admin/settings/test-api-key'),
			{ stirlingUrl: urlInput.value, stirlingApiKey: keyInput.value },
		)
		statusEl.textContent = data.message
	} catch (e) {
		statusEl.textContent = e.response?.data?.message || t('pdf_tools', 'API Key test failed')
	}
})

// 清除 API Key
clearBtn.addEventListener('click', async () => {
	if (!await confirm({
		name: t('pdf_tools', 'Clear API key'),
		text: t('pdf_tools', 'Clear the stored API key?'),
		type: 'warning',
	})) {
		return
	}
	try {
		await axios.delete(generateUrl('/apps/pdf_tools/api/admin/settings/api-key'))
		hasApiKey = false
		keyInput.value = ''
		updateKeyUi()
		statusEl.textContent = t('pdf_tools', 'API Key cleared')
	} catch (e) {
		statusEl.textContent = e.response?.data?.message || t('pdf_tools', 'API Key clear failed')
	}
})

load()
