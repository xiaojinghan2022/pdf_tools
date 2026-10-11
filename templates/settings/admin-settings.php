<?php
?>

<div id="pdf-tools-admin-settings">
    <p>
        <label for="stirling-url">Stirling PDF URL</label>
        <input type="url" id="stirling-url" placeholder="https://stirling.example.com">
    </p>
    <p>
        <label for="stirling-api-key">API Key</label>
        <input type="password" id="stirling-api-key" placeholder="Input your Api Key">
    </p>
    <p>
        <button id="save-btn">Save</button>
        <button id="test-btn">Test Connection</button>
        <button id="test-api-key-btn">Test Api Key</button>
    </p>
    <p id="status-msg"></p>
</div>