<?php
?>

<div id="pdf-tools-admin-settings">
    <p>
        <label for="stirling-url">Stirling PDF URL</label>
        <input type="url" id="stirling-url" placeholder="https://stirling.example.com" autocomplete="url">
    </p>
    <p>
        <label for="stirling-api-key">API Key</label>
        <input type="password" id="stirling-api-key" placeholder="Input your Api Key" autocomplete="new-password">
    </p>
    <details id="advanced">
        <summary>Advanced</summary>
        <p>
            <label for="server-proxy-auth-type">Auth Type</label>
            <select id="server-proxy-auth-type">
                <option value="none">None</option>
                <option value="basic">Basic</option>
                <option value="bearer">Bearer</option>
            </select>
        </p>
        <div id="server-proxy-auth-basic-p" hidden>
            <p>
                <label for="server-proxy-auth-user">Auth Username</label>
                <input type="text" id="server-proxy-auth-user" autocomplete="username">
            </p>
            <p>
                <label for="server-proxy-auth-pass">Auth Password</label>
                <input type="password" id="server-proxy-auth-pass" placeholder="Input your Server Proxy Auth Password" autocomplete="new-password">
            </p>
        </div>
        <p id="server-proxy-auth-bearer-p" hidden>
            <label for="server-proxy-auth-token">Auth Token</label>
            <input type="password" id="server-proxy-auth-token" placeholder="Input your Server Proxy Auth Token" autocomplete="new-password">
        </p>
        <button id="clear-server-proxy-auth-btn" hidden>Clear Server Proxy Auth Config</button>

    </details>
    <p>
        <button id="save-btn">Save</button>
        <button id="test-btn">Test Connection</button>
        <button id="test-api-key-btn">Test Api Key</button>
        <button id="clear-api-key-btn" hidden>Clear Api Key</button>
    </p>
    <p id="status-msg"></p>
</div>