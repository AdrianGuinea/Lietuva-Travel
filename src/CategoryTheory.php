<?php
declare(strict_types=1);

/**
 * ============================================================================
 * LIETUVA TRAVEL - CORE CATEGORICAL & GRAPH INDUCTOR ARCHITECTURE
 * Implements Barr & Wells Category Theory structures in strict PHP 8.1+:
 * 1. Functor & Monad Interfaces
 * 2. Option Monad (Some | None)
 * 3. Either Monad (Right | Left) for Kleisli Pipelines (A -> B -> C)
 * 4. RouteList Endofunctor & Natural Transformations (eta: RouteList => Option)
 * 5. Monoid Interface & TripMetrics Associative Algebra
 * 6. Directed Graph (V, E) & Free Category Inductor F(G) (Prof. Grinis Model)
 * ============================================================================
 */

// 1. FUNCTOR & MONAD INTERFACES
interface Functor {
    public function fmap(callable $f): self;
}

interface Monad extends Functor {
    public static function pure(mixed $value): self;
    public function bind(callable $f): self; // Kleisli composition (>>=)
}

// 2. OPTION MONAD (Maybe: Some | None)
abstract class Option implements Monad {
    public static function pure(mixed $value): self {
        return new Some($value);
    }
    abstract public function isSome(): bool;
    abstract public function getOrElse(mixed $default): mixed;
    abstract public function toEither(array $errorData): Either;
}

final class Some extends Option {
    public function __construct(private readonly mixed $value) {}
    public function fmap(callable $f): Option { return Option::pure($f($this->value)); }
    public function bind(callable $f): Option {
        $res = $f($this->value);
        if (!$res instanceof Option) {
            throw new InvalidArgumentException("Option::bind must return an Option instance.");
        }
        return $res;
    }
    public function isSome(): bool { return true; }
    public function getOrElse(mixed $default): mixed { return $this->value; }
    public function toEither(array $errorData): Either { return new Right($this->value); }
}

final class None extends Option {
    public function fmap(callable $f): Option { return $this; }
    public function bind(callable $f): Option { return $this; }
    public function isSome(): bool { return false; }
    public function getOrElse(mixed $default): mixed { return $default; }
    public function toEither(array $errorData): Either { return new Left($errorData); }
}

// 3. EITHER MONAD (Right = Valid State | Left = Short-Circuit Error)
abstract class Either implements Monad {
    public static function pure(mixed $value): self { return new Right($value); }
    abstract public function isRight(): bool;
    abstract public function getValue(): mixed;
}

final class Right extends Either {
    public function __construct(private readonly mixed $value) {}
    public function fmap(callable $f): Either { return new Right($f($this->value)); }
    public function bind(callable $f): Either {
        $res = $f($this->value);
        if (!$res instanceof Either) {
            throw new InvalidArgumentException("Either::bind must return an Either instance.");
        }
        return $res;
    }
    public function isRight(): bool { return true; }
    public function getValue(): mixed { return $this->value; }
}

final class Left extends Either {
    public function __construct(private readonly array $errorData) {}
    public function fmap(callable $f): Either { return $this; }
    public function bind(callable $f): Either { return $this; }
    public function isRight(): bool { return false; }
    public function getValue(): array { return $this->errorData; }
}

// 4. ROUTELIST FUNCTOR & NATURAL TRANSFORMATION (RouteList => Option)
final class RouteList implements Functor {
    public function __construct(private readonly array $items) {}
    public function fmap(callable $f): self { return new self(array_map($f, $this->items)); }
    public function filter(callable $p): self { return new self(array_values(array_filter($this->items, $p))); }
    public function getItems(): array { return $this->items; }

    public function sortAsc(callable $comparator): self {
        $items = $this->items;
        usort($items, $comparator);
        return new self($items);
    }

    /**
     * Natural Transformation eta: RouteList<A> => Option<A>
     * Satisfies G(f) ∘ eta_A = eta_B ∘ F(f)
     */
    public static function headOption(self $list): Option {
        $items = $list->getItems();
        // First item by position, not key 0: keyed/sparse arrays are valid RouteList input.
        return empty($items) ? new None() : new Some($items[array_key_first($items)]);
    }

    public function pickCheapest(callable $comparator): Option {
        return self::headOption($this->sortAsc($comparator));
    }
}

// 5. MONOID FOR ASSOCIATIVE PRICING, DURATION & LOYALTY CALCULATIONS
interface Monoid {
    public static function empty(): self;
    public function append(self $other): self;
}

final class TripMetrics implements Monoid {
    public readonly float $costEur;
    public readonly int $costCents;

    public function __construct(
        float $costEur,
        public readonly int $durationHours,
        public readonly int $points,
        public readonly array $items = []
    ) {
        // shortcut: rounds to whole cents once, at the leaf. From here append() only
        // ever adds ints, so (a+b)+c = a+(b+c) holds exactly — no re-rounding drift.
        $this->costCents = (int) round($costEur * 100);
        $this->costEur = $this->costCents / 100;
    }

    public static function empty(): self { return new self(0.0, 0, 0, []); }

