<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terminal Log Interface</title>
    <style>
        body {
            background-color: #1a1a1a;
            color: #00ff00;
            font-family: 'Courier New', monospace;
            margin: 0;
            padding: 20px;
        }
        
        .terminal-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .terminal-header {
            background-color: #333;
            padding: 10px 15px;
            border-radius: 5px 5px 0 0;
            border-bottom: 1px solid #555;
        }
        
        .terminal-title {
            color: #fff;
            font-size: 14px;
            margin: 0;
        }
        
        .terminal-body {
            background-color: #000;
            border: 1px solid #333;
            border-radius: 0 0 5px 5px;
            padding: 15px;
            min-height: 400px;
        }
        
        .log-output {
            background-color: #111;
            border: 1px solid #333;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 3px;
            max-height: 300px;
            overflow-y: auto;
            font-size: 12px;
            line-height: 1.4;
        }
        
        .log-line {
            margin-bottom: 5px;
        }
        
        .log-timestamp {
            color: #888;
        }
        
        .log-info {
            color: #00ff00;
        }
        
        .log-warning {
            color: #ffff00;
        }
        
        .log-error {
            color: #ff0000;
        }
        
        .command-input {
            background-color: #111;
            border: 1px solid #333;
            color: #00ff00;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            padding: 10px;
            width: 100%;
            border-radius: 3px;
            resize: vertical;
            min-height: 100px;
        }
        
        .command-input:focus {
            outline: none;
            border-color: #00ff00;
        }
        
        .execute-btn {
            background-color: #333;
            border: 1px solid #555;
            color: #fff;
            padding: 10px 20px;
            margin-top: 10px;
            border-radius: 3px;
            cursor: pointer;
            font-family: 'Courier New', monospace;
        }
        
        .execute-btn:hover {
            background-color: #555;
        }
        
        .notification {
            padding: 10px 15px;
            margin-bottom: 15px;
            border-radius: 3px;
            border-left: 4px solid;
        }
        
        .notification.success {
            background-color: #1a3d1a;
            border-left-color: #00ff00;
            color: #00ff00;
        }
        
        .notification.error {
            background-color: #3d1a1a;
            border-left-color: #ff0000;
            color: #ff0000;
        }
        
        .notification.warning {
            background-color: #3d3d1a;
            border-left-color: #ffff00;
            color: #ffff00;
        }
        
        .prompt {
            color: #00ff00;
            margin-bottom: 10px;
        }
        
        .clear-btn {
            background-color: #666;
            border: 1px solid #888;
            color: #fff;
            padding: 5px 10px;
            margin-left: 10px;
            border-radius: 3px;
            cursor: pointer;
            font-family: 'Courier New', monospace;
            font-size: 12px;
        }
        
        .clear-btn:hover {
            background-color: #888;
        }
    </style>
</head>
<body>
    <div class="terminal-container">
        <div class="terminal-header">
            <h3 class="terminal-title">Terminal Log Interface - Laravel Tool E-Kanban</h3>
        </div>
        
        <div class="terminal-body">
            <!-- Notification Area -->
            <div id="notifications"></div>
            
            <!-- Log Output Area -->
            <div class="log-output" id="logOutput">
                <div class="log-line">
                    <span class="log-timestamp">[{{ date('Y-m-d H:i:s') }}]</span>
                    <span class="log-info">Terminal Log Interface initialized</span>
                </div>
                <div class="log-line">
                    <span class="log-timestamp">[{{ date('Y-m-d H:i:s') }}]</span>
                    <span class="log-info">Ready to execute commands...</span>
                </div>
            </div>
            
            <!-- Command Input Area -->
            <div class="prompt">root@laravel-ekanban:~$ </div>
            <textarea 
                id="commandInput" 
                class="command-input" 
                placeholder="Enter your command here...\nExample: php artisan route:list\nExample: composer install\nExample: npm run dev"
            ></textarea>
            
            <div>
                <button class="execute-btn" onclick="executeCommand()">Execute Command</button>
                <button class="clear-btn" onclick="clearLog()">Clear Log</button>
            </div>
        </div>
    </div>
    
    <script>
        function addLogLine(message, type = 'info') {
            const logOutput = document.getElementById('logOutput');
            const timestamp = new Date().toISOString().replace('T', ' ').substr(0, 19);
            const logLine = document.createElement('div');
            logLine.className = 'log-line';
            logLine.innerHTML = `
                <span class="log-timestamp">[${timestamp}]</span>
                <span class="log-${type}">${message}</span>
            `;
            logOutput.appendChild(logLine);
            logOutput.scrollTop = logOutput.scrollHeight;
        }
        
        function showNotification(message, type = 'info') {
            const notifications = document.getElementById('notifications');
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.textContent = message;
            notifications.appendChild(notification);
            
            // Auto remove notification after 5 seconds
            setTimeout(() => {
                notification.remove();
            }, 5000);
        }
        
        function executeCommand() {
            const commandInput = document.getElementById('commandInput');
            const command = commandInput.value.trim();
            
            if (!command) {
                showNotification('Please enter a command', 'warning');
                return;
            }
            
            addLogLine(`Executing: ${command}`, 'info');
            
            // Simulate command execution
            fetch('/api/execute-command', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ command: command })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    addLogLine(data.output || 'Command executed successfully', 'info');
                    showNotification('Command executed successfully', 'success');
                } else {
                    addLogLine(`Error: ${data.error}`, 'error');
                    showNotification(`Error: ${data.error}`, 'error');
                }
            })
            .catch(error => {
                const errorMessage = `Network error: ${error.message}`;
                addLogLine(errorMessage, 'error');
                showNotification(errorMessage, 'error');
            });
            
            commandInput.value = '';
        }
        
        function clearLog() {
            const logOutput = document.getElementById('logOutput');
            logOutput.innerHTML = `
                <div class="log-line">
                    <span class="log-timestamp">[${new Date().toISOString().replace('T', ' ').substr(0, 19)}]</span>
                    <span class="log-info">Log cleared</span>
                </div>
            `;
        }
        
        // Allow Enter key to execute command (Ctrl+Enter for new line)
        document.getElementById('commandInput').addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.ctrlKey) {
                e.preventDefault();
                executeCommand();
            }
        });
    </script>
</body>
</html>