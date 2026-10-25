<?php
declare(strict_types=1);

require_once __DIR__ . '/src/CategoryTheory.php';

// 0. LANGUAGE MANAGEMENT (POST > GET > COOKIE > Default 'en')
$lang = $_POST['lang'] ?? $_GET['lang'] ?? $_COOKIE['lang'] ?? 'en';
if (!in_array($lang, ['en', 'es', 'lt'], true)) {
    $lang = 'en';
}
if (!headers_sent()) {
    setcookie('lang', $lang, time() + (3600 * 24 * 30), '/');
}
$translations = require __DIR__ . "/lang/{$lang}.php";

function t(string $key, array $replacements = []): string {
    global $translations;
    $text = $translations[$key] ?? $key;
    foreach ($replacements as $k => $v) {
        $text = str_replace(":{$k}:", (string)$v, $text);
    }
    return $text;
}

// 1. LIVE APIs (Open-Meteo & Frankfurter ECB wrapped in Option Monad)
function fetchLiveJsonOption(string $url): Option {
    $ctx = stream_context_create(['http' => ['timeout' => 2.0, 'header' => "User-Agent: LietuvaTravel/6.2\r\n"]]);
    $raw = @file_get_contents($url, false, $ctx);
    return Option::pure($raw)->fmap(fn(string $json) => json_decode($json, true));
}

function getLiveLithuanianWeather(string $city): array {
    $coords = [
        'Vilnius'  => ['lat' => 54.6872, 'lon' => 25.2797],
        'Kaunas'   => ['lat' => 54.8985, 'lon' => 23.9036],
        'Klaipėda' => ['lat' => 55.7033, 'lon' => 21.1443],
    ][$city] ?? ['lat' => 54.6872, 'lon' => 25.2797];

    $url = "https://api.open-meteo.com/v1/forecast?latitude={$coords['lat']}&longitude={$coords['lon']}&current=temperature_2m,precipitation,weather_code,wind_speed_10m";
    return fetchLiveJsonOption($url)
        ->fmap(function (array $d) {
            $cur = $d['current'] ?? [];
            $isRainy = (((int)($cur['weather_code'] ?? 0)) >= 51 || ((float)($cur['precipitation'] ?? 0)) > 0.2);
            return [
                'source' => 'LIVE Open-Meteo API',
                'temp' => ($cur['temperature_2m'] ?? 14) . '°C',
                'wind' => ($cur['wind_speed_10m'] ?? 10) . ' km/h',
                'condition' => $isRainy ? 'Rainy' : 'Sunny',
            ];
        })
        ->getOrElse(['source' => 'Offline Cache', 'temp' => '15°C', 'wind' => '10 km/h', 'condition' => 'Sunny']);
}

function getLiveCurrencyRate(string $targetCurrency): array {
    if ($targetCurrency === 'EUR') return ['rate' => 1.0, 'symbol' => '€', 'source' => 'ECB Base (EUR)'];
    $symbols = ['USD' => '$', 'GBP' => '£', 'PLN' => 'zł'];
    return fetchLiveJsonOption("https://api.frankfurter.app/latest?from=EUR&to={$targetCurrency}")
        ->fmap(fn(array $d) => [
            'rate' => (float)($d['rates'][$targetCurrency] ?? 1.0),
            'symbol' => $symbols[$targetCurrency] ?? $targetCurrency . ' ',
            'source' => 'LIVE ECB'
        ])
        ->getOrElse(['rate' => 1.0, 'symbol' => '€', 'source' => 'Fallback']);
}

