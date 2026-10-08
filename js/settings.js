const form = document.querySelector('#pdf-tools-settings-form');
const saveButton = document.querySelector('#pdf-tools-save');
const testButton = document.querySelector('#pdf-tools-test');
const statusElement = document.querySelector('#pdf-tools-status');

const urlInput = document.querySelector(
    '#pdf-tools-stirling-url'
);

const apiKeyInput = document.querySelector(
    '#pdf-tools-stirling-api-key'
);

function setStatus(message, success = false) {
    statusElement.textContent = message;
    statusElement.style.color = success ? 'green' : 'red';
}

function getHeaders() {
    return {
        'Content-Type': 'application/json',
        'requesttoken': OC.requestToken,
    };
}

form?.addEventListener('submit', async (event) => {
    event.preventDefault();

    saveButton.disabled = true;
    setStatus('Saving...', true);

    try {
        const response = await fetch(
            OC.generateUrl('/apps/pdf_tools/settings'),
            {
                method: 'POST',
                headers: getHeaders(),
                body: JSON.stringify({
                    stirlingUrl: urlInput.value,
                    stirlingApiKey: apiKeyInput.value,
                }),
            }
        );

        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(
                data.error || 'Failed to save settings.'
            );
        }

        setStatus('Settings saved.', true);
    } catch (error) {
        setStatus(error.message);
    } finally {
        saveButton.disabled = false;
    }
});

testButton?.addEventListener('click', async () => {
    testButton.disabled = true;
    setStatus('Testing connection...', true);

    try {
        const response = await fetch(
            OC.generateUrl('/apps/pdf_tools/settings/test'),
            {
                method: 'POST',
                headers: getHeaders(),
                body: JSON.stringify({
                    stirlingUrl: urlInput.value,
                    stirlingApiKey: apiKeyInput.value,
                }),
            }
        );

        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(
                data.error || 'Connection failed.'
            );
        }

        setStatus(
            'Connected to Stirling-PDF successfully.',
            true
        );
    } catch (error) {
        setStatus(error.message);
    } finally {
        testButton.disabled = false;
    }
});