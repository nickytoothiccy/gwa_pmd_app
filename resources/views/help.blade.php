@extends('layouts.app')

@section('content')
<div class="container-fluid" id="AIHelp-container">
    <div class="row">
        <div class="col-md-8">
            <div id="token-usage">
                <span id="tokens-up">↑ 0</span>
                <span id="tokens-down">↓ 0</span>
                <span id="cache-hits">Cache: ⚡ 0</span>
                <span id="api-cost">API Cost: $0.0000</span>
                <button id="export-usage" class="btn btn-sm btn-secondary">EXPORT</button>
            </div>
            <div id="AIHelp-display"></div>
            <div id="image-preview-container">
                <img id="image-preview" src="" alt="Image Preview" style="display: none; max-width: 100px; max-height: 100px;">
                <div id="image-analysis"></div>
            </div>
            <div id="input-container">
                <input type="text" id="user-input" placeholder="Type your message here...">
                <button id="send-button" class="btn btn-primary">Send</button>
                <button id="upload-image" class="btn btn-secondary">Upload Image</button>
            </div>
            <div id="conversation-container">
                <select id="conversation-dropdown"></select>
                <button id="new-conversation" class="btn btn-secondary">New Conversation</button>
            </div>
            <div id="status-label"></div>
        </div>
        <div class="col-md-4">
            <div id="debug-log">
                <h4>Debug Log</h4>
                <div id="debug-content"></div>
            </div>
        </div>
    </div>
</div>

