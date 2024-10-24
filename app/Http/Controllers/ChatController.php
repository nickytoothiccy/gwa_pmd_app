<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    private $pythonPath;
    private $scriptPath;

    public function __construct()
    {
        // Update this path to your virtual environment's Python interpreter
        $this->pythonPath = base_path('.venv/Scripts/python');
        $this->scriptPath = base_path('app/Services/PmdAIService.py');

        // Check if the Python interpreter exists
        if (!file_exists($this->pythonPath)) {
            Log::error('Python interpreter not found at: ' . $this->pythonPath);
            // Fallback to system Python if virtual environment is not found
            $this->pythonPath = 'python';
        }
    }

    public function index()
    {
        return view('help');
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'conversation_id' => 'nullable|string',
            'image_data' => 'nullable|string',
        ]);

        $message = $request->input('message');
        $conversationId = $request->input('conversation_id') ?? 'null';
        $imageData = $request->input('image_data');

        $command = [
            $this->pythonPath,
            $this->scriptPath,
            'send_message',
            $conversationId,
            $message
        ];

        if ($imageData) {
            $command[] = $imageData;
        }

        Log::info('Executing Python command', ['command' => implode(' ', $command)]);

        $result = $this->executeCommand($command);

        Log::info('Python command result', ['result' => $result]);

        return response()->json($result);
    }

    public function createConversation(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $name = $request->input('name');
        $command = [
            $this->pythonPath,
            $this->scriptPath,
            'create_conversation',
            $name
        ];

        $result = $this->executeCommand($command);

        return response()->json($result);
    }

    public function getConversations()
    {
        $command = [
            $this->pythonPath,
            $this->scriptPath,
            'get_conversations'
        ];

        $result = $this->executeCommand($command);

        return response()->json($result);
    }

    public function exportUsage()
    {
        $command = [
            $this->pythonPath,
            $this->scriptPath,
            'get_total_usage'
        ];

        $result = $this->executeCommand($command);

        if (!isset($result['tokens_up']) || !isset($result['tokens_down']) || !isset($result['total_cost'])) {
            return response()->json(['error' => 'Invalid usage data received'], 500);
        }

        $data = [
            ['Metric', 'Value'],
            ['Tokens Up', $result['tokens_up']],
            ['Tokens Down', $result['tokens_down']],
            ['Total API Cost', '$' . number_format($result['total_cost'], 4)]
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            foreach ($data as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="AIHelp_usage_data.csv"',
        ]);
    }

    private function executeCommand(array $command)
    {
        $currentDir = getcwd();
        Log::info('Current working directory', ['directory' => $currentDir]);
        Log::info('Executing command', ['command' => implode(' ', $command)]);

        $process = proc_open($command, [
            0 => ["pipe", "r"],
            1 => ["pipe", "w"],
            2 => ["pipe", "w"]
        ], $pipes, $currentDir);

        if (!is_resource($process)) {
            Log::error('Failed to execute Python script', ['command' => implode(' ', $command)]);
            return ['error' => 'Failed to execute command'];
        }

        $output = stream_get_contents($pipes[1]);
        $error = stream_get_contents($pipes[2]);
        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        Log::info('Command execution completed', [
            'command' => implode(' ', $command),
            'output' => $output,
            'error' => $error,
            'exit_code' => $exitCode
        ]);

        if ($exitCode !== 0) {
            Log::error('Python script execution failed', [
                'command' => implode(' ', $command),
                'error' => $error,
                'output' => $output,
                'exit_code' => $exitCode
            ]);
            return ['error' => 'Command execution failed: ' . $error];
        }

        Log::info('Raw output from Python script', ['output' => $output]);

        $result = json_decode($output, true);

        if ($result === null) {
            Log::error('Failed to decode JSON output from Python script', [
                'command' => implode(' ', $command),
                'output' => $output
            ]);
            return ['error' => 'Failed to process the response: ' . $output];
        }

        Log::info('Decoded JSON result', ['result' => $result]);

        // Check if the result contains the expected 'message' key
        if (!isset($result['message']) && !isset($result['error'])) {
            Log::error('Python script output does not contain a message or error', [
                'command' => implode(' ', $command),
                'output' => $output
            ]);
            return ['error' => 'Invalid response format from the server'];
        }

        return $result;
    }
}