    public function append(Monoid $other): self {
        if (!$other instanceof self) {
            throw new InvalidArgumentException("Can only append TripMetrics.");
        }
        return new self(
            ($this->costCents + $other->costCents) / 100,
            $this->durationHours + $other->durationHours,
            $this->points + $other->points,
            self::mergeItems($this->items, $other->items)
        );
    }

    // Identity's [] leaves the other side untouched (keeps sparse keys); otherwise
    // array_merge, which is associative (int keys renumbered in order, string keys last-wins).
    private static function mergeItems(array $a, array $b): array {
        if ($a === []) return $b;
        if ($b === []) return $a;
        return array_merge($a, $b);
    }

    public static function fold(array $list): self {
        return array_reduce($list, fn(self $acc, self $item) => $acc->append($item), self::empty());
    }
}

// ============================================================================
// 6. DIRECTED GRAPH & FREE CATEGORY INDUCTOR F(G) (Prof. I. Grinis Model)
// A Directed Graph G = (V, E, src, tgt) induces a Free Category F(G) where:
// - Objects Ob(F(G)) are Graph Vertices V (Travel Nodes / States)
// - Morphisms Hom(u, v) are finite composable paths of Directed Edges E
// - Identity id_u is the empty path at vertex u
// - Composition (p2 ∘ p1) is path concatenation when tgt(p1) === src(p2)
// ============================================================================
final class GraphNode {
    public function __construct(
        public readonly string $id,
        public readonly string $label,
        public readonly string $stage
    ) {}
}

final class GraphEdge {
    public function __construct(
        public readonly string $id,
        public readonly GraphNode $source,
        public readonly GraphNode $target,
        public readonly string $actionLabel,
        public readonly TripMetrics $weight
    ) {}
}

final class FreeCategoryPath {
    /**
     * @param GraphNode   $source Start vertex in Ob(F(G))
     * @param GraphNode   $target End vertex in Ob(F(G))
     * @param GraphEdge[] $edges  Ordered sequence of directed edges
     */
    public function __construct(
        public readonly GraphNode $source,
        public readonly GraphNode $target,
        public readonly array $edges = []
    ) {
        if (empty($edges)) {
            if ($source->id !== $target->id) {
                throw new InvalidArgumentException("Empty path must be an identity: source must equal target.");
            }
            return;
        }
        if ($edges[0]->source->id !== $source->id || $edges[count($edges) - 1]->target->id !== $target->id) {
            throw new InvalidArgumentException("Path source/target must match the first/last edge.");
        }
        for ($i = 0; $i < count($edges) - 1; $i++) {
            if ($edges[$i]->target->id !== $edges[$i + 1]->source->id) {
                throw new InvalidArgumentException("Path edges must form a connected chain.");
            }
        }
    }

    /**
     * Identity morphism id_u : u -> u (0 edges, neutral Monoid weight)
     */
    public static function identity(GraphNode $node): self {
        return new self($node, $node, []);
    }

    /**
     * Lifts a single directed graph edge e: u -> v into a Free Category morphism
     */
    public static function fromEdge(GraphEdge $edge): self {
        return new self($edge->source, $edge->target, [$edge]);
    }

    /**
     * Categorical composition in F(G): this (u -> v) composed with $next (v -> w)
     * Returns Right(FreeCategoryPath u -> w) iff target(this) === source(next).
     */
    public function compose(self $next): Either {
        if ($this->target->id !== $next->source->id) {
            return new Left([
                'step'  => 'Free Category Inductor F(G)',
                'title' => 'Non-Composable Graph Morphisms',
                'msg'   => "Target vertex '{$this->target->id}' does not match source vertex '{$next->source->id}'.",
                'fix'   => 'Ensure graph edges form a connected walk u -> v -> w.'
            ]);
        }
        return new Right(new self(
            $this->source,
            $next->target,
            array_merge($this->edges, $next->edges)
        ));
    }

    /**
     * Strict Monoidal Functor M : F(G) -> TripMetrics
     * Maps identity path to TripMetrics::empty() and path composition to Monoid append:
     * M(p2 ∘ p1) = M(p1) ⊕ M(p2)
     */
    public function evaluateMetrics(): TripMetrics {
        return TripMetrics::fold(array_map(fn(GraphEdge $e) => $e->weight, $this->edges));
    }

    /**
     * Formats the induced categorical path as a visual graph chain
     */
    public function toSignature(): string {
        if (empty($this->edges)) {
            return "id_{" . $this->source->id . "} (" . $this->source->id . " → " . $this->target->id . ")";
        }
        $parts = [$this->source->id];
        foreach ($this->edges as $e) {
            $parts[] = "──[{$e->actionLabel}]──► {$e->target->id}";
        }
        return implode(' ', $parts);
    }
}

// 7. DOMAIN ENTITIES
final class BookingOffer {
    public function __construct(
        public readonly string $providerName,
        public readonly float $priceEur,
        public readonly string $bookingUrl,
        public readonly string $perk
    ) {}
}

final class Flight {
    public readonly RouteList $sortedOffers;
    public readonly BookingOffer $cheapestOffer;

