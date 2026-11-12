<?php
/**
 * Lietuva Travel — additional regression cases found 2026-11-12.
 * Place this file beside index.php (src/), then run:
 *   php LietuvaTravel_nov12_additional_checks.php
 *
 * Exit 0 means all guards are effective; non-zero reveals missing guards.
 * Deliberately tests boundary inputs and public API invariants outside the
 * app's normal form-generated inputs.
 */
declare(strict_types=1);
require_once __DIR__ . '/CategoryTheory.php';

$results = [];
function verify_case(string $name, callable $test): void {
    global $results;
    try { [$ok, $details] = $test(); }
    catch (Throwable $e) { $ok = false; $details = get_class($e) . ': ' . $e->getMessage(); }
    $results[] = [$name, $ok, $details];
}

verify_case('TripMetrics associative for its accepted items arrays', function (): array {
    $a = new TripMetrics(0, 0, 0, [1 => 'A']);
    $b = new TripMetrics(0, 0, 0, [-1 => 'B']);
    $c = new TripMetrics(0, 0, 0, [-1 => 'C']);
    $left = $a->append($b)->append($c)->items;
    $right = $a->append($b->append($c))->items;
    return [$left === $right,
        'left keys=' . json_encode(array_keys($left)) . ', right keys=' . json_encode(array_keys($right))];
});

verify_case('RouteList headOption preserves first item for nonempty keyed array', function (): array {
    $result = null;
    $warning = '';
    set_error_handler(function ($severity, $message) use (&$warning) {
        $warning = $message;
        return true;
    });
    try { $result = RouteList::headOption(new RouteList([7 => 'ticket'])); }
    finally { restore_error_handler(); }
    $actual = $result->getOrElse('NOTHING');
    return [$result->isSome() && $actual === 'ticket' && $warning === '',
        'actual=' . var_export($actual, true) . ($warning ? ', warning=' . $warning : '')];
});

verify_case('Itinerary rejects weighted graphEdge without corresponding metrics', function (): array {
    $origin = new GraphNode('A', 'Origin', 'origin');
    $airport = new GraphNode('B', 'Airport', 'airport');
    $edge = new GraphEdge('e', $origin, $airport, 'Flight', new TripMetrics(100, 2, 0));
    $trip = new Itinerary('Vilnius', 'A', 1, 1, 200, 'Sunny');
    try {
        $next = $trip->advance(graphEdge: $edge);
        $valid = $next->metrics->costCents === $next->inducedPath->evaluateMetrics()->costCents;
        return [$valid, "itinerary={$next->metrics->costCents} cents, graph={$next->inducedPath->evaluateMetrics()->costCents} cents"];
    } catch (InvalidArgumentException | RuntimeException $e) {
        return [true, 'Rejected inconsistent graph step: ' . $e->getMessage()];
    }
});

verify_case('Itinerary rejects metric-changing step without graphEdge', function (): array {
    $trip = new Itinerary('Vilnius', 'A', 1, 1, 200, 'Sunny');
    try {
        $next = $trip->advance(stepMetrics: new TripMetrics(100, 2, 0));
        $valid = $next->metrics->costCents === $next->inducedPath->evaluateMetrics()->costCents;
        return [$valid, "itinerary={$next->metrics->costCents} cents, graph={$next->inducedPath->evaluateMetrics()->costCents} cents"];
    } catch (InvalidArgumentException | RuntimeException $e) {
        return [true, 'Rejected inconsistent metric-only step: ' . $e->getMessage()];
    }
});

$passed = 0;
foreach ($results as [$name, $ok, $details]) {
    echo ($ok ? '[PASS] ' : '[FAIL] ') . $name . PHP_EOL;
    echo '       ' . $details . PHP_EOL;
    $passed += (int)$ok;
}
echo PHP_EOL . "$passed/" . count($results) . " additional checks passed.\n";
exit($passed === count($results) ? 0 : 1);
