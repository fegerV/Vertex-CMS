<?php

declare(strict_types=1);

/**
 * Summarises a PHPUnit clover report and enforces a coverage threshold.
 *
 * Usage: php scripts/ci/coverage-check.php coverage.xml <min-percent>
 *
 * Prints one human readable line followed by the percentage on its own line, so
 * CI can copy the last line into an output or an artifact name. Exits with 1
 * when coverage is below the threshold, 2 when the report is unusable.
 */

$reportPath = $argv[1] ?? 'coverage.xml';
$threshold = (float) ($argv[2] ?? 0);

if (! is_file($reportPath)) {
    fwrite(STDERR, "Coverage report not found: {$reportPath}\n");
    exit(2);
}

$xml = @simplexml_load_file($reportPath);

if ($xml === false || ! isset($xml->project->metrics)) {
    fwrite(STDERR, "Coverage report is not valid clover XML: {$reportPath}\n");
    exit(2);
}

$metrics = $xml->project->metrics;
$statements = (int) $metrics['statements'];
$covered = (int) $metrics['coveredstatements'];

if ($statements === 0) {
    fwrite(STDERR, "Coverage report contains no statements: {$reportPath}\n");
    exit(2);
}

$percent = round($covered / $statements * 100, 2);

printf(
    "Covered %d of %d statements: %.2f%% (threshold %.2f%%)%s\n",
    $covered,
    $statements,
    $percent,
    $threshold,
    $percent < $threshold ? ' - below threshold' : ''
);

echo $percent.PHP_EOL;

if ($percent < $threshold) {
    fwrite(STDERR, sprintf("Coverage %.2f%% is below the required %.2f%%.\n", $percent, $threshold));
    exit(1);
}

exit(0);
