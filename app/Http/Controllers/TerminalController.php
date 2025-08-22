<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class TerminalController extends Controller
{
    /**
     * Execute a terminal command
     */
    public function executeCommand(Request $request): JsonResponse
    {
        try {
            $command = $request->input('command');
            
            if (empty($command)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Command cannot be empty'
                ]);
            }
            
            // Security: Only allow safe commands
            if (!$this->isCommandSafe($command)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Command not allowed for security reasons'
                ]);
            }
            
            // Log the command execution
            Log::info('Terminal command executed', ['command' => $command]);
            
            // Execute the command
            $result = $this->runCommand($command);
            
            return response()->json([
                'success' => $result['success'],
                'output' => $result['output'],
                'error' => $result['error'] ?? null
            ]);
            
        } catch (\Exception $e) {
            Log::error('Terminal command execution failed', [
                'command' => $request->input('command'),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Internal server error: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Check if command is safe to execute
     */
    private function isCommandSafe(string $command): bool
    {
        // List of allowed command prefixes
        $allowedCommands = [
            'php artisan',
            'composer',
            'npm',
            'yarn',
            'git status',
            'git log',
            'git branch',
            'ls',
            'dir',
            'pwd',
            'whoami',
            'date',
            'echo'
        ];
        
        // List of dangerous commands to block
        $dangerousCommands = [
            'rm ',
            'del ',
            'format',
            'shutdown',
            'reboot',
            'halt',
            'poweroff',
            'passwd',
            'su ',
            'sudo ',
            'chmod 777',
            'chown',
            'kill',
            'killall',
            'pkill',
            'dd ',
            'fdisk',
            'mkfs',
            'mount',
            'umount'
        ];
        
        $command = strtolower(trim($command));
        
        // Check for dangerous commands
        foreach ($dangerousCommands as $dangerous) {
            if (strpos($command, $dangerous) !== false) {
                return false;
            }
        }
        
        // Check if command starts with allowed prefixes
        foreach ($allowedCommands as $allowed) {
            if (strpos($command, $allowed) === 0) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Run the command and return result
     */
    private function runCommand(string $command): array
    {
        try {
            // Determine if we're on Windows or Unix
            $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
            
            if ($isWindows) {
                // Windows command
                $process = Process::fromShellCommandline($command, base_path(), null, null, 60);
            } else {
                // Unix command
                $process = Process::fromShellCommandline($command, base_path(), null, null, 60);
            }
            
            $process->run();
            
            if ($process->isSuccessful()) {
                return [
                    'success' => true,
                    'output' => $process->getOutput() ?: 'Command executed successfully (no output)'
                ];
            } else {
                return [
                    'success' => false,
                    'output' => $process->getOutput(),
                    'error' => $process->getErrorOutput() ?: 'Command failed with exit code: ' . $process->getExitCode()
                ];
            }
            
        } catch (ProcessFailedException $e) {
            return [
                'success' => false,
                'error' => 'Process failed: ' . $e->getMessage()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Execution error: ' . $e->getMessage()
            ];
        }
    }
}