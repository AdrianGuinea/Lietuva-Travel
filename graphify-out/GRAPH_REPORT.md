# Graph Report - Adrian.Guinea  (2026-11-12)

## Corpus Check
- 10 files · ~20,794 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 17 file(s) not represented in the graph (top: .tex 11, (none) 3, .sty 1)

## Summary
- 259 nodes · 486 edges · 20 communities (11 shown, 9 thin omitted)
- Extraction: 87% EXTRACTED · 13% INFERRED · 0% AMBIGUOUS · INFERRED: 63 edges (avg confidence: 0.83)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `0d50afaf`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Travel Options & RouteList
- CategoryTheory.php
- Submission Docs Overview
- Option Monad Implementation
- Report: Monad Laws & Credits
- Either Monad Implementation
- Report: Live API & Option
- Report: Free Category Core
- Frontend JS Interactions
- Favicon Icon Assets
- Functor Mapping
- Kaunas City Photos
- Klaipeda City Photos
- Vilnius City Photos
- VU Seal Logo
- TripMetrics Identity Element
- TripMetrics

## God Nodes (most connected - your core abstractions)
1. `Either` - 24 edges
2. `Option` - 20 edges
3. `TripMetrics` - 19 edges
4. `RouteList` - 17 edges
5. `Right` - 16 edges
6. `Some` - 14 edges
7. `Left` - 14 edges
8. `GraphNode` - 13 edges
9. `Flight` - 13 edges
10. `Itinerary` - 13 edges

## Surprising Connections (you probably didn't know these)
- `Validation Pipeline` --semantically_similar_to--> `Graceful Degradation via Option/Either when remote APIs are unreachable`  [INFERRED] [semantically similar]
  README.md → bin/EJECUCION.txt
- `{closure#14}()` --calls--> `Right`  [INFERRED]
  src/index.php → src/CategoryTheory.php
- `{closure#18}()` --calls--> `Right`  [INFERRED]
  src/index.php → src/CategoryTheory.php
- `{closure#19}()` --calls--> `Right`  [INFERRED]
  src/index.php → src/CategoryTheory.php
- `{closure#21}()` --calls--> `Right`  [INFERRED]
  src/index.php → src/CategoryTheory.php

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Submission Deliverable Package (doc/bin/src)** — readme_txt_contenido_entrega, bin_ejecucion_txt_instructions, readme_maindocument_en_pdf [EXTRACTED 0.90]
- **Defect report, diagnosis, fix and regression-verification cycle** — doc_maindocument_en_option_pure, doc_maindocument_en_tripmetrics_append, doc_maindocument_en_itinerary_advance, doc_maindocument_en_category_laws_test_php [EXTRACTED 1.00]
- **Directed travel graph induces the free category F(G) via path composition** — doc_maindocument_en_category, doc_maindocument_en_free_category_f_g, doc_maindocument_en_freecategorypath_compose, doc_maindocument_en_itinerary_advance, doc_maindocument_en_graphedge [EXTRACTED 1.00]
- **Live HTTP verification of the Option::pure fix against external services** — doc_maindocument_en_fetchlivejsonoption, doc_maindocument_en_open_meteo, doc_maindocument_en_frankfurter_ecb, doc_maindocument_en_option_pure [EXTRACTED 1.00]
- **Graceful Degradation of External APIs via Option/Either** — readme_open_meteo, readme_frankfurter_api, bin_ejecucion_txt_option_either_fallback [INFERRED 0.75]
- **Category Theory Implementation and Verification** — readme_category_theory, readme_categorytheory_php, readme_category_laws_test_suite [INFERRED 0.80]

## Communities (20 total, 9 thin omitted)