// 2. MULTILINGUAL INTERNATIONAL CATALOG
function getCatalog(string $travelDate, int $nights, int $travelers): array {
    $ts = strtotime($travelDate) ?: time();
    $checkout = date('Y-m-d', strtotime("+{$nights} days", $ts));
    $isWeekend = ((int)date('N', $ts) >= 5);
    $factor = $isWeekend ? 1.18 : 1.0;
    $p = fn(float $base) => round($base * $factor, 2);

    $makeFlightOffers = function(string $origIata, string $destIata, string $airline, string $url, float $base) use ($travelDate, $p) {
        return new RouteList([
            new BookingOffer($airline . ' Official', $p($base), $url, t('perk_direct_flight')),
            new BookingOffer('Trip.com / Agoda', $p($base + 7.50), "https://www.trip.com/flights/{$origIata}-to-{$destIata}/", t('perk_ota_flight')),
            new BookingOffer('Google Flights', $p($base + 12.00), "https://www.google.com/travel/flights?q=Flights+from+{$origIata}+to+{$destIata}+on+{$travelDate}", t('perk_gflights')),
        ]);
    };

    $makeHotelOffers = function(string $name, string $city, float $baseNightly) use ($travelDate, $checkout, $travelers, $p) {
        $q = urlencode($name . ' ' . $city . ' Lithuania');
        return new RouteList([
            new BookingOffer('Booking.com', $p($baseNightly), "https://www.booking.com/searchresults.html?ss={$q}&checkin={$travelDate}&checkout={$checkout}&group_adults={$travelers}&order=price", t('perk_booking')),
            new BookingOffer('Hotels.com', $p($baseNightly + 6.00), "https://www.hotels.com/Hotel-Search?destination={$q}&startDate={$travelDate}&endDate={$checkout}&adults={$travelers}", t('perk_hotels_com')),
            new BookingOffer('Official Hotel Site', $p($baseNightly + 11.50), "https://www.google.com/search?q={$q}+official+site", t('perk_official_hotel')),
        ]);
    };

    return [
        'flights' => (new RouteList([
            // VILNIUS (VNO)
            new Flight('FL-BER-VNO', 'Ryanair', t('airport_BER'), 'BER', 'Vilnius', 'VNO', '11:30', 14, 2, '✈️',
                $makeFlightOffers('BER', 'VNO', 'Ryanair', 'https://www.ryanair.com', 38.0)),
            new Flight('FL-STN-VNO', 'Ryanair', t('airport_STN'), 'STN', 'Vilnius', 'VNO', '07:30', 12, 3, '✈️',
                $makeFlightOffers('STN', 'VNO', 'Ryanair', 'https://www.ryanair.com', 42.0)),
            new Flight('FL-LTN-VNO', 'Wizz Air', t('airport_LTN'), 'LTN', 'Vilnius', 'VNO', '17:20', 22, 3, '🌙',
                $makeFlightOffers('LTN', 'VNO', 'Wizz Air', 'https://www.wizzair.com', 49.0)),
            new Flight('FL-WAW-VNO', 'LOT Polish Airlines', t('airport_WAW'), 'WAW', 'Vilnius', 'VNO', '15:10', 17, 1, '🛫',
                $makeFlightOffers('WAW', 'VNO', 'LOT', 'https://www.lot.com', 74.0)),
            new Flight('FL-BER-VNO-AB', 'airBaltic', t('airport_BER'), 'BER', 'Vilnius', 'VNO', '20:45', 23, 2, '🌙',
                $makeFlightOffers('BER', 'VNO', 'airBaltic', 'https://www.airbaltic.com', 84.0)),
            new Flight('FL-BCN-VNO', 'Wizz Air', t('airport_BCN'), 'BCN', 'Vilnius', 'VNO', '06:15', 11, 4, '✈️',
                $makeFlightOffers('BCN', 'VNO', 'Wizz Air', 'https://www.wizzair.com', 89.0)),
            new Flight('FL-MAD-VNO', 'airBaltic', t('airport_MAD'), 'MAD', 'Vilnius', 'VNO', '14:10', 19, 4, '✈️',
                $makeFlightOffers('MAD', 'VNO', 'airBaltic', 'https://www.airbaltic.com', 115.0)),
            new Flight('FL-FRA-VNO', 'Lufthansa', t('airport_FRA'), 'FRA', 'Vilnius', 'VNO', '10:20', 13, 2, '✈️',
                $makeFlightOffers('FRA', 'VNO', 'Lufthansa', 'https://www.lufthansa.com', 124.0)),

            // KAUNAS (KUN)
            new Flight('FL-STN-KUN', 'Ryanair', t('airport_STN'), 'STN', 'Kaunas', 'KUN', '08:15', 13, 3, '✈️',
                $makeFlightOffers('STN', 'KUN', 'Ryanair', 'https://www.ryanair.com', 36.0)),
            new Flight('FL-CPH-KUN', 'Ryanair', t('airport_CPH'), 'CPH', 'Kaunas', 'KUN', '12:40', 15, 2, '✈️',
                $makeFlightOffers('CPH', 'KUN', 'Ryanair', 'https://www.ryanair.com', 41.0)),
            new Flight('FL-LTN-KUN', 'Wizz Air', t('airport_LTN'), 'LTN', 'Kaunas', 'KUN', '18:10', 23, 3, '🌙',
                $makeFlightOffers('LTN', 'KUN', 'Wizz Air', 'https://www.wizzair.com', 46.0)),
            new Flight('FL-MAD-KUN', 'Ryanair', t('airport_MAD'), 'MAD', 'Kaunas', 'KUN', '16:30', 22, 4, '🌙',
                $makeFlightOffers('MAD', 'KUN', 'Ryanair', 'https://www.ryanair.com', 96.0)),
            new Flight('FL-FRA-KUN', 'Lufthansa', t('airport_FRA'), 'FRA', 'Kaunas', 'KUN', '13:00', 16, 2, '✈️',
                $makeFlightOffers('FRA', 'KUN', 'Lufthansa', 'https://www.lufthansa.com', 122.0)),

            // KLAIPĖDA / PALANGA (PLQ)
            new Flight('FL-RIX-PLQ', 'airBaltic', t('airport_RIX'), 'RIX', 'Klaipėda', 'PLQ', '11:00', 12, 1, '🛩️',
                $makeFlightOffers('RIX', 'PLQ', 'airBaltic', 'https://www.airbaltic.com', 58.0)),
            new Flight('FL-STN-PLQ', 'Ryanair', t('airport_STN'), 'STN', 'Klaipėda', 'PLQ', '17:45', 22, 3, '🌙',
                $makeFlightOffers('STN', 'PLQ', 'Ryanair', 'https://www.ryanair.com', 64.0)),
            new Flight('FL-CPH-PLQ', 'SAS Scandinavian', t('airport_CPH'), 'CPH', 'Klaipėda', 'PLQ', '13:15', 15, 1, '✈️',
                $makeFlightOffers('CPH', 'PLQ', 'SAS', 'https://www.flysas.com', 92.0)),
        ]))->sortAsc(fn(Flight $a, Flight $b) => $a->cheapestOffer->priceEur <=> $b->cheapestOffer->priceEur),

        'hotels' => (new RouteList([
            new Hotel('HT-ARBATA', 'Arbata Cozy Guest House', 'Vilnius', 20, t('hotel_badge_arbata'), '🏡',
                $makeHotelOffers('Arbata Guest House', 'Vilnius', 55.0)),
            new Hotel('HT-NERIS', 'Radisson Blu Hotel Lietuva', 'Vilnius', 24, t('hotel_badge_neris'), '🏨',
                $makeHotelOffers('Radisson Blu Hotel Lietuva', 'Vilnius', 88.0)),
            new Hotel('HT-PACAI', 'Hotel PACAI 5★ Old Town', 'Vilnius', 24, t('hotel_badge_pacai'), '🏰',
                $makeHotelOffers('Hotel PACAI', 'Vilnius', 145.0)),

            new Hotel('HT-SANTAKA', 'Santaka Old Town Boutique', 'Kaunas', 21, t('hotel_badge_santaka'), '🏛️',
                $makeHotelOffers('Santaka Hotel', 'Kaunas', 62.0)),
            new Hotel('HT-MOXY', 'Moxy Kaunas Center', 'Kaunas', 24, t('hotel_badge_moxy'), '⚡',
                $makeHotelOffers('Moxy Kaunas Center', 'Kaunas', 70.0)),

            new Hotel('HT-SMILGA', 'Neringa Lagoon Pine Cabin', 'Klaipėda', 20, t('hotel_badge_smilga'), '🌲',
                $makeHotelOffers('Neringa Hotel', 'Klaipėda', 54.0)),
            new Hotel('HT-MICHA', 'Michaelson Boutique Hotel', 'Klaipėda', 23, t('hotel_badge_micha'), '⚓',
                $makeHotelOffers('Michaelson Boutique Hotel', 'Klaipėda', 96.0)),
            new Hotel('HT-AMBERTON', 'Amberton Klaipėda Sea View', 'Klaipėda', 24, t('hotel_badge_amberton'), '🌊',
                $makeHotelOffers('Amberton Hotel', 'Klaipėda', 82.0)),
        ]))->sortAsc(fn(Hotel $a, Hotel $b) => $a->cheapestOffer->priceEur <=> $b->cheapestOffer->priceEur),

        'activities' => (new RouteList([
            new Activity('ACT-KGB', t('act_title_kgb'), 'Vilnius', false, 30.0, 2, '🎨', 'https://www.getyourguide.com/vilnius-l164/'),
            new Activity('ACT-CEPELINAI', t('act_title_cepelinai'), 'Vilnius', false, 45.0, 3, '🥟', 'https://www.getyourguide.com/vilnius-l164/'),
            new Activity('ACT-TRAKAI', t('act_title_trakai'), 'Vilnius', true, 155.0, 3, '🎈', 'https://www.getyourguide.com/trakai-l32477/'),

            new Activity('ACT-CIURLIONIS', t('act_title_ciurlionis'), 'Kaunas', false, 35.0, 3, '👹', 'https://www.getyourguide.com/kaunas-l1606/'),
            new Activity('ACT-LAGOON', t('act_title_lagoon'), 'Kaunas', true, 60.0, 3, '⛵', 'https://www.getyourguide.com/kaunas-l1606/'),

            new Activity('ACT-AMBER', t('act_title_amber'), 'Klaipėda', false, 40.0, 3, '💎', 'https://www.getyourguide.com/klaipeda-l1607/'),
            new Activity('ACT-NERINGA', t('act_title_neringa'), 'Klaipėda', true, 75.0, 5, '🛶', 'https://www.getyourguide.com/curonian-spit-l4582/'),
        ]))->sortAsc(fn(Activity $a, Activity $b) => $a->pricePerPerson <=> $b->pricePerPerson),
    ];
}

