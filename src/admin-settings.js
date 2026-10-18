import axios from '@nextcloud/axios'
import { showConfirmation } from '@nextcloud/dialogs'
import { translate as t } from '@nextcloud/l10n'
import { generateUrl } from '@nextcloud/router'

import '@nextcloud/dialogs/style.css'

const urlInput = document.getElementById('stirling-url')
const keyInput = document.getElementById('stirling-api-key')
const userInput = document.getElementById('server-proxy-auth-user')
const passInput = document.getElementById('server-proxy-auth-pass')
const tokenInput = document.getElementById('server-proxy-auth-token')
const typeInput = document.getElementById('server-proxy-auth-type')
const statusEl = document.getElementById('status-msg')
const saveBtn = document.getElementById('save-btn')
const testBtn = document.getElementById('test-btn')
const testApiKeyBtn = document.getElementById('test-api-key-btn')
const clearKeyBtn = document.getElementById('clear-api-key-btn')
const clearAuthBtn = document.getElementById('clear-server-proxy-auth-btn')
const advanceDetails = document.getElementById('advanced')
const basicParagraph = document.getElementById('server-proxy-auth-basic-p')
const bearerParagraph = document.getElementById('server-proxy-auth-bearer-p')

let hasApiKey = false
let hasServerProxyAuthUser = false
let hasServerProxyAuthPass = false
let hasServerProxyAuthToken = false
/**
 * 更新输入框显示
 */
function updateKeyUi() {
	if (hasApiKey) {
		clearKeyBtn.hidden = false
		keyInput.placeholder = t('pdf_tools', '(unchanged)')
	} else {
		clearKeyBtn.hidden = true
		keyInput.placeholder = t('pdf_tools', 'Input your Api Key')
	}
	if (hasServerProxyAuthUser) {
		userInput.placeholder = t('pdf_tools', '(unchanged)')
	} else {
		userInput.placeholder = t('pdf_tools', 'Input your Server Proxy Auth Username')
	}
	if (hasServerProxyAuthPass) {
		passInput.placeholder = t('pdf_tools', '(unchanged)')
	} else {
		passInput.placeholder = t('pdf_tools', 'Input your Server Proxy Auth Password')
	}
	if (hasServerProxyAuthToken) {
		tokenInput.placeholder = t('pdf_tools', '(unchanged)')
	} else {
		tokenInput.placeholder = t('pdf_tools', 'Input your Server Proxy Auth Token')
	}
	if (hasServerProxyAuthUser || hasServerProxyAuthPass || hasServerProxyAuthToken) {
		advanceDetails.open = true
	} else {
		advanceDetails.open = false
	}
}
/**
 * 加载已有配置
 */
async function load() {
	try {
		const { data } = await axios.get(generateUrl('/apps/pdf_tools/api/v1/admin/settings'))
		urlInput.value = data.stirlingUrl || ''
		hasApiKey = data.hasApiKey
		hasServerProxyAuthUser = data.hasServerProxyAuthUser
		hasServerProxyAuthPass = data.hasServerProxyAuthPass
		hasServerProxyAuthToken = data.hasServerProxyAuthToken
		typeInput.value = data.serverProxyAuthType || 'none'
		typeInput.dispatchEvent(new Event('change'))

		updateKeyUi()
	} catch (e) {
		statusEl.textContent = e.response?.data?.message || t('pdf_tools', 'Settings load failed')
	}
}

// 保存
saveBtn.addEventListener('click', async () => {
	if (!urlInput.value.trim()) {
		statusEl.textContent = t('pdf_tools', 'Url is required')
		return
	}
	const payload = { stirlingUrl: urlInput.value, serverProxyAuthType: typeInput.value }
	const optionalFields = [
		[keyInput.value, 'stirlingApiKey'],
		[userInput.value, 'serverProxyAuthUser'],
		[passInput.value, 'serverProxyAuthPass'],
		[tokenInput.value, 'serverProxyAuthToken'],
	]
	optionalFields.forEach(([value, keyName]) => {
		if (value) {
			payload[keyName] = value
		}
	})
	try {
		await axios.post(generateUrl('/apps/pdf_tools/api/v1/admin/settings'), { fields: payload })
		statusEl.textContent = t('pdf_tools', 'Settings saved')
		load()
	} catch (e) {
		statusEl.textContent = e.response?.data?.message || t('pdf_tools', 'Failed to save settings')
	}
})