### Community 0 - "Travel Options & RouteList"
Cohesion: 0.10
Nodes (25): Activity, Flight, Hotel, {closure#10}(), {closure#12}(), {closure#13}(), {closure#15}(), {closure#16}() (+17 more)

### Community 1 - "CategoryTheory.php"
Cohesion: 0.11
Nodes (12): BookingOffer, {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), Functor, Monad, Monoid (+4 more)

### Community 2 - "Submission Docs Overview"
Cohesion: 0.10
Nodes (31): Frankfurter / ECB API (remote resource), EJECUCION.txt (Execution Instructions), Open-Meteo API (remote resource), Requirement: PHP 8.1+ with allow_url_fopen, Run Command: php -S localhost:8001 (from src/), Category Laws Test Suite (18/18 laws, category_laws_test.php), Category Theory Architecture, src/CategoryTheory.php (Core categorical logic and Graph Inductors) (+23 more)

### Community 3 - "Option Monad Implementation"
Cohesion: 0.10
Nodes (11): None, Option, Some, fetchLiveJsonOption(), getLiveCurrencyRate(), getLiveLithuanianWeather(), {closure#1}(), {closure#2}() (+3 more)

### Community 4 - "Report: Monad Laws & Credits"
Cohesion: 0.07
Nodes (31): Adrián Guinea Pérez, Barr and Wells (category theory reference), tests/category_laws_test.php, Claude Code (Anthropic), Either (Right/Left monad), Error Propagation as a Categorical Obligation, FreeCategoryPath::compose, FreeCategoryPath::evaluateMetrics (+23 more)

### Community 5 - "Either Monad Implementation"
Cohesion: 0.10
Nodes (7): Either, Left, Right, {closure#10}(), {closure#11}(), {closure#13}(), {closure#9}()

### Community 6 - "Report: Live API & Option"
Cohesion: 0.15
Nodes (13): fetchLiveJsonOption, Frankfurter/ECB (currency conversion service), getLiveCurrencyRate, getLiveLithuanianWeather, Natural Transformation, None (final class extends Option), None::bind, Open-Meteo (weather service) (+5 more)

### Community 7 - "Report: Free Category Core"
Cohesion: 0.29
Nodes (7): Category, Free Category F(G), FreeCategoryPath, FreeCategoryPath::fromEdge, FreeCategoryPath::identity, GraphEdge, GraphNode

### Community 8 - "Frontend JS Interactions"
Cohesion: 0.60
Nodes (5): changeLanguage(), loadScenario(), selectCity(), selectRadio(), submitForm()

### Community 9 - "Favicon Icon Assets"
Cohesion: 0.83
Nodes (4): Airplane Icon, Site Favicon (Lithuania Travel), Dashed Flight Route Path, Lithuanian Flag

### Community 19 - "TripMetrics"
Cohesion: 0.19
Nodes (12): FreeCategoryPath, GraphEdge, GraphNode, Itinerary, TripMetrics, {closure#14}(), {closure#18}(), {closure#19}() (+4 more)

## Knowledge Gaps
- **43 isolated node(s):** `RouteList::fmap`, `Functor`, `Kaunas City Photo`, `Kaunas Town Hall Tower Thumbnail`, `Klaipeda City Aerial Photo` (+38 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 84 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **9 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Option` connect `Option Monad Implementation` to `CategoryTheory.php`?**
  _High betweenness centrality (0.195) - this node is a cross-community bridge._
- **Are the 6 inferred relationships involving `TripMetrics` (e.g. with `{closure#14}()` and `{closure#18}()`) actually correct?**
  _`TripMetrics` has 6 INFERRED edges - model-reasoned connections that need verification._
- **What connects `RouteList::fmap`, `Functor`, `Kaunas City Photo` to the rest of the system?**
  _43 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Travel Options & RouteList` be split into smaller, more focused modules?**
  _Cohesion score 0.0967741935483871 - nodes in this community are weakly interconnected._
- **Why does `Lietuva Travel v6.2 (Graph Inductor Edition)` connect `Submission Docs Overview` to `Option Monad Implementation`?**
  _High betweenness centrality (0.144) - this node is a cross-community bridge._
- **Should `CategoryTheory.php` be split into smaller, more focused modules?**
  _Cohesion score 0.11491935483870967 - nodes in this community are weakly interconnected._
- **Should `Submission Docs Overview` be split into smaller, more focused modules?**
  _Cohesion score 0.1028225806451613 - nodes in this community are weakly interconnected._