// 3. KLEISLI PIPELINE OVER GRAPH EDGES (Inducing Free Category Path F(G))
function stepSelectFlight(RouteList $flights, string $selectedId, string $originAirport, float $discountRate, Closure $fmtPrice): Closure {
    return function (Itinerary $it) use ($flights, $selectedId, $originAirport, $discountRate, $fmtPrice): Either {
        $mapped = $flights->fmap(function (Flight $f) use ($discountRate) {
            $discOffers = $f->sortedOffers->fmap(fn(BookingOffer $o) => new BookingOffer(
                $o->providerName,
                round($o->priceEur * (1 - $discountRate), 2),
                $o->bookingUrl,
                $o->perk
            ));
            return new Flight($f->id, $f->airline, $f->originCity, $f->originIata, $f->destinationCity, $f->destIata, $f->departureTime, $f->arrivalHour, $f->durationHours, $f->icon, $discOffers);
        });

        $candidates = $mapped->filter(function (Flight $f) use ($it, $originAirport, $selectedId) {
            $matchDest = ($f->destinationCity === $it->city);
            $matchOrigin = ($originAirport === 'ALL' || $f->originIata === $originAirport);
            $matchId = ($selectedId === 'AUTO' || $f->id === $selectedId);
            return $matchDest && $matchOrigin && $matchId;
        });

        $option = $candidates->pickCheapest(fn(Flight $a, Flight $b) => $a->cheapestOffer->priceEur <=> $b->cheapestOffer->priceEur);

        return $option
            ->toEither([
                'step'  => t('step1_step'),
                'title' => t('step1_title'),
                'msg'   => t('step1_msg', ['city' => $it->city]),
                'fix'   => t('step1_fix')
            ])
            ->bind(function (Flight $f) use ($it, $fmtPrice): Either {
                $best = $f->cheapestOffer;
                $cost = round($best->priceEur * $it->travelers, 2);
                $m = new TripMetrics($cost, $f->durationHours, (int)($cost * 1.2), [[
                    'label' => t('receipt_flight_label', [
                        'icon' => $f->icon,
                        'orig' => $f->originCity,
                        'dest' => $f->destinationCity,
                        'airline' => $f->airline,
                        'travelers' => $it->travelers
                    ]),
                    'provider' => $best->providerName,
                    'url' => $best->bookingUrl,
                    'cost' => $cost,
                ]]);

                // Graph Edge e1: Origin_Hub -> Dest_Airport
                $srcNode = new GraphNode($f->originIata, $f->originCity, 'origin');
                $tgtNode = new GraphNode($f->destIata, $f->destinationCity . ' Airport', 'airport');
                $edge1 = new GraphEdge('e1', $srcNode, $tgtNode, "e₁:{$f->airline}", $m);

                return new Right($it->advance(
                    flight: $f,
                    stepMetrics: $m,
                    stepType: 'flight',
                    traceMsg: t('trace_step1', ['id' => $f->id, 'origin' => $f->originIata, 'cost' => $fmtPrice($cost)]),
                    graphEdge: $edge1
                ));
            });
    };
}

function stepSelectHotel(RouteList $hotels, string $selectedId): Closure {
    return function (Itinerary $it) use ($hotels, $selectedId): Either {
        $candidates = $hotels->filter(fn(Hotel $h) => $h->city === $it->city && ($selectedId === 'AUTO' || $h->id === $selectedId));
        $option = $candidates->pickCheapest(fn(Hotel $a, Hotel $b) => $a->cheapestOffer->priceEur <=> $b->cheapestOffer->priceEur);

        return $option
            ->toEither([
                'step'  => t('step2_step'),
                'title' => t('step2_title'),
                'msg'   => t('step2_msg', ['city' => $it->city]),
                'fix'   => t('step2_fix')
            ])
            ->bind(function (Hotel $h) use ($it): Either {
                $arrivalAtHotel = $it->flight->arrivalHour + 1;
                if ($arrivalAtHotel > $h->lastCheckInHour) {
                    return new Left([
                        'step'  => t('step2_conn_step'),
                        'title' => t('step2_conn_title'),
                        'msg'   => t('step2_conn_msg', [
                            'airline' => $it->flight->airline,
                            'arrivalHour' => $it->flight->arrivalHour,
                            'arrivalAtHotel' => $arrivalAtHotel,
                            'hotelName' => $h->name,
                            'lastCheckInHour' => $h->lastCheckInHour
                        ]),
                        'fix'   => t('step2_conn_fix')
                    ]);
                }
                $best = $h->cheapestOffer;
                $cost = round($best->priceEur * $it->nights, 2);
                $nightsWord = $it->nights === 1 ? t('night') : t('nights_plural');
                $m = new TripMetrics($cost, $it->nights * 24, (int)($cost * 1.5), [[
                    'label' => t('receipt_hotel_label', [
                        'icon' => $h->icon,
                        'name' => $h->name,
                        'nights' => $it->nights,
                        'nights_word' => strtolower($nightsWord)
                    ]),
                    'provider' => $best->providerName,
                    'url' => $best->bookingUrl,
                    'cost' => $cost,
                ]]);

                // Graph Edge e2: Dest_Airport -> Hotel_Node
                $srcNode = $it->inducedPath->target;
                $tgtNode = new GraphNode($h->id, $h->name, 'hotel');
                $edge2 = new GraphEdge('e2', $srcNode, $tgtNode, "e₂:CheckIn≤{$h->lastCheckInHour}h", $m);

                return new Right($it->advance(
                    hotel: $h,
                    stepMetrics: $m,
                    stepType: 'hotel',
                    traceMsg: t('trace_step2', ['arr' => $arrivalAtHotel, 'cut' => $h->lastCheckInHour, 'hotel' => $h->name]),
                    graphEdge: $edge2
                ));
            });
    };
}

