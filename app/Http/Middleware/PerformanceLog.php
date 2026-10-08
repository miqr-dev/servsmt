<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Performance measurement (2026-10-07), OFF by default.
 *
 *   .env:  PERF_LOG=true            turn on
 *          PERF_LOG_SLOW_MS=300     only log requests slower than this (default 0 = all)
 *
 * Per request it writes one block to storage/logs/perf-YYYY-MM-DD.log:
 *   total time, PHP memory, number of SQL queries and their total time,
 *   the 8 slowest queries (with bindings), queries that ran many times
 *   (N+1), response size and - for Inertia page visits - the size of each
 *   page prop (big props = slow page).
 * It also sets a Server-Timing header, so the browser DevTools (Network >
 * Timing) show "app" and "db" time for every request.
 *
 * Measures only; changes nothing. Turn it off again after measuring.
 */
class PerformanceLog
{
    public function handle(Request $request, Closure $next)
    {
        if (! config('app.perf_log')) {
            return $next($request);
        }

        $start = microtime(true);
        $queries = [];
        DB::listen(function (QueryExecuted $q) use (&$queries) {
            $queries[] = [$q->time, $q->sql, $q->bindings];
        });

        $response = $next($request);

        $totalMs = (microtime(true) - $start) * 1000;
        $dbMs = array_sum(array_column($queries, 0));
        $response->headers->set('Server-Timing', sprintf('app;dur=%.1f, db;dur=%.1f;desc="%d queries"', $totalMs, $dbMs, count($queries)));

        if ($totalMs < (float) config('app.perf_log_slow_ms', 0)) {
            return $response;
        }

        $content = method_exists($response, 'getContent') ? (string) $response->getContent() : '';
        $lines = [];
        $lines[] = sprintf(
            '[%s] %s %s  user=%s  total=%.0fms  db=%.0fms  queries=%d  mem=%.1fMB  size=%.0fKB',
            now()->format('H:i:s'),
            $request->method(),
            $request->getRequestUri(),
            optional($request->user())->username ?? '-',
            $totalMs,
            $dbMs,
            count($queries),
            memory_get_peak_usage(true) / 1048576,
            strlen($content) / 1024
        );

        // Inertia props, biggest first (only for X-Inertia JSON responses)
        if ($request->header('X-Inertia') && str_starts_with(trim($content), '{')) {
            $page = json_decode($content, true);
            $sizes = [];
            foreach (($page['props'] ?? []) as $key => $value) {
                $sizes[$key] = strlen(json_encode($value));
            }
            arsort($sizes);
            $lines[] = '  props: '.collect($sizes)->take(8)->map(fn ($b, $k) => sprintf('%s=%.0fKB', $k, $b / 1024))->join('  ');
        }

        // slowest queries
        usort($queries, fn ($a, $b) => $b[0] <=> $a[0]);
        foreach (array_slice($queries, 0, 8) as [$ms, $sql, $bindings]) {
            $lines[] = sprintf('  %7.1fms  %s', $ms, $this->sql($sql, $bindings));
        }

        // repeated queries (N+1)
        $counts = [];
        foreach ($queries as [$ms, $sql]) {
            $counts[$sql] = ($counts[$sql] ?? 0) + 1;
        }
        arsort($counts);
        foreach (array_filter($counts, fn ($n) => $n >= 5) as $sql => $n) {
            $lines[] = sprintf('  repeated %dx: %s', $n, mb_substr($sql, 0, 200));
        }

        @file_put_contents(storage_path('logs/perf-'.now()->format('Y-m-d').'.log'), implode("\n", $lines)."\n\n", FILE_APPEND);

        return $response;
    }

    private function sql(string $sql, array $bindings): string
    {
        foreach ($bindings as $b) {
            $b = is_string($b) ? "'".mb_substr($b, 0, 40)."'" : (is_bool($b) ? (int) $b : ($b instanceof \DateTimeInterface ? "'".$b->format('Y-m-d H:i:s')."'" : (string) $b));
            $sql = preg_replace('/\?/', (string) $b, $sql, 1);
        }

        return mb_substr(preg_replace('/\s+/', ' ', $sql), 0, 400);
    }
}
