# Lietuva Travel v5.0

A travel planning application that implements Category Theory concepts (Functors, Monads, Monoids) to build type-safe, composable travel itineraries.

## Overview

Lietuva Travel helps users plan trips to Lithuanian destinations (Vilnius, Kaunas, Klaipėda) by combining flights, accommodations, and activities. The application uniquely applies Category Theory principles to ensure data integrity and provide clear error handling through a categorical pipeline.

## Features

- **Live Data Integration**: Real-time weather (Open-Meteo) and currency exchange rates (Frankfurter API)
- **Category Theory Architecture**:
  - Functors for data transformation
  - Option/Either Monads for error handling
  - Monoids for associative cost calculation
  - Natural Transformations for converting collections to options
- **Interactive UI**: Select flights, hotels, and activities with real-time pricing
- **Validation Pipeline**: Step-by-step validation ensuring:
  - Flight availability and pricing
  - Hotel check-in time compatibility
  - Weather suitability for activities
  - Budget compliance
- **Responsive Design**: Works on mobile and desktop devices

## Category Theory Concepts Implemented

### Functors
- `RouteList`: Maps functions over collections of travel options
- Used for applying discounts to all providers simultaneously

### Monads
- `Option`: Handles missing values (no flights/hotels available)
- `Either`: Represents success/failure with error context
- Used in the Kleisli pipeline for step-by-step itinerary building

### Monoids
- `TripMetrics`: Combines costs, durations, and points associatively
- Used to aggregate activity costs regardless of grouping

### Natural Transformations
- `RouteList::pickCheapest()`: Converts a list of options to the best option
- Used to select the cheapest flight/hotel from available choices

## File Structure

```
V5/
├── index.php          # Main application logic and HTML
├── css/
│   └── style.css      # All styling (extracted from inline)
├── js/
│   └── main.js        # JavaScript functions (extracted from inline)
└── README.md          # This file
```

## Setup Requirements

- PHP 7.4+ with `allow_url_fopen` enabled
- Web server (Apache/Nginx/built-in PHP server)
- Internet connection for live API data

## Usage

1. Place all files in your web server's document root
2. Access via browser: `http://your-domain/V5/`
3. Select travel parameters:
   - Destination city (Vilnius, Kaunas, Klaipėda)
   - Origin airport (specific or "All origins")
   - Travel date
   - Number of travelers and nights
   - Budget
   - Student discount (optional)
   - Activity preferences
4. The application validates each step and shows:
   - Selected itinerary with pricing breakdown
   - Any validation errors with suggested fixes
   - Live weather and currency information

## API Dependencies

- [Open-Meteo](https://open-meteo.com/) for weather data
- [Frankfurter](https://www.frankfurter.app/) for exchange rates
- [GetYourGuide](https://www.getyourguide.com/) for activity links (static)

## Design Philosophy

This application demonstrates how Category Theory provides practical benefits in web development:

1. **Type Safety**: Monads prevent null pointer errors through explicit handling
2. **Composability**: Kleisli arrows allow chaining operations with automatic error propagation
3. **Associativity**: Monoids ensure consistent results regardless of operation grouping
4. **Abstraction**: Functors enable reusable transformation logic

The categorical pipeline makes the application robust, maintainable, and mathematically sound.

## Future Enhancements

- Separate PHP classes for entities (Flight, Hotel, Activity)
- Service layer for API interactions
- Unit tests for Monad implementations
- Caching layer for API responses
- Additional destinations and travel options
- User authentication and saved itineraries

## License

MIT License - Feel free to use and modify for educational purposes.