function stepSelectActivities(RouteList $activities, array $selectedIds, Closure $fmtPrice): Closure {
    return function (Itinerary $it) use ($activities, $selectedIds, $fmtPrice): Either {
        $chosen = $activities->filter(fn(Activity $a) => $a->city === $it->city && in_array($a->id, $selectedIds, true))->getItems();
        $metricsList = [];
        foreach ($chosen as $act) {
            if ($act->isOutdoor && $it->weather === 'Rainy') {
                return new Left([
                    'step'  => t('step3_step'),
                    'title' => t('step3_title'),
                    'msg'   => t('step3_msg', ['activityTitle' => $act->title, 'city' => $it->city]),
                    'fix'   => t('step3_fix')
                ]);
            }
            $cost = round($act->pricePerPerson * $it->travelers, 2);
            $metricsList[] = new TripMetrics($cost, $act->durationHours, (int)$cost * 2, [[
                'label' => t('receipt_activity_label', ['icon' => $act->icon, 'title' => $act->title, 'travelers' => $it->travelers]),
                'provider' => 'GetYourGuide',
                'url' => $act->bookingUrl,
                'cost' => $cost,
            ]]);
        }
        $folded = TripMetrics::fold($metricsList);

        // Graph Edge e3: Hotel_Node -> Activities_Node
        $srcNode = $it->inducedPath->target;
        $actNodeId = count($chosen) > 0 ? 'ACT×' . count($chosen) : 'ACT_0';
        $tgtNode = new GraphNode($actNodeId, 'Activities', 'activities');
        $edge3 = new GraphEdge('e3', $srcNode, $tgtNode, "e₃:{$it->weather}", $folded);

        return new Right($it->advance(
            activities: $chosen,
            stepMetrics: $folded,
            stepType: 'activity',
            traceMsg: t('trace_step3', ['count' => count($chosen), 'cost' => $fmtPrice($folded->costEur)]),
            graphEdge: $edge3
        ));
    };
}

function stepCheckBudget(Closure $fmtPrice): Closure {
    return function (Itinerary $it) use ($fmtPrice): Either {
        $total = $it->metrics->costEur;
        if ($total > $it->maxBudget) {
            $diff = round($total - $it->maxBudget, 2);
            return new Left([
                'step'  => t('step4_step'),
                'title' => t('step4_title', ['diff' => $fmtPrice($diff)]),
                'msg'   => t('step4_msg', ['total' => $fmtPrice($total), 'budget' => $fmtPrice($it->maxBudget)]),
                'fix'   => t('step4_fix')
            ]);
        }

        // Graph Edge e4: Activities_Node -> Verified_Boarding_Pass (Identity-weighted verification edge)
        $srcNode = $it->inducedPath->target;
        $tgtNode = new GraphNode('PASS_OK', 'Verified Boarding Pass', 'complete');
        $edge4 = new GraphEdge('e4', $srcNode, $tgtNode, 'e₄:BudgetOK', TripMetrics::empty());

        return new Right($it->advance(
            traceMsg: t('trace_step4', ['total' => $fmtPrice($total), 'budget' => $fmtPrice($it->maxBudget)]),
            graphEdge: $edge4
        ));
    };
}

// 4. EXECUTE PIPELINE & LIVE MATHEMATICAL PROOFS
$city = $_POST['city'] ?? 'Vilnius';
if (!in_array($city, ['Vilnius', 'Kaunas', 'Klaipėda'], true)) {
    $city = 'Vilnius';
}
$originAirport = $_POST['origin_airport'] ?? 'ALL';
$travelDate = $_POST['travel_date'] ?? date('Y-m-d', strtotime('+7 days'));
$currency = $_POST['currency'] ?? 'EUR';
$nights = max(1, min(14, (int)($_POST['nights'] ?? 2)));
$travelers = max(1, min(6, (int)($_POST['travelers'] ?? 1)));
$flightId = $_POST['flight_id'] ?? 'AUTO';
$hotelId = $_POST['hotel_id'] ?? 'AUTO';
$activityIds = $_POST['activity_ids'] ?? (isset($_POST['city']) ? [] : ['ACT-CEPELINAI']);
if (!is_array($activityIds)) $activityIds = [];
$budget = (float)($_POST['budget'] ?? 650);
$isStudent = isset($_POST['is_student']);
$discountRate = $isStudent ? 0.15 : 0.0;

$liveWeather = getLiveLithuanianWeather($city);
$weatherMode = $_POST['weather_mode'] ?? 'LIVE';
$effectiveWeather = ($weatherMode === 'LIVE') ? $liveWeather['condition'] : $weatherMode;

$fx = getLiveCurrencyRate($currency);
$fmtPrice = fn(float $eur) => $fx['symbol'] . number_format(round($eur * $fx['rate'], 2), 2);

$catalog = getCatalog($travelDate, $nights, $travelers);
$initial = new Itinerary($city, $originAirport, $nights, $travelers, $budget, $effectiveWeather);

$result = Either::pure($initial)
    ->bind(stepSelectFlight($catalog['flights'], $flightId, $originAirport, $discountRate, $fmtPrice))
    ->bind(stepSelectHotel($catalog['hotels'], $hotelId))
    ->bind(stepSelectActivities($catalog['activities'], $activityIds, $fmtPrice))
    ->bind(stepCheckBudget($fmtPrice));

$cityFlights = $catalog['flights']->filter(fn(Flight $f) => $f->destinationCity === $city && ($originAirport === 'ALL' || $f->originIata === $originAirport))->getItems();
$cityHotels = $catalog['hotels']->filter(fn(Hotel $h) => $h->city === $city)->getItems();
$cityActivities = $catalog['activities']->filter(fn(Activity $a) => $a->city === $city)->getItems();
$liveCondTranslated = t('condition_' . $liveWeather['condition']);
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars(t('title')) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= htmlspecialchars(t('meta_description', ['app' => 'Lietuva Travel'])) ?>">
    <meta name="keywords" content="<?= htmlspecialchars(t('meta_keywords')) ?>">
    <meta name="author" content="Adrián Guinea Pérez">
    <meta property="og:title" content="<?= htmlspecialchars(t('og_title', ['app' => 'Lietuva Travel'])) ?>">
    <meta property="og:description" content="<?= htmlspecialchars(t('og_description')) ?>">
    <meta property="og:type" content="website">
    <link rel="icon" href="img/favicon.svg?v=7.0" type="image/svg+xml">
    <meta name="theme-color" content="#00311f">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700&family=Barlow+Condensed:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css?v=7.0">
</head>
<body>

