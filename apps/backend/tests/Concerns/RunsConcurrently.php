<?php

namespace Tests\Concerns;

use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

trait RunsConcurrently
{
    /**
     * @param  callable(int): string  $task
     * @return list<string>
     */
    protected function runConcurrently(int $processes, callable $task): array
    {
        if (! function_exists('pcntl_fork') || ! function_exists('posix_kill')) {
            $this->markTestSkipped('The pcntl and posix extensions are required for concurrency tests.');
        }

        DB::disconnect();

        $startAt = microtime(true) + 0.5;
        $children = [];

        foreach (range(1, $processes) as $index) {
            $output = tempnam(sys_get_temp_dir(), 'concurrent-');
            $pid = pcntl_fork();

            if ($pid === -1) {
                throw new RuntimeException('Unable to fork a concurrent process.');
            }

            if ($pid === 0) {
                $this->runChild($task, $index, $output, $startAt);
            }

            $children[$pid] = $output;
        }

        $results = [];

        foreach ($children as $pid => $output) {
            pcntl_waitpid($pid, $status);
            $results[] = (string) file_get_contents($output);
            unlink($output);
        }

        DB::reconnect();

        return $results;
    }

    private function runChild(callable $task, int $index, string $output, float $startAt): never
    {
        while (microtime(true) < $startAt) {
            usleep(200);
        }

        try {
            $result = $task($index);
        } catch (Throwable $exception) {
            $result = 'error: '.$exception::class.': '.$exception->getMessage();
        }

        file_put_contents($output, $result);

        posix_kill(getmypid(), SIGKILL);

        exit(1);
    }
}