    public function __construct(
        public readonly string $id,
        public readonly string $airline,
        public readonly string $originCity,
        public readonly string $originIata,
        public readonly string $destinationCity,
        public readonly string $destIata,
        public readonly string $departureTime,
        public readonly int $arrivalHour,
        public readonly int $durationHours,
        public readonly string $icon,
        RouteList $offers
    ) {
        $this->sortedOffers = $offers->sortAsc(fn(BookingOffer $a, BookingOffer $b) => $a->priceEur <=> $b->priceEur);
        $this->cheapestOffer = RouteList::headOption($this->sortedOffers)->getOrElse(
            new BookingOffer('Direct', 99.0, 'https://www.google.com/travel/flights', '')
        );
    }
}

final class Hotel {
    public readonly RouteList $sortedOffers;
    public readonly BookingOffer $cheapestOffer;

    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $city,
        public readonly int $lastCheckInHour,
        public readonly string $badge,
        public readonly string $icon,
        RouteList $offers
    ) {
        $this->sortedOffers = $offers->sortAsc(fn(BookingOffer $a, BookingOffer $b) => $a->priceEur <=> $b->priceEur);
        $this->cheapestOffer = RouteList::headOption($this->sortedOffers)->getOrElse(
            new BookingOffer('Booking.com', 80.0, 'https://www.booking.com', '')
        );
    }
}

final class Activity {
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly string $city,
        public readonly bool $isOutdoor,
        public readonly float $pricePerPerson,
        public readonly int $durationHours,
        public readonly string $icon,
        public readonly string $bookingUrl
    ) {}
}

final class Itinerary {
    public readonly FreeCategoryPath $inducedPath;

    public function __construct(
        public readonly string $city,
        public readonly string $originAirport,
        public readonly int $nights,
        public readonly int $travelers,
        public readonly float $maxBudget,
        public readonly string $weather,
        public readonly ?Flight $flight = null,
        public readonly ?Hotel $hotel = null,
        public readonly array $activities = [],
        public readonly TripMetrics $flightMetrics = new TripMetrics(0.0, 0, 0),
        public readonly TripMetrics $hotelMetrics = new TripMetrics(0.0, 0, 0),
        public readonly TripMetrics $activityMetrics = new TripMetrics(0.0, 0, 0),
        public readonly TripMetrics $metrics = new TripMetrics(0.0, 0, 0),
        public readonly array $trace = [],
        ?FreeCategoryPath $inducedPath = null
    ) {
        $startNode = new GraphNode(
            $originAirport === 'ALL' ? 'HUB' : $originAirport,
            'Origin Hub',
            'origin'
        );
        $this->inducedPath = $inducedPath ?? FreeCategoryPath::identity($startNode);
    }

    public function advance(
        ?Flight $flight = null,
        ?Hotel $hotel = null,
        ?array $activities = null,
        ?TripMetrics $stepMetrics = null,
        string $stepType = '',
        string $traceMsg = '',
        ?GraphEdge $graphEdge = null
    ): self {
        if ($graphEdge !== null && $stepMetrics !== null && $stepMetrics != $graphEdge->weight) {
            throw new InvalidArgumentException("Step metrics must match the traversed edge's weight.");
        }
        // Metrics only change by traversing an edge, so itinerary metrics == M(inducedPath).
        if ($graphEdge === null && $stepMetrics !== null) {
            throw new InvalidArgumentException("A metric-changing step requires a graphEdge.");
        }
        $stepMetrics ??= $graphEdge?->weight;

        $newMetrics = $stepMetrics ? $this->metrics->append($stepMetrics) : $this->metrics;
        $newTrace = $this->trace;
        if ($traceMsg !== '') $newTrace[] = $traceMsg;

        $newPath = $this->inducedPath;
        if ($graphEdge !== null) {
            // 'HUB' is the unresolved-origin sentinel (originAirport === 'ALL'): it has
            // no real edges, so the first real edge is allowed to set the actual start.
            if (empty($newPath->edges) && $newPath->source->id === 'HUB') {
                $newPath = FreeCategoryPath::fromEdge($graphEdge);
            } else {
                $composed = $newPath->compose(FreeCategoryPath::fromEdge($graphEdge));
                if (!$composed->isRight()) {
                    $err = $composed->getValue();
                    throw new RuntimeException("{$err['title']}: {$err['msg']}");
                }
                $newPath = $composed->getValue();
            }
        }

        return new self(
            $this->city,
            $this->originAirport,
            $this->nights,
            $this->travelers,
            $this->maxBudget,
            $this->weather,
            $flight ?? $this->flight,
            $hotel ?? $this->hotel,
            $activities ?? $this->activities,
            $stepType === 'flight' && $stepMetrics ? $stepMetrics : $this->flightMetrics,
            $stepType === 'hotel' && $stepMetrics ? $stepMetrics : $this->hotelMetrics,
            $stepType === 'activity' && $stepMetrics ? $stepMetrics : $this->activityMetrics,
            $newMetrics,
            $newTrace,
            $newPath
        );
    }
}