<header>
    <div class="brand">
        <h1><span class="flag" aria-hidden="true"></span><?= htmlspecialchars(t('brand_name')) ?> <span class="version-badge">v6.2 Graph Inductor</span></h1>
        <p><?= htmlspecialchars(t('brand_subtitle')) ?></p>
    </div>
    <div class="language-switcher" role="group" aria-label="Language selector">
        <button type="button" class="lang-btn <?= $lang === 'en' ? 'active' : '' ?>" onclick="changeLanguage('en')">🇬🇧 EN</button>
        <button type="button" class="lang-btn <?= $lang === 'es' ? 'active' : '' ?>" onclick="changeLanguage('es')">🇪🇸 ES</button>
        <button type="button" class="lang-btn <?= $lang === 'lt' ? 'active' : '' ?>" onclick="changeLanguage('lt')">🇱🇹 LT</button>
    </div>
</header>

<div class="live-bar">
    <div class="live-item">
        <span class="pulse"></span>
        <strong><?= htmlspecialchars(t('live_weather', ['city' => $city])) ?></strong>
        <span><?= htmlspecialchars($liveWeather['temp']) ?></span>
        <span><?= htmlspecialchars(t('live_wind', ['wind' => $liveWeather['wind']])) ?></span>
        <span class="live-chip"><?= $liveWeather['condition'] === 'Rainy' ? '🌧️' : '☀️' ?> <?= htmlspecialchars($liveCondTranslated) ?></span>
        <em><?= htmlspecialchars(t('live_source', ['source' => $liveWeather['source']])) ?></em>
    </div>
    <div class="live-item">
        <strong>💱 <?= htmlspecialchars(t('currency_exchange')) ?></strong>
        <span><?= htmlspecialchars(t('currency_rate', ['rate' => (string)$fx['rate'], 'currency' => $currency])) ?></span>
    </div>
</div>

