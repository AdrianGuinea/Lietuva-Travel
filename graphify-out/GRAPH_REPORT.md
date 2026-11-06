# Graph Report - Adrian.Guinea  (2026-11-06)

## Corpus Check
- 1 files · ~12,925 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 223 nodes · 409 edges · 9 communities (8 shown, 1 thin omitted)
- Extraction: 96% EXTRACTED · 4% INFERRED · 0% AMBIGUOUS · INFERRED: 17 edges (avg confidence: 0.8)
- Token cost: 0 input · 0 output

## Community Hubs (Navigation)
- Itinerary Pipeline & Live Data (index.php)
- Free Category & Monad/Monoid Core
- LaTeX Report Documentation
- Either Monad & Law Tests
- Regression Check Suite
- Frontend JS
- README Category Theory Docs
- RouteList & Booking Construction
- Favicon Assets

## God Nodes (most connected - your core abstractions)
1. `Option` - 19 edges
2. `Either` - 18 edges
3. `TripMetrics` - 13 edges
4. `Some` - 13 edges
5. `RouteList` - 13 edges
6. `Right` - 12 edges
7. `Lietuva Travel v6.2 (Graph Inductor Edition)` - 12 edges
8. `FreeCategoryPath` - 11 edges
9. `Left` - 11 edges
10. `None` - 10 edges

## Surprising Connections (you probably didn't know these)
- `{closure#13}()` --references--> `FreeCategoryPath`  [EXTRACTED]
  tests/category_laws_test.php → src/CategoryTheory.php
- `{closure#10}()` --calls--> `Left`  [INFERRED]
  tests/category_laws_test.php → src/CategoryTheory.php
- `{closure#11}()` --calls--> `Left`  [INFERRED]
  tests/category_laws_test.php → src/CategoryTheory.php
- `{closure#10}()` --calls--> `Right`  [INFERRED]
  tests/category_laws_test.php → src/CategoryTheory.php
- `{closure#11}()` --calls--> `Right`  [INFERRED]
  tests/category_laws_test.php → src/CategoryTheory.php

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Category Theory Foundations (Category, Functor, Natural Transformation, Monad)** — latex_report_maindocument_en_category, latex_report_maindocument_en_functor, latex_report_maindocument_en_natural_transformation, latex_report_maindocument_en_monad [EXTRACTED 1.00]
- **Live HTTP Verification Against Two Real External Services** — latex_report_maindocument_en_fetchlivejsonoption, latex_report_maindocument_en_open_meteo_service, latex_report_maindocument_en_frankfurter_ecb_service [EXTRACTED 1.00]
- **Category Theory Architecture Components** — readme_category_theory, readme_free_category_inductor, readme_functors, readme_monads, readme_monoids, readme_natural_transformations [EXTRACTED 1.00]
- **Validation Pipeline Steps** — readme_validation_pipeline, readme_flight_availability, readme_hotel_checkin_compatibility, readme_weather_suitability, readme_budget_compliance [EXTRACTED 1.00]
- **Defect-Fix-Verification Cycle for the Three Reported Laws** — latex_report_maindocument_en_option_pure_right_identity_defect, latex_report_maindocument_en_tripmetrics_associativity_defect, latex_report_maindocument_en_itinerary_advance_swallowed_composition_defect, latex_report_maindocument_en_category_laws_test_php [INFERRED 0.85]

## Communities (9 total, 1 thin omitted)

### Community 0 - "Itinerary Pipeline & Live Data (index.php)"
Cohesion: 0.08
Nodes (31): {closure#10}(), {closure#11}(), {closure#12}(), {closure#13}(), {closure#14}(), {closure#15}(), {closure#16}(), {closure#17}() (+23 more)

### Community 1 - "Free Category & Monad/Monoid Core"
Cohesion: 0.10
Nodes (16): Activity, BookingOffer, {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), Flight, FreeCategoryPath (+8 more)

### Community 2 - "LaTeX Report Documentation"
Cohesion: 0.09
Nodes (28): Vilnius University Seal (Universitas Vilnensis, 1579), Barr & Wells - Category Theory for Computing Science (1990), Category (objects + composable morphisms), category_laws_test.php Regression Suite (18/18 laws), src/CategoryTheory.php Core Classes, Either Monad (Right/Left), fetchLiveJsonOption(), Frankfurter/ECB Currency Conversion Service (+20 more)

### Community 3 - "Either Monad & Law Tests"
Cohesion: 0.11
Nodes (7): Either, Left, Right, {closure#10}(), {closure#11}(), {closure#13}(), {closure#9}()

### Community 4 - "Regression Check Suite"
Cohesion: 0.11
Nodes (8): {closure#1}(), {closure#2}(), None, Option, Some, {closure#5}(), {closure#6}(), {closure#7}()

### Community 5 - "Frontend JS"
Cohesion: 0.13
Nodes (16): changeLanguage(), loadScenario(), selectCity(), selectRadio(), submitForm(), Budget Compliance, css/style.css, Flight Availability and Pricing (+8 more)

### Community 6 - "README Category Theory Docs"
Cohesion: 0.19
Nodes (13): Category Theory Laws Test Suite (18/18), Category Theory Architecture, Either Monad, Free Category Inductor F(G), Functors, Kleisli Pipeline, Monads (Option/Either), Monoids (+5 more)

### Community 8 - "Favicon Assets"
Cohesion: 0.83
Nodes (4): Airplane Icon, Site Favicon (Lithuania Travel), Dashed Flight Route Path, Lithuanian Flag

## Knowledge Gaps
- **13 isolated node(s):** `Frankfurter/ECB Currency Conversion Service`, `Monoid (associative op + identity)`, `Open-Meteo Weather Service`, `Short-circuit (Either::bind propagating Left)`, `Vilnius University Seal (Universitas Vilnensis, 1579)` (+8 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 55 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **1 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Lietuva Travel v6.2 (Graph Inductor Edition)` connect `Frontend JS` to `Itinerary Pipeline & Live Data (index.php)`, `Free Category & Monad/Monoid Core`, `Either Monad & Law Tests`, `README Category Theory Docs`?**
  _High betweenness centrality (0.369) - this node is a cross-community bridge._
- **Are the 3 inferred relationships involving `Some` (e.g. with `{closure#1}()` and `{closure#6}()`) actually correct?**
  _`Some` has 3 INFERRED edges - model-reasoned connections that need verification._
- **What connects `Frankfurter/ECB Currency Conversion Service`, `Monoid (associative op + identity)`, `Open-Meteo Weather Service` to the rest of the system?**
  _13 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Itinerary Pipeline & Live Data (index.php)` be split into smaller, more focused modules?**
  _Cohesion score 0.08080808080808081 - nodes in this community are weakly interconnected._
- **Why does `Option` connect `Regression Check Suite` to `Free Category & Monad/Monoid Core`, `RouteList & Booking Construction`?**
  _High betweenness centrality (0.088) - this node is a cross-community bridge._
- **Should `Free Category & Monad/Monoid Core` be split into smaller, more focused modules?**
  _Cohesion score 0.09966777408637874 - nodes in this community are weakly interconnected._
- **Why does `Category Theory Architecture` connect `README Category Theory Docs` to `Free Category & Monad/Monoid Core`, `Frontend JS`?**
  _High betweenness centrality (0.083) - this node is a cross-community bridge._