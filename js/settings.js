import { generateUrl } from '@nextcloud/router';
import { showError, showSuccess } from '@nextcloud/dialogs';

const form = document.querySelector('#pdf-tools-settings-form');

if (form) {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const url = document.querySelector(
            '#pdf-tools-stirling-url'
        ).value;

        const apiKey = document.querySelector(
            '#pdf-tools-stirling-api-key'
        ).value;

        try {
            const response = await fetch(
                generateUrl('/apps/pdf_tools/settings'),
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        stirlingUrl: url,
                        stirlingApiKey: apiKey,
                    }),
                }
            );

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(
                    data.error || 'Failed to save settings.'
                );
            }

            showSuccess('PDF Tools settings saved.');
        } catch (error) {
            showError(error.message);
        }
    });
}