<form method="POST" id="builderForm" class="layout">
    <input type="hidden" name="city" id="cityInput" value="<?= htmlspecialchars($city) ?>">
    <input type="hidden" name="lang" id="langInput" value="<?= htmlspecialchars($lang) ?>">

    <div class="panel">
        <!-- PROFESSOR GRINIS QUICK DEMO & FORMAL VERIFICATION BAR -->
        <div class="demo-bar">
            <strong style="font-size:0.84rem; color:var(--green-deep);"><?= htmlspecialchars(t('demo_bar_title')) ?></strong>
            <div class="demo-actions">
                <button type="button" class="preset-btn" onclick="loadScenario('Vilnius','ALL','FL-BER-VNO','HT-NERIS','ACT-CEPELINAI','Sunny',650)"><?= htmlspecialchars(t('preset_valid')) ?></button>
                <button type="button" class="preset-btn" onclick="loadScenario('Vilnius','ALL','FL-LTN-VNO','HT-ARBATA','ACT-CEPELINAI','Sunny',650)"><?= htmlspecialchars(t('preset_late_flight')) ?></button>
                <button type="button" class="preset-btn" onclick="loadScenario('Vilnius','ALL','FL-BER-VNO','HT-ARBATA','ACT-TRAKAI','Rainy',650)"><?= htmlspecialchars(t('preset_rain')) ?></button>
                <button type="button" class="preset-btn" onclick="loadScenario('Klaipėda','ALL','AUTO','HT-MICHA','ACT-NERINGA','Sunny',200)"><?= htmlspecialchars(t('preset_budget')) ?></button>
                <a href="tests/category_laws_test.php" class="suite-link"><?= htmlspecialchars(t('open_test_suite')) ?></a>
            </div>
        </div>

        <div class="city-tabs">
            <?php
            $destinations = [
                'Vilnius'  => ['VNO', t('city_desc_Vilnius')],
                'Kaunas'   => ['KUN', t('city_desc_Kaunas')],
                'Klaipėda' => ['PLQ', t('city_desc_Klaipėda')],
            ];
            foreach ($destinations as $cName => [$cCode, $cDesc]):
            ?>
                <div class="city-tab <?= $city === $cName ? 'active' : '' ?>" role="button" tabindex="0" aria-pressed="<?= $city === $cName ? 'true' : 'false' ?>"
                     onclick="selectCity('<?= $cName ?>')"
                     onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); selectCity('<?= $cName ?>'); }">
                    <em class="code"><?= $cCode ?></em>
                    <strong><?= $cName ?></strong>
                    <span><?= htmlspecialchars($cDesc) ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="settings-bar">
            <div class="setting-item">
                <label><?= htmlspecialchars(t('origin_airport')) ?></label>
                <select name="origin_airport" onchange="submitForm()">
                    <option value="ALL" <?= $originAirport === 'ALL' ? 'selected' : '' ?>><?= htmlspecialchars(t('all_origins')) ?></option>
                    <?php foreach (['STN', 'LTN', 'MAD', 'BCN', 'BER', 'FRA', 'WAW', 'CPH', 'RIX'] as $iata): ?>
                        <option value="<?= $iata ?>" <?= $originAirport === $iata ? 'selected' : '' ?>><?= htmlspecialchars(t('airport_' . $iata)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="setting-item">
                <label><?= htmlspecialchars(t('travel_date')) ?></label>
                <input type="date" name="travel_date" value="<?= htmlspecialchars($travelDate) ?>" onchange="submitForm()">
            </div>
            <div class="setting-item">
                <label><?= htmlspecialchars(t('travelers')) ?></label>
                <select name="travelers" onchange="submitForm()">
                    <?php for ($i = 1; $i <= 4; $i++): ?>
                        <option value="<?= $i ?>" <?= $travelers === $i ? 'selected' : '' ?>><?= $i ?> <?= htmlspecialchars($i === 1 ? t('traveler') : t('travelers_plural')) ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="setting-item">
                <label><?= htmlspecialchars(t('nights')) ?></label>
                <select name="nights" onchange="submitForm()">
                    <?php for ($n = 1; $n <= 7; $n++): ?>
                        <option value="<?= $n ?>" <?= $nights === $n ? 'selected' : '' ?>><?= $n ?> <?= htmlspecialchars($n === 1 ? t('night') : t('nights_plural')) ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="setting-item">
                <label><?= htmlspecialchars(t('currency')) ?></label>
                <select name="currency" onchange="submitForm()">
                    <?php foreach (['EUR' => 'EUR (€)', 'USD' => 'USD ($)', 'GBP' => 'GBP (£)', 'PLN' => 'PLN (zł)'] as $code => $label): ?>
                        <option value="<?= $code ?>" <?= $currency === $code ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="setting-item">
                <label><?= htmlspecialchars(t('weather_label')) ?></label>
                <select name="weather_mode" onchange="submitForm()">
                    <option value="LIVE" <?= $weatherMode === 'LIVE' ? 'selected' : '' ?>><?= htmlspecialchars(t('weather_live', ['cond' => $liveCondTranslated])) ?></option>
                    <option value="Sunny" <?= $weatherMode === 'Sunny' ? 'selected' : '' ?>><?= htmlspecialchars(t('weather_sunny')) ?></option>
                    <option value="Rainy" <?= $weatherMode === 'Rainy' ? 'selected' : '' ?>><?= htmlspecialchars(t('weather_rainy')) ?></option>
                </select>
            </div>
            <div class="setting-item">
                <label><?= htmlspecialchars(t('budget_label')) ?> <span id="budgetValue" class="budget-value"><?= $fmtPrice($budget) ?></span></label>
                <input type="range" name="budget" min="150" max="1500" step="25" value="<?= (int)$budget ?>" data-rate="<?= $fx['rate'] ?>" data-symbol="<?= htmlspecialchars($fx['symbol']) ?>" onchange="submitForm()">
            </div>
        </div>

        <label class="toggle">
            <input type="checkbox" name="is_student" <?= $isStudent ? 'checked' : '' ?> onchange="submitForm()">
            <span class="toggle-ui" aria-hidden="true"></span>
            <span class="toggle-text"><?= t('student_discount') ?></span>
        </label>

        <!-- STEP 1: INTERNATIONAL FLIGHTS -->
        <div class="step-title">
            <div><span class="step-badge">1</span> <?= htmlspecialchars(t('step_flights', ['city' => $city])) ?></div>
            <span class="sort-pill"><?= htmlspecialchars(t('sort_pill')) ?></span>
        </div>
        <div class="card-grid">
            <div class="choice-card <?= $flightId === 'AUTO' ? 'selected' : '' ?>" onclick="selectRadio('flight_id', 'AUTO')">
                <input type="radio" name="flight_id" value="AUTO" <?= $flightId === 'AUTO' ? 'checked' : '' ?>>
                <div class="card-select-area">
                    <span class="cheapest-ribbon"><?= htmlspecialchars(t('auto_select')) ?></span>
                    <div class="choice-top">
                        <strong><?= htmlspecialchars(t('auto_flight')) ?></strong>
                        <span class="choice-price"><?= htmlspecialchars(t('auto_flight_price')) ?></span>
                    </div>
                    <div class="choice-meta"><?= htmlspecialchars(t('natural_transform')) ?></div>
                </div>
            </div>

            <?php foreach ($cityFlights as $idx => $f):
                $cheapestPrice = round($f->cheapestOffer->priceEur * (1 - $discountRate), 2);
            ?>
                <div class="choice-card <?= $flightId === $f->id ? 'selected' : '' ?>">
                    <input type="radio" name="flight_id" value="<?= $f->id ?>" <?= $flightId === $f->id ? 'checked' : '' ?>>
                    <div class="card-select-area" onclick="selectRadio('flight_id', '<?= $f->id ?>')">
                        <?php if ($idx === 0): ?>
                            <span class="cheapest-ribbon"><?= htmlspecialchars(t('cheapest_flight_ribbon')) ?></span>
                        <?php else: ?>
                            <span style="font-size:0.75rem; color:var(--muted); font-weight:700;"><?= htmlspecialchars(t('option_number', ['num' => $idx + 1])) ?></span>
                        <?php endif; ?>
                        <div class="choice-top">
                            <strong><?= $f->icon ?> <?= htmlspecialchars($f->originCity) ?></strong>
                            <span class="choice-price"><?= $fmtPrice($cheapestPrice) ?></span>
                        </div>
                        <div class="choice-meta">
                            <?= htmlspecialchars($f->airline) ?> • <?= htmlspecialchars(t('flight_departure', ['time' => $f->departureTime])) ?> • <?= htmlspecialchars(t('best_price_at')) ?> <strong><?= htmlspecialchars($f->cheapestOffer->providerName) ?></strong>
                        </div>
                        <div>
                            <?= $f->arrivalHour >= 21
                                ? "<span class='tag-warn'>" . htmlspecialchars(t('flight_arrives_late', ['hour' => $f->arrivalHour])) . "</span>"
                                : "<span class='tag-ok'>" . htmlspecialchars(t('flight_arrives_on_time', ['hour' => $f->arrivalHour])) . "</span>" ?>
                        </div>
                    </div>

                    <details class="deals-drawer">
                        <summary>
                            <span><?= htmlspecialchars(t('view_fares', ['count' => count($f->sortedOffers->getItems())])) ?></span>
                            <span>▾</span>
                        </summary>
                        <?php foreach ($f->sortedOffers->getItems() as $oIdx => $offer):
                            $op = round($offer->priceEur * (1 - $discountRate), 2);
                        ?>
                            <div class="offer-row <?= $oIdx === 0 ? 'rank-1' : '' ?>">
                                <div>
                                    <strong>#<?= $oIdx + 1 ?> <?= htmlspecialchars($offer->providerName) ?></strong>
                                    <div style="font-size:0.74rem; color:var(--muted);"><?= htmlspecialchars($offer->perk) ?></div>
                                </div>
                                <div style="display:flex; align-items:center; gap:0.45rem;">
                                    <strong style="color:var(--lt-green);"><?= $fmtPrice($op) ?></strong>
                                    <a href="<?= htmlspecialchars($offer->bookingUrl) ?>" target="_blank" rel="noopener" class="book-btn"><?= htmlspecialchars(t('book_button')) ?></a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </details>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- STEP 2: ACCOMMODATIONS -->
        <div class="step-title">
            <div><span class="step-badge">2</span> <?= htmlspecialchars(t('step_hotels', ['city' => $city])) ?></div>
            <span class="sort-pill"><?= htmlspecialchars(t('sort_pill')) ?></span>
        </div>
        <div class="card-grid">
            <div class="choice-card <?= $hotelId === 'AUTO' ? 'selected' : '' ?>" onclick="selectRadio('hotel_id', 'AUTO')">
                <input type="radio" name="hotel_id" value="AUTO" <?= $hotelId === 'AUTO' ? 'checked' : '' ?>>
                <div class="card-select-area">
                    <span class="cheapest-ribbon"><?= htmlspecialchars(t('auto_select')) ?></span>
                    <div class="choice-top">
                        <strong><?= htmlspecialchars(t('auto_hotel')) ?></strong>
                        <span class="choice-price"><?= htmlspecialchars(t('auto_hotel_price')) ?></span>
                    </div>
                    <div class="choice-meta"><?= htmlspecialchars(t('auto_hotel_desc', ['city' => $city])) ?></div>
                </div>
            </div>

            <?php foreach ($cityHotels as $idx => $h):
                $bestNightly = $h->cheapestOffer->priceEur;
            ?>
                <div class="choice-card <?= $hotelId === $h->id ? 'selected' : '' ?>">
                    <input type="radio" name="hotel_id" value="<?= $h->id ?>" <?= $hotelId === $h->id ? 'checked' : '' ?>>
                    <div class="card-select-area" onclick="selectRadio('hotel_id', '<?= $h->id ?>')">
                        <?php if ($idx === 0): ?>
                            <span class="cheapest-ribbon"><?= htmlspecialchars(t('cheapest_hotel_ribbon')) ?></span>
                        <?php else: ?>
                            <span style="font-size:0.75rem; color:var(--muted); font-weight:700;"><?= htmlspecialchars(t('option_number', ['num' => $idx + 1])) ?></span>
                        <?php endif; ?>
                        <div class="choice-top">
                            <strong><?= $h->icon ?> <?= htmlspecialchars($h->name) ?></strong>
                            <span class="choice-price"><?= $fmtPrice($bestNightly) ?><?= htmlspecialchars(t('hotel_night')) ?></span>
                        </div>
                        <div class="choice-meta">
                            <?= htmlspecialchars($h->badge) ?> • <?= htmlspecialchars(t('best_price_at')) ?> <strong><?= htmlspecialchars($h->cheapestOffer->providerName) ?></strong>
                        </div>
                        <div>
                            <?= $h->lastCheckInHour < 24
                                ? "<span class='tag-warn'>" . htmlspecialchars(t('hotel_checkin', ['hour' => $h->lastCheckInHour])) . "</span>"
                                : "<span class='tag-ok'>" . htmlspecialchars(t('hotel_reception_24')) . "</span>" ?>
                        </div>
                    </div>

                    <details class="deals-drawer">
                        <summary>
                            <span><?= htmlspecialchars(t('hotel_view_platforms', ['count' => count($h->sortedOffers->getItems())])) ?></span>
                            <span>▾</span>
                        </summary>
                        <?php foreach ($h->sortedOffers->getItems() as $oIdx => $offer): ?>
                            <div class="offer-row <?= $oIdx === 0 ? 'rank-1' : '' ?>">
                                <div>
                                    <strong>#<?= $oIdx + 1 ?> <?= htmlspecialchars($offer->providerName) ?></strong>
                                    <div style="font-size:0.74rem; color:var(--muted);"><?= htmlspecialchars($offer->perk) ?></div>
                                </div>
                                <div style="display:flex; align-items:center; gap:0.45rem;">
                                    <strong style="color:var(--lt-green);"><?= $fmtPrice($offer->priceEur) ?><?= htmlspecialchars(t('hotel_night_short')) ?></strong>
                                    <a href="<?= htmlspecialchars($offer->bookingUrl) ?>" target="_blank" rel="noopener" class="book-btn"><?= htmlspecialchars(t('ver_button')) ?></a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </details>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- STEP 3: ACTIVITIES & EXPERIENCES -->
        <div class="step-title">
            <div><span class="step-badge">3</span> <?= htmlspecialchars(t('step_activities', ['city' => $city])) ?></div>
            <span class="sort-pill"><?= htmlspecialchars(t('sort_pill_activities')) ?></span>
        </div>
        <div class="card-grid">
            <?php foreach ($cityActivities as $idx => $act):
                $checked = in_array($act->id, $activityIds, true);
            ?>
                <div class="choice-card <?= $checked ? 'selected' : '' ?>">
                    <label class="card-select-area" style="display:block;">
                        <input type="checkbox" name="activity_ids[]" value="<?= $act->id ?>" <?= $checked ? 'checked' : '' ?> onchange="submitForm()">
                        <?php if ($idx === 0): ?>
                            <span class="cheapest-ribbon"><?= htmlspecialchars(t('featured_activity_ribbon')) ?></span>
                        <?php endif; ?>
                        <div class="choice-top">
                            <strong><?= $act->icon ?> <?= htmlspecialchars($act->title) ?></strong>
                            <span class="choice-price">+<?= $fmtPrice($act->pricePerPerson) ?></span>
                        </div>
                        <div class="choice-meta"><?= htmlspecialchars(t('activity_duration', ['duration' => $act->durationHours])) ?></div>
                        <div>
                            <?= $act->isOutdoor
                                ? "<span class='tag-warn'>" . htmlspecialchars(t('activity_outdoor')) . "</span>"
                                : "<span class='tag-ok'>" . htmlspecialchars(t('activity_indoor')) . "</span>" ?>
                        </div>
                    </label>
                    <div style="margin-top:0.55rem; border-top:1px dashed var(--line-strong); padding-top:0.45rem; display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-size:0.78rem; color:var(--muted);"><?= htmlspecialchars(t('activity_official_tickets')) ?></span>
                        <a href="<?= htmlspecialchars($act->bookingUrl) ?>" target="_blank" rel="noopener" class="book-btn"><?= htmlspecialchars(t('book_button')) ?></a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- RIGHT PANEL: BOARDING PASS SUMMARY & LIVE MATHEMATICAL PROOFS -->
    <div class="panel sticky-panel">
        <h2 class="pass-title"><?= htmlspecialchars(t('summary_title')) ?></h2>

        <?php if ($result->isRight()):
            /** @var Itinerary $trip */
            $trip = $result->getValue();
            $pathMetrics = $trip->inducedPath->evaluateMetrics();
            $totalEur = $pathMetrics->costEur;
            $pct = min(100, round(($totalEur / $trip->maxBudget) * 100));

            // Live Proof 1: Monoid Associativity (F ⊕ H) ⊕ A === F ⊕ (H ⊕ A)
            $leftAssoc = ($trip->flightMetrics->append($trip->hotelMetrics))->append($trip->activityMetrics);
            $rightAssoc = $trip->flightMetrics->append($trip->hotelMetrics->append($trip->activityMetrics));

            // Live Proof 2: Naturality Square on Selected Flight Offers
            $fnDisc = fn(BookingOffer $o) => round($o->priceEur * (1 - $discountRate), 2);
            $natPath1 = RouteList::headOption($trip->flight->sortedOffers)->fmap($fnDisc)->getOrElse(0.0);
            $natPath2 = RouteList::headOption($trip->flight->sortedOffers->fmap($fnDisc))->getOrElse(0.0);
        ?>
            <div class="pass-route">
                <div>
                    <span class="iata"><?= htmlspecialchars($trip->flight->originIata) ?></span>
                    <small><?= htmlspecialchars(preg_replace('/\s*\([^)]*\)/', '', $trip->flight->originCity)) ?></small>
                </div>
                <div class="pass-line" aria-hidden="true"><span>✈</span></div>
                <div>
                    <span class="iata"><?= htmlspecialchars($trip->flight->destIata) ?></span>
                    <small><?= htmlspecialchars($trip->flight->destinationCity) ?></small>
                </div>
            </div>

            <div class="pass-meta">
                <div><small><?= htmlspecialchars(t('pass_date')) ?></small><strong><?= htmlspecialchars(date('d/m/Y', strtotime($travelDate) ?: time())) ?></strong></div>
                <div><small><?= htmlspecialchars(t('pass_departure')) ?></small><strong><?= htmlspecialchars($trip->flight->departureTime) ?></strong></div>
                <div><small><?= htmlspecialchars(t('pass_travelers')) ?></small><strong><?= $trip->travelers ?></strong></div>
                <div><small><?= htmlspecialchars(t('pass_nights')) ?></small><strong><?= $trip->nights ?></strong></div>
            </div>

            <!-- VISUAL FREE CATEGORY PATH F(G) DIAGRAM -->
            <div class="graph-inductor-box">
                <small><?= htmlspecialchars(t('graph_box_title', [
                    'src' => $trip->inducedPath->source->id,
                    'tgt' => $trip->inducedPath->target->id
                ])) ?></small>
                <div class="graph-chain"><?= htmlspecialchars($trip->inducedPath->toSignature()) ?></div>
            </div>

            <div class="pass-tear" aria-hidden="true"></div>

            <div class="ticket-ok">
                <div style="font-weight:700; color:#065f46;"><?= htmlspecialchars(t('itinerary_ok')) ?></div>
                <p style="margin:0.25rem 0 0; font-size:0.86rem; color:#047857;">
                    <?= htmlspecialchars(t('itinerary_ok_text')) ?>
                </p>
            </div>

            <div style="margin-top:1rem;">
                <div style="display:flex; justify-content:space-between; font-size:0.84rem; font-weight:600;">
                    <span><?= htmlspecialchars(t('budget_used', ['used' => $fmtPrice($totalEur), 'max' => $fmtPrice($trip->maxBudget)])) ?></span>
                    <span><?= $pct ?>%</span>
                </div>
                <div class="budget-meter"><div class="budget-fill <?= $pct >= 90 ? 'danger' : ($pct >= 70 ? 'warn' : '') ?>" style="width:<?= $pct ?>%;"></div></div>
            </div>

            <ul class="receipt-list">
                <?php foreach ($trip->metrics->items as $item): ?>
                    <li>
                        <div class="receipt-line-top">
                            <span><strong><?= htmlspecialchars($item['label']) ?></strong></span>
                            <strong style="color:var(--lt-green);"><?= $fmtPrice($item['cost']) ?></strong>
                        </div>
                        <div class="receipt-sub">
                            <span><?= htmlspecialchars(t('receipt_provider')) ?> <strong><?= htmlspecialchars($item['provider']) ?></strong></span>
                            <a href="<?= htmlspecialchars($item['url']) ?>" target="_blank" rel="noopener" class="book-btn">
                                <?= htmlspecialchars(t('receipt_book', ['provider' => $item['provider']])) ?>
                            </a>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="total-row">
                <span><?= htmlspecialchars(t('total_final', ['currency' => $currency])) ?></span>
                <span><?= $fmtPrice($totalEur) ?></span>
            </div>
            <div style="font-size:0.82rem; color:var(--muted); margin-top:0.4rem; text-align:right;">
                <?= htmlspecialchars(t('total_metrics_footer', ['hours' => $trip->metrics->durationHours, 'points' => $trip->metrics->points])) ?>
            </div>

            <details class="prof-box" open>
                <summary><?= htmlspecialchars(t('professor_mode')) ?></summary>
                <div style="margin-top:0.5rem;">
                    <div class="trace-item" style="color:#fde047; font-weight:700;">
                        ✔ <?= htmlspecialchars(t('graph_inductor_proof', [
                            'src' => $trip->inducedPath->source->id,
                            'tgt' => $trip->inducedPath->target->id,
                            'total' => $fmtPrice($pathMetrics->costEur)
                        ])) ?>
                    </div>
                    <div class="trace-item">✔ <?= htmlspecialchars(t('weather_verification', ['source' => $liveWeather['source'], 'temp' => $liveWeather['temp'], 'cond' => $liveCondTranslated])) ?></div>
                    <div class="trace-item">✔ <?= htmlspecialchars(t('currency_verification', ['rate' => (string)$fx['rate'], 'currency' => $currency])) ?></div>
                    <?php foreach ($trip->trace as $tr): ?>
                        <div class="trace-item">✔ <?= htmlspecialchars($tr) ?></div>
                    <?php endforeach; ?>
                    <div class="trace-item" style="color:#38bdf8; font-weight:600;">
                        ✔ <?= htmlspecialchars(t('naturality_proof', ['val1' => $fmtPrice($natPath1), 'val2' => $fmtPrice($natPath2)])) ?>
                    </div>
                    <div class="trace-item" style="color:#4ade80; font-weight:600;">
                        ✔ <?= htmlspecialchars(t('associativity_proof', [
                            'left' => $fmtPrice($leftAssoc->costEur),
                            'right' => $fmtPrice($rightAssoc->costEur),
                            'hours' => $leftAssoc->durationHours
                        ])) ?>
                    </div>
                </div>
            </details>
        <?php else:
            $err = $result->getValue();
        ?>
            <div class="ticket-err">
                <div style="font-size:0.78rem; font-weight:700; text-transform:uppercase; color:#b91c1c;"><?= htmlspecialchars($err['step']) ?></div>
                <h3 style="margin:0.3rem 0; color:#991b1b; font-size:1.3rem;"><?= htmlspecialchars($err['title']) ?></h3>
                <p style="margin:0.35rem 0; font-size:0.92rem; color:#7f1d1d;"><?= htmlspecialchars($err['msg']) ?></p>
                <div style="margin-top:0.7rem; background:#fff; padding:0.65rem 0.8rem; border-radius:8px; font-size:0.86rem; border-left:4px solid var(--lt-yellow);">
                    <strong><?= htmlspecialchars(t('error_fix_label')) ?></strong> <?= htmlspecialchars($err['fix']) ?>
                </div>
            </div>

            <details class="prof-box" open>
                <summary><?= htmlspecialchars(t('professor_mode')) ?></summary>
                <div style="margin-top:0.5rem;">
                    <div class="trace-item" style="color:#fca5a5;">
                        ✖ Either::Left Short-Circuit — <?= htmlspecialchars(t('monad_short_circuit_explain')) ?>
                    </div>
                </div>
            </details>
        <?php endif; ?>
    </div>
</form>

<script src="js/main.js?v=7.0"></script>
</body>
</html>
