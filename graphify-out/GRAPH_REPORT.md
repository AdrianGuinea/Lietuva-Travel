# Graph Report - Adrian.Guinea  (2026-11-05)

## Corpus Check
- Corpus is ~11,623 words - fits in a single context window. You may not need a graph.

## Summary
- 205 nodes · 425 edges · 9 communities
- Extraction: 90% EXTRACTED · 10% INFERRED · 0% AMBIGUOUS · INFERRED: 43 edges (avg confidence: 0.83)
- Token cost: 156,611 input · 0 output

## Community Hubs (Navigation)
- index.php UI Flow Controller
- LaTeX Report: Defects & Fixes
- Either Monad Implementation
- Option Monad & RouteList
- Free Category Graph Engine
- Frontend & Booking Validation
- CategoryTheory.php Core Abstractions
- Category Theory Concepts (README)
- Favicon Iconography

## God Nodes (most connected - your core abstractions)
1. `Either` - 24 edges
2. `Option` - 18 edges
3. `Right` - 16 edges
4. `RouteList` - 16 edges
5. `TripMetrics` - 15 edges
6. `Left` - 14 edges
7. `Some` - 13 edges
8. `Flight` - 13 edges
9. `{closure#20}()` - 12 edges
10. `None` - 12 edges

## Surprising Connections (you probably didn't know these)
- `fetchLiveJsonOption()` --calls--> `None`  [INFERRED]
  index.php → src/CategoryTheory.php
- `fetchLiveJsonOption()` --calls--> `Some`  [INFERRED]
  index.php → src/CategoryTheory.php
- `{closure#15}()` --calls--> `Right`  [INFERRED]
  index.php → src/CategoryTheory.php
- `{closure#19}()` --calls--> `Left`  [INFERRED]
  index.php → src/CategoryTheory.php
- `{closure#19}()` --calls--> `Right`  [INFERRED]
  index.php → src/CategoryTheory.php

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Category Theory Architecture Components** — readme_category_theory, readme_free_category_inductor, readme_functors, readme_monads, readme_monoids, readme_natural_transformations [EXTRACTED 1.00]
- **Validation Pipeline Steps** — readme_validation_pipeline, readme_flight_availability, readme_hotel_checkin_compatibility, readme_weather_suitability, readme_budget_compliance [EXTRACTED 1.00]
- **Category Theory Foundations (Category, Functor, Natural Transformation, Monad)** — latex_report_maindocument_en_category, latex_report_maindocument_en_functor, latex_report_maindocument_en_natural_transformation, latex_report_maindocument_en_monad [EXTRACTED 1.00]
- **Defect-Fix-Verification Cycle for the Three Reported Laws** — latex_report_maindocument_en_option_pure_right_identity_defect, latex_report_maindocument_en_tripmetrics_associativity_defect, latex_report_maindocument_en_itinerary_advance_swallowed_composition_defect, latex_report_maindocument_en_category_laws_test_php [INFERRED 0.85]
- **Live HTTP Verification Against Two Real External Services** — latex_report_maindocument_en_fetchlivejsonoption, latex_report_maindocument_en_open_meteo_service, latex_report_maindocument_en_frankfurter_ecb_service [EXTRACTED 1.00]

## Communities (9 total, 0 thin omitted)

### Community 0 - "index.php UI Flow Controller"
Cohesion: 0.08
Nodes (28): {closure#10}(), {closure#11}(), {closure#13}(), {closure#14}(), {closure#16}(), {closure#17}(), {closure#18}(), {closure#21}() (+20 more)

### Community 1 - "LaTeX Report: Defects & Fixes"
Cohesion: 0.09
Nodes (28): Vilnius University Seal (Universitas Vilnensis, 1579), Barr & Wells - Category Theory for Computing Science (1990), Category (objects + composable morphisms), category_laws_test.php Regression Suite (18/18 laws), src/CategoryTheory.php Core Classes, Either Monad (Right/Left), fetchLiveJsonOption(), Frankfurter/ECB Currency Conversion Service (+20 more)

### Community 2 - "Either Monad Implementation"
Cohesion: 0.10
Nodes (7): Either, Left, Right, {closure#10}(), {closure#11}(), {closure#13}(), {closure#9}()

### Community 3 - "Option Monad & RouteList"
Cohesion: 0.13
Nodes (7): None, Option, RouteList, Some, {closure#5}(), {closure#6}(), {closure#7}()

### Community 4 - "Free Category Graph Engine"
Cohesion: 0.24
Nodes (9): {closure#15}(), {closure#19}(), {closure#20}(), {closure#22}(), FreeCategoryPath, GraphEdge, GraphNode, Itinerary (+1 more)

### Community 5 - "Frontend & Booking Validation"
Cohesion: 0.13
Nodes (16): changeLanguage(), loadScenario(), selectCity(), selectRadio(), submitForm(), Budget Compliance, css/style.css, Flight Availability and Pricing (+8 more)

### Community 6 - "CategoryTheory.php Core Abstractions"
Cohesion: 0.16
Nodes (10): {closure#12}(), {closure#27}(), BookingOffer, {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), Functor (+2 more)

### Community 7 - "Category Theory Concepts (README)"
Cohesion: 0.19
Nodes (13): Category Theory Laws Test Suite (18/18), Category Theory Architecture, Either Monad, Free Category Inductor F(G), Functors, Kleisli Pipeline, Monads (Option/Either), Monoids (+5 more)

### Community 8 - "Favicon Iconography"
Cohesion: 0.83
Nodes (4): Airplane Icon, Site Favicon (Lithuania Travel), Dashed Flight Route Path, Lithuanian Flag

## Knowledge Gaps
- **13 isolated node(s):** `Prof. Irus Grinis Model`, `Option Monad`, `Either Monad`, `TripMetrics`, `Flight Availability and Pricing` (+8 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 48 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Lietuva Travel v6.2 (Graph Inductor Edition)` connect `Frontend & Booking Validation` to `index.php UI Flow Controller`, `Either Monad Implementation`, `CategoryTheory.php Core Abstractions`, `Category Theory Concepts (README)`?**
  _High betweenness centrality (0.187) - this node is a cross-community bridge._
- **Are the 6 inferred relationships involving `Right` (e.g. with `{closure#15}()` and `{closure#19}()`) actually correct?**
  _`Right` has 6 INFERRED edges - model-reasoned connections that need verification._
- **What connects `Prof. Irus Grinis Model`, `Option Monad`, `Either Monad` to the rest of the system?**
  _13 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `index.php UI Flow Controller` be split into smaller, more focused modules?**
  _Cohesion score 0.07965860597439545 - nodes in this community are weakly interconnected._
- **Why does `Category Theory Architecture` connect `Category Theory Concepts (README)` to `Frontend & Booking Validation`, `CategoryTheory.php Core Abstractions`?**
  _High betweenness centrality (0.089) - this node is a cross-community bridge._
- **Should `LaTeX Report: Defects & Fixes` be split into smaller, more focused modules?**
  _Cohesion score 0.09475806451612903 - nodes in this community are weakly interconnected._
- **Why does `Either` connect `Either Monad Implementation` to `index.php UI Flow Controller`, `Option Monad & RouteList`, `Free Category Graph Engine`, `CategoryTheory.php Core Abstractions`?**
  _High betweenness centrality (0.066) - this node is a cross-community bridge._