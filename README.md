# Lietuva Travel v6.2 (Graph Inductor Edition)

A travel planning application that implements Category Theory concepts (Free Category Graph Inductors, Functors, Monads, Monoids) to build type-safe, composable travel itineraries.

## Overview

Lietuva Travel helps users plan trips to Lithuanian destinations (Vilnius, Kaunas, Klaipėda) by combining flights, accommodations, and activities. The application uniquely applies Category Theory principles to ensure data integrity and provide clear error handling through a categorical pipeline.

## Features

- **Live Data Integration**: Real-time weather (Open-Meteo) and currency exchange rates (Frankfurter API)
- **Category Theory Architecture**:
  - Directed Graph & Free Category Inductors F(G)
  - Functors for data transformation
  - Option/Either Monads for error handling
  - Monoids for associative cost calculation
  - Natural Transformations for converting collections to options
- **Mathematical Verification**: Built-in test suite verifying 15/15 Category Theory laws.
- **Interactive UI**: Select flights, hotels, and activities with real-time pricing and visual F(G) path traces.
- **Validation Pipeline**: Step-by-step validation ensuring:
  - Flight availability and pricing
  - Hotel check-in time compatibility
  - Weather suitability for activities
  - Budget compliance
- **Multilingual Support**: English, Spanish, and Lithuanian localizations.

## Category Theory Concepts Implemented

### Free Category Inductor F(G) (Prof. Irus Grinis Model)
- **Vertices Ob(F(G))**: Travel states (e.g., Origin Hub -> Destination Airport -> Hotel Check-in -> Activities).
- **Morphisms Hom(u, v)**: Composable paths of directed travel edges. Ensures an itinerary is a connected, valid walk through the travel graph.

### Functors
- `RouteList`: Maps functions over collections of travel options.
- Used for applying discounts to all providers simultaneously.

### Monads
- `Option`: Handles missing values (no flights/hotels available).
- `Either`: Represents success/failure with error context.
- Used in the Kleisli pipeline for step-by-step itinerary building and short-circuiting invalid graph edges.

### Monoids
- `TripMetrics`: Combines costs, durations, and points associatively.
- Used to aggregate activity costs and evaluate the strict monoidal functor over the induced graph path.

### Natural Transformations
- `RouteList::headOption()`: Converts a list of options to the best option.
- Used to select the cheapest flight/hotel from available choices.

## File Structure

Lietuva-Travel/
├── index.php                 # Main application logic and HTML UI
├── src/
│   └── CategoryTheory.php    # Core categorical logic and Graph Inductors
├── tests/
│   └── category_laws_test.php# 15/15 Mathematical laws test suite
├── lang/
│   ├── en.php                # English translations
│   ├── es.php                # Spanish translations
│   └── lt.php                # Lithuanian translations
├── css/
│   └── style.css             # Application styling
├── js/
│   └── main.js               # Frontend interactions
├── img/
│   └── favicon.svg           # Application icons
└── README.md                 # This file

## Setup Requirements

- PHP 8.1+ with `allow_url_fopen` enabled
- Web server (Apache/Nginx/built-in PHP server)
- Internet connection for live API data

## Usage

1. Start the PHP server: `php -S localhost:8001`
2. Access via browser: `http://localhost:8001`
3. Run mathematical proofs: `php tests/category_laws_test.php`
4. The application validates each step and shows:
   - Selected itinerary with pricing breakdown and visual graph path.
   - Any validation errors with suggested fixes and categorical traces.

## License

MIT License - Feel free to use and modify for educational purposes.
