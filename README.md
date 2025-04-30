# Event Sourced Hotel Reservation System

This project implements a hotel reservation system using Event Sourcing principles with EventSauce.

## Requirements

- PHP 8.1 or higher
- Composer

## Installation

1. Clone the repository
2. Install dependencies:

```bash
composer install
```

## Testing

Run the tests with:

```bash
./vendor/bin/phpunit
```

## Project Structure

- `src/Reservation/`: Main application code
  - `Command/`: Command classes
  - `Query/`: Query classes
  - Aggregates and domain models
- `tests/`: Test files
  - `Unit/`: Unit tests
  - `Integration/`: Integration tests 