# AGENTS.md

## Project at a glance
- `bluem-php` is a PHP 8.3 library for Bluem payment flows: Payments, eMandates, iDIN, and IBAN-name check.
- `src/Bluem.php` is the main orchestration layer: it builds requests, validates XML, sends them through `Transport/`, and turns responses into `Responses/*` objects.
- `src/Webhook.php` handles inbound webhook XML and is intentionally strict: HTTPS POST + `text/xml; charset=UTF-8` + XML/signature validation.

## Code structure to preserve
- `src/Contexts/*` defines service-specific bank/BIC sets and XSD schema paths (`IdentityContext`, `PaymentsContext`, `MandatesContext`).
- `src/Requests/*` owns XML/endpoint generation; `src/Responses/*` wraps `SimpleXMLElement` parsing.
- `src/Transport/` isolates HTTP; `CurlHttpTransport` is the default and is injected into `Bluem` for testability.
- `src/Validators/*` enforces XML and webhook constraints; do not bypass these checks in higher layers.

## Conventions specific to this repo
- Public API names are legacy-compatible and intentionally mixed-case in places (`CreateMandateRequest`, `PerformRequest`, `getConfig`, `Webhook::getPurchaseID()`); avoid renaming unless you are ready to update downstream consumers.
- `phpcs.xml.dist` uses PSR-12 with narrow exceptions for legacy method/property naming and file-header ordering.
- Existing code mixes older style and newer strict typing; prefer small, behavior-preserving edits over broad refactors.

## Developer workflow
- Install deps with `composer install`.
- Lint with `make lint`; auto-fix style with `make lint_fix`.
- Run unit tests with `make test_unit` or `./vendor/bin/phpunit tests/Unit`.
- Run live tests with `make test_integration` / `make test_acceptance`; these require `.env`.
- CI (`.github/workflows/ci.yml`) runs on PHP 8.3, then `make lint`, then PHPUnit.

## Environment and testing
- Copy `.env.example` to `.env` for integration/acceptance tests.
- Required env vars are enforced in `tests/Integration/BluemGenericTestCase.php`: `BLUEM_ENV`, `BLUEM_SENDER_ID`, `BLUEM_BRANDID`, `BLUEM_TEST_ACCESS_TOKEN`, `BLUEM_MERCHANTID`, `BLUEM_MERCHANTRETURNURLBASE`.
- Unit tests typically use a fake transport; integration tests extend the shared base case and hit live Bluem services.

## When changing code
- Update request/response/context pieces together so XML shape, endpoint URL, and validation stay aligned.
- Check `validation/*.xsd` and `examples/` when touching service-specific payloads.
- Keep webhook validation strict; relaxing HTTPS, content-type, or signature checks is a security regression.