// 认证类型切换
typeInput.addEventListener('change', (e) => {
	basicParagraph.hidden = e.target.value !== 'basic'
	bearerParagraph.hidden = e.target.value !== 'bearer'
})

// 测试连接
testBtn.addEventListener('click', async () => {
	if (!urlInput.value.trim()) {
		statusEl.textContent = t('pdf_tools', 'Url is required')
		return
	}
	try {
		const { data } = await axios.post(
			generateUrl('/apps/pdf_tools/api/v1/admin/settings/test'),
			{ fields: {
				stirlingUrl: urlInput.value,
				serverProxyAuthUser: userInput.value,
				serverProxyAuthPass: passInput.value,
				serverProxyAuthType: typeInput.value,
				serverProxyAuthToken: tokenInput.value,
			}},
		)
		statusEl.textContent = data.message
	} catch (e) {
		statusEl.textContent = e.response?.data?.message || t('pdf_tools', 'Connection failed')
	}
})

// 测试 API Key
testApiKeyBtn.addEventListener('click', async () => {
	if (!urlInput.value.trim()) {
		statusEl.textContent = t('pdf_tools', 'Url is required')
		return
	}
	try {
		const { data } = await axios.post(
			generateUrl('/apps/pdf_tools/api/v1/admin/settings/test-api-key'),
			{ fields: {
				stirlingUrl: urlInput.value,
				stirlingApiKey: keyInput.value,
				serverProxyAuthUser: userInput.value,
				serverProxyAuthPass: passInput.value,
				serverProxyAuthType: typeInput.value,
				serverProxyAuthToken: tokenInput.value,
			}},
		)
		statusEl.textContent = data.message
	} catch (e) {
		statusEl.textContent = e.response?.data?.message || t('pdf_tools', 'API Key test failed')
	}
})

// 清除 API Key
clearKeyBtn.addEventListener('click', async () => {
	if (!await showConfirmation({
		name: t('pdf_tools', 'Clear API key'),
		text: t('pdf_tools', 'Clear the stored API key?'),
		type: 'warning',
	})) {
		return
	}
	try {
		await axios.delete(
			generateUrl('/apps/pdf_tools/api/v1/admin/settings/config-value'),
			{ data: { clearValue: ['stirlingApiKey'] } },
		)
		hasApiKey = false
		keyInput.value = ''
		updateKeyUi()
		statusEl.textContent = t('pdf_tools', 'API Key cleared')
	} catch (e) {
		statusEl.textContent = e.response?.data?.message || t('pdf_tools', 'API Key clear failed')
	}
})

// 清除反代认证项
clearAuthBtn.addEventListener('click', async () => {
	if (!await showConfirmation({
		name: t('pdf_tools', 'Clear Server proxy Auth'),
		text: t('pdf_tools', 'Clear all Server proxy Auth settings?'),
		type: 'warning',
	})) {
		return
	}
	try {
		await axios.delete(
			generateUrl('/apps/pdf_tools/api/v1/admin/settings/config-value'),
			{ data: { clearValue: ['serverProxyAuthUser', 'serverProxyAuthPass', 'serverProxyAuthToken', 'serverProxyAuthType'] } },
		)
		hasServerProxyAuthUser = false
		hasServerProxyAuthPass = false
		hasServerProxyAuthToken = false
		userInput.value = ''
		passInput.value = ''
		tokenInput.value = ''
		typeInput.value = 'none'
		typeInput.dispatchEvent(new Event('change'))
		updateKeyUi()
		statusEl.textContent = t('pdf_tools', 'Server proxy auth cleared')
	} catch (e) {
		statusEl.textContent = e.response?.data?.message || t('pdf_tools', 'Server proxy auth clear failed')
	}
})

load()
