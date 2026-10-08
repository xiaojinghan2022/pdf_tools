<div id="pdf-tools-admin-settings" class="section">
    <h2>PDF Tools</h2>

    <p>
        Configure the connection to your Stirling-PDF instance.
    </p>

    <form id="pdf-tools-settings-form">

        <p>
            <label for="pdf-tools-stirling-url">
                Stirling-PDF URL
            </label>
        </p>

        <p>
            <input
                type="url"
                id="pdf-tools-stirling-url"
                name="stirling_url"
                class="settings-input"
                placeholder="http://stirling-pdf:8080"
                value="<?php p($_['stirlingUrl']); ?>"
            >
        </p>

        <p>
            <label for="pdf-tools-stirling-api-key">
                API Key
            </label>
        </p>

        <p>
            <input
                type="password"
                id="pdf-tools-stirling-api-key"
                name="stirling_api_key"
                class="settings-input"
                value="<?php p($_['stirlingApiKey']); ?>"
                autocomplete="new-password"
            >
        </p>

        <p>
            <button
                type="submit"
                id="pdf-tools-save"
                class="primary"
            >
                Save
            </button>

            <span id="pdf-tools-status"></span>
        </p>

    </form>
</div>