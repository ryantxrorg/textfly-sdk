# Repository Guidelines

## Project Structure & Module Organization
Core SDK code lives in `src/`, with service-specific clients under `src/Api/` and shared exceptions in `src/Exceptions/`. Public entry points such as `Client.php` expose typed helpers for API access. Tests reside in `tests/` and mirror the production namespace (`TextFly\Sdk\Tests\`), while API reference material sits in `docs/` (`docs/api.md`, `docs/openapi.yaml`).

## Build, Test, and Development Commands
Run `composer install` to pull dependencies before local work. Execute the test suite via `./vendor/bin/phpunit`, which boots through `phpunit.xml.dist` and targets the `tests/` directory. During iterative development, you can rerun a specific test with `./vendor/bin/phpunit tests/ClientTest.php --filter testScheduledMessagesCreateSendsJsonPayload`.

## Coding Style & Naming Conventions
Match the existing PSR-12 style: 4-space indentation, braces on new lines, and typed properties when possible. Namespace classes under `TextFly\Sdk` and use `PascalCase` for class names, `camelCase` for methods, and snake_case for array payload keys to align with API contracts. Keep public APIs documented with concise docblocks describing payload shapes and exception paths.

## Testing Guidelines
All tests use PHPUnit with Guzzle's `MockHandler` to assert request construction. Name files with the `*Test.php` suffix and place them in a path that mirrors the class under test (e.g., `tests/ClientTest.php`). When adding features, cover both happy-path JSON handling and failure branches (HTTP errors, invalid JSON). Run `./vendor/bin/phpunit` before opening a pull request and ensure new mocks include realistic status codes and headers.

## Commit & Pull Request Guidelines
The existing history favors short, lowercase summaries (e.g., `init`). Continue using imperative, one-line commit messages that focus on the outcome (`Add scheduled messages client`). For pull requests, include a brief description of the change, note any new endpoints or payload contracts, link related issues, and attach CLI output or screenshots when behaviour differs from previous releases. Highlight any configuration or token requirements so reviewers can reproduce your tests.
