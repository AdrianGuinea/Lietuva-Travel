<?php
/**
 * Additional mathematical and invariant checks for Lietuva Travel v6.2.
 * Place this file in the project root (beside index.php), then run:
 * php LietuvaTravel_regression_checks.php
 */
declare(strict_types=1);
require_once __DIR__ . '/src/CategoryTheory.php';

$results = [];
function check(string $name, bool $ok, string $detail): void {
    global $results;
    $results[] = [$name, $ok, $detail];
}

// 1. Option monad laws on PHP mixed (including null).
$f = fn(mixed $x): Option => new Some('received');
$left = Option::pure(null)->bind($f);
check('Option left identity: null', $left->isSome() && $left->getOrElse('') === 'received',
    'pure(null) >>= f must equal f(null) = Some("received")');

$someNull = new Some(null);
$right = $someNull->bind(fn(mixed $x): Option => Option::pure($x));
check('Option right identity: Some(null)', $right->isSome() && $right->getOrElse('sentinel') === null,
    'Some(null) >>= pure must remain Some(null)');

$mapped = $someNull->fmap(fn(mixed $x): mixed => $x);
check('Option functor identity: Some(null)', $mapped->isSome() && $mapped->getOrElse('sentinel') === null,
    'fmap(id) must preserve Some(null)');

$g = fn(mixed $x): string => $x === null ? 'FOUND' : 'OTHER';
$fun = fn(mixed $x): mixed => null;
$m = new Some(1);
$l = $m->fmap($fun)->fmap($g);
$r = $m->fmap(fn(mixed $x): mixed => $g($fun($x)));
check('Option functor composition: intermediate null', $l->isSome() && $r->isSome() && $l->getOrElse('') === $r->getOrElse(''),
    'fmap(g) o fmap(f) must equal fmap(g o f)');

$xs = new RouteList([null]);
$l = RouteList::headOption($xs)->fmap(fn(mixed $x): mixed => $x);
$r = RouteList::headOption($xs->fmap(fn(mixed $x): mixed => $x));
check('Naturality: RouteList([null])', $l->isSome() && $r->isSome() && $l->getOrElse('sentinel') === $r->getOrElse('sentinel'),
    'headOption followed by fmap must equal fmap followed by headOption');

// 2. Monoid identity on all arrays admitted by TripMetrics's constructor.
$sp = new TripMetrics(1.0, 2, 3, [7 => 'ticket']);
$z = TripMetrics::empty();
$l = $z->append($sp);
$r = $sp->append($z);
check('TripMetrics monoid identity: sparse array', $l->items === $sp->items && $r->items === $sp->items,
    'empty() must not renumber numeric item keys; alternatively reject non-list inputs');

// 3. Graph/path integrity.
$A = new GraphNode('A', 'Origin', 'origin');
$B = new GraphNode('B', 'Airport', 'airport');
$C = new GraphNode('C', 'Hotel', 'hotel');
$AB = new GraphEdge('ab', $A, $B, 'Flight', new TripMetrics(10.0, 2, 0));
$BC = new GraphEdge('bc', $B, $C, 'Hotel', new TripMetrics(20.0, 24, 0));

try {
    $invalid = new FreeCategoryPath($A, $C, [$AB]);
    $ok = !($invalid->target->id === 'C' && $invalid->edges[0]->target->id === 'B');
} catch (Throwable $e) {
    $ok = true; // rejecting invalid path is correct
}
check('Path rejects inconsistent target', $ok,
    'Path A->C cannot contain only edge A->B');

try {
    $invalidEmpty = new FreeCategoryPath($A, $B, []);
    $ok = $invalidEmpty->source->id === $invalidEmpty->target->id;
} catch (Throwable $e) {
    $ok = true;
}
check('Empty paths are identities only', $ok,
    'An empty path A->B with A != B must be rejected');

$it = new Itinerary('Vilnius', 'A', 2, 1, 200, 'Sunny');
try {
    $badFirst = $it->advance(stepMetrics: $BC->weight, graphEdge: $BC);
    $ok = $badFirst->inducedPath->source->id === $it->inducedPath->source->id;
} catch (Throwable $e) {
    $ok = true; // fail-fast is correct
}
check('Itinerary first edge preserves origin', $ok,
    'Starting at A must not silently turn into starting at B');

$wrongWeight = new TripMetrics(100.0, 2, 0);
try {
    $mismatch = $it->advance(stepMetrics: $wrongWeight, graphEdge: $AB);
    $ok = $mismatch->metrics->costCents === $mismatch->inducedPath->evaluateMetrics()->costCents;
} catch (Throwable $e) {
    $ok = true; // rejecting inconsistent weights is correct
}
check('Itinerary metrics agree with path', $ok,
    'Step cost 100 and edge cost 10 must not silently coexist');

$passed = 0;
foreach ($results as [$name, $ok, $detail]) {
    echo ($ok ? '[PASS]' : '[FAIL]') . " $name\n       $detail\n";
    $passed += (int)$ok;
}
$total = count($results);
echo "\n$passed/$total additional regression checks passed.\n";
exit($passed === $total ? 0 : 1);