<style>
    body {
        background-color: #1a1a1a;
        color: #e0e0e0;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    #AIHelp-container {
        max-width: 1200px;
        margin: 20px auto;
        padding: 20px;
        background-color: #2a2a2a;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    #AIHelp-display, #debug-content {
        height: 450px;
        overflow-y: auto;
        border: 1px solid #3a3a3a;
        background-color: #2d2d2d;
        padding: 15px;
        margin-bottom: 15px;
        border-radius: 8px;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    #debug-content {
        font-family: monospace;
        font-size: 0.9em;
    }
    #user-input {
        width: calc(100% - 220px);
        background-color: #3a3a3a;
        color: #ffffff;
        border: 1px solid #4a4a4a;
        padding: 10px;
        border-radius: 5px;
        transition: all 0.3s ease;
    }
    #user-input:focus {
        outline: none;
        box-shadow: 0 0 0 2px rgba(66, 153, 225, 0.5);
    }
    #input-container {
        margin-bottom: 15px;
        display: flex;
        gap: 10px;
    }
    #token-usage {
        margin-bottom: 15px;
        background-color: #333;
        padding: 10px;
        border-radius: 5px;
        font-size: 0.9em;
    }
    #token-usage span {
        margin-right: 15px;
        padding: 3px 8px;
        background-color: #444;
        border-radius: 3px;
    }
    .user-message {
        color: #ffffff;
        background-color: #3a3a3a;
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 10px;
    }
    .assistant-message {
        color: #a8e6cf;
        background-color: #2d2d2d;
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 10px;
    }
    #status-label {
        margin-top: 15px;
        font-style: italic;
        color: #888;
    }
    .btn {
        border-radius: 5px;
        transition: all 0.3s ease;
    }
    .btn-primary {
        background-color: #4299e1;
        border-color: #4299e1;
    }
    .btn-primary:hover {
        background-color: #3182ce;
        border-color: #3182ce;
    }
    .btn-secondary {
        background-color: #718096;
        border-color: #718096;
    }
    .btn-secondary:hover {
        background-color: #4a5568;
        border-color: #4a5568;
    }
    #conversation-dropdown {
        background-color: #3a3a3a;
        color: #ffffff;
        border: 1px solid #4a4a4a;
        border-radius: 5px;
        padding: 5px;
        margin-right: 10px;
    }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    const AIHelpDisplay = $('#AIHelp-display');
    const userInput = $('#user-input');
    const sendButton = $('#send-button');
    const uploadImageButton = $('#upload-image');
    const imagePreview = $('#image-preview');
    const imageAnalysis = $('#image-analysis');
    const conversationDropdown = $('#conversation-dropdown');
    const newConversationButton = $('#new-conversation');
    const statusLabel = $('#status-label');
    const tokensUpLabel = $('#tokens-up');
    const tokensDownLabel = $('#tokens-down');
    const cacheHitsLabel = $('#cache-hits');
    const apiCostLabel = $('#api-cost');
    const exportUsageButton = $('#export-usage');
    const debugContent = $('#debug-content');

    let currentConversationId = null;
    let currentImageData = null;

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    function displayMessage(role, content) {
        const messageElement = $('<div>').addClass(role + '-message').text(role.charAt(0).toUpperCase() + role.slice(1) + ': ' + content);
        AIHelpDisplay.append(messageElement);
        AIHelpDisplay.scrollTop(AIHelpDisplay[0].scrollHeight);
    }

    function logDebug(message) {
        const logElement = $('<div>').text(new Date().toISOString() + ': ' + message);
        debugContent.append(logElement);
        debugContent.scrollTop(debugContent[0].scrollHeight);
        console.log(message);
    }

    function sendMessage() {
        const message = userInput.val().trim();
        if (message) {
            displayMessage('user', message);
            userInput.val('');
            sendButton.prop('disabled', true);
            statusLabel.text('Generating response...');

            logDebug('Sending message: ' + message);
            $.ajax({
                url: '{{ route("AIHelp.send-message") }}',
                method: 'POST',
                data: {
                    message: message,
                    conversation_id: currentConversationId,
                    image_data: currentImageData
                },
                success: function(response) {
                    logDebug('Received response: ' + JSON.stringify(response));
                    if (response && response.message) {
                        displayMessage('assistant', response.message);
                        updateTokenUsage(response.tokens_up, response.tokens_down, response.cache_hits, response.cost);
                    } else {
                        logDebug('Invalid response from server: ' + JSON.stringify(response));
                        statusLabel.text('Error: Invalid response from server');
                    }
                    currentImageData = null;
                    imagePreview.hide();
                    imageAnalysis.empty();
                },
                error: function(xhr, status, error) {
                    logDebug('Error sending message: ' + error);
                    logDebug('XHR status: ' + status);
                    logDebug('XHR response: ' + xhr.responseText);
                    let errorMessage = 'Error: Failed to send message';
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMessage += ' - ' + xhr.responseJSON.error;
                    }
                    statusLabel.text(errorMessage);
                    displayMessage('assistant', 'An error occurred while processing your request. Please try again.');
                },
                complete: function() {
                    sendButton.prop('disabled', false);
                    statusLabel.text('');
                }
            });
        }
    }

    function updateTokenUsage(tokensUp, tokensDown, cacheHits, cost) {
        logDebug('Updating token usage: ' + tokensUp + ' up, ' + tokensDown + ' down, ' + cacheHits + ' cache hits, $' + cost.toFixed(4) + ' cost');
        tokensUpLabel.text('↑ ' + tokensUp);
        tokensDownLabel.text('↓ ' + tokensDown);
        cacheHitsLabel.text('Cache: ⚡ ' + cacheHits);
        apiCostLabel.text('API Cost: $' + cost.toFixed(4));
    }

    function loadConversations() {
        $.get('{{ route("AIHelp.get-conversations") }}', function(conversations) {
            logDebug('Loaded conversations: ' + JSON.stringify(conversations));
            conversationDropdown.empty();
            conversations.forEach(function(conversation) {
                conversationDropdown.append($('<option>').val(conversation.id).text(conversation.name));
            });
        }).fail(function(xhr, status, error) {
            logDebug('Error loading conversations: ' + error);
            statusLabel.text('Error: Failed to load conversations');
        });
    }

    sendButton.on('click', sendMessage);
    userInput.on('keypress', function(e) {
        if (e.which === 13) {
            sendMessage();
        }
    });

    uploadImageButton.on('click', function() {
        // TODO: Implement image upload functionality
        alert('Image upload functionality not implemented yet.');
    });

    newConversationButton.on('click', function() {
        const name = prompt('Enter a name for the new conversation:');
        if (name) {
            $.ajax({
                url: '{{ route("AIHelp.create-conversation") }}',
                method: 'POST',
                data: { name: name },
                success: function(response) {
                    logDebug('Created new conversation: ' + JSON.stringify(response));
                    currentConversationId = response.id;
                    loadConversations();
                    conversationDropdown.val(currentConversationId);
                    AIHelpDisplay.empty();
                },
                error: function(xhr, status, error) {
                    logDebug('Error creating conversation: ' + error);
                    alert('Failed to create new conversation');
                }
            });
        }
    });

    exportUsageButton.on('click', function() {
        window.location.href = '{{ route("AIHelp.export-usage") }}';
    });

    conversationDropdown.on('change', function() {
        currentConversationId = $(this).val();
        AIHelpDisplay.empty();
        // TODO: Load messages for the selected conversation
    });

    loadConversations();
    logDebug('AIHelp interface initialized');
});
</script>
@endsection