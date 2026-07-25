# V2 API plan

## Purpose

Introduce a modern, consistent API alongside the existing `Bluem` API without breaking current integrations. The new API should make payment methods explicit, support immutable request construction, remove shorthand inconsistencies, and provide a cleaner foundation for adding Wero, ApplePay, Pay by Bank, and future payment methods.

This is an API-version namespace, not an immediate Composer major-version change. The existing public API remains supported during the migration period.

## Compatibility principles

- Keep `Bluem\BluemPHP\Bluem` unchanged initially.
- Do not rename existing public methods, constants, request classes, or response classes.
- Do not change legacy XML output unless required for a separately documented bugfix.
- Add the new API under `Bluem\BluemPHP\Api\V2`.
- Share transport, configuration validation, schema validation, and protocol constants where safe.
- Add adapters rather than making the legacy API depend on unproven V2 behavior too early.
- Deprecate legacy methods only after the V2 API has had a release and migration documentation.

## Proposed public API

```php
use Bluem\BluemPHP\Api\V2\BluemClient;
use Bluem\BluemPHP\Api\V2\Money;
use Bluem\BluemPHP\Api\V2\PaymentMethod;

$client = BluemClient::fromConfig($config);

$payment = $client->payments()
    ->newPayment()
    ->withDescription('Wero payment')
    ->withReference('ORDER123')
    ->withAmount(Money::eur('12.34'))
    ->withPaymentMethod(PaymentMethod::WERO)
    ->withReturnUrl('https://example.com/payment-return')
    ->build();

$created = $client->payments()->create($payment);

header('Location: ' . $created->transactionUrl());
```

Status retrieval should use the same client:

```php
$status = $client->payments()->status(
    transactionId: $created->transactionId(),
    entranceCode: $created->entranceCode()
);

if ($status->isSuccessful()) {
    // Fulfil the order.
}
```

The V2 API should have one canonical network operation: `create()`. Convenience methods, if added later, must delegate to this operation rather than create a second payment flow.

## Core objects

### `BluemClient`

Responsibilities:

- Hold immutable configuration and injected transport.
- Expose service clients such as `payments()`.
- Provide shared request execution, response parsing, and error handling.

It should not build XML directly and should not contain payment-method-specific branches.

### Immutable configuration

Introduce a typed configuration object with validated fields such as:

- environment
- sender ID
- access token
- brand ID
- merchant return URL

Keep `fromConfig(stdClass|array)` as a compatibility-friendly factory, but convert immediately to the immutable V2 configuration object.

### `PaymentRequestBuilder`

Use immutable `withX()` methods. Each method returns a new builder or request state.

Suggested fields:

- description
- debtor reference
- payment reference
- amount
- currency
- due date/time
- return URL
- payment method
- payment-method details
- additional debtor data

Simple field validation can happen in `withX()`. Cross-field and payment-method validation happens in `build()`.

The builder must not perform HTTP requests. `build()` should return a validated immutable `PaymentRequest`.

### Money

Do not use floating-point values in the V2 API. Use a decimal string internally:

```php
Money::eur('12.34');
```

The serializer can then produce the required two-decimal XML representation.

### Payment methods

Use an enum with explicit wire mappings. Request wallet names and response payment-method values must not be assumed to be identical.

```php
enum PaymentMethod: string
{
    case HOSTED_SELECTION = 'HOSTED_SELECTION';
    case IDEAL = 'IDEAL';
    case PAYPAL = 'PAYPAL';
    case CREDIT_CARD = 'VISA_MASTER';
    case SOFORT = 'SOFORT';
    case SOFORT_DIGITAL_SERVICES = 'SOFORT_DIGITALSERVICES';
    case CARTE_BANCAIRE = 'CARTE_BANCAIRE';
    case BANCONTACT = 'BANCONTACT';
    case GIROPAY = 'GIROPAY';
    case APPLE_PAY = 'APPLEPAY';
    case WERO = 'WERO';
    case PAY_BY_BANK = 'PAY_BY_BANK';
}
```

The enum or a registry should separately define the request wallet element:

| Response value | Request wallet element |
|---|---|
| `HOSTED_SELECTION` | *(no `DebtorWallet` element)* |
| `IDEAL` | `IDEAL` |
| `PAYPAL` | `PayPal` |
| `VISA_MASTER` | `CreditCard` |
| `SOFORT` | `Sofort` |
| `SOFORT_DIGITALSERVICES` | `SofortDigitalServices` |
| `CARTE_BANCAIRE` | `CarteBancaire` |
| `BANCONTACT` | `Bancontact` |
| `GIROPAY` | `Giropay` |
| `APPLEPAY` | `ApplePay` |
| `WERO` | `Wero` |
| `PAY_BY_BANK` | `PayByBank` |

Payment-specific data should use typed objects such as `CreditCardDetails`, rather than unvalidated arrays.

## XML and protocol design

Separate protocol concerns into dedicated components:

```text
PaymentRequest
    -> PaymentRequestSerializer
    -> Bluem transport
    -> PaymentResponseParser
    -> PaymentTransaction / PaymentStatus DTO
```

The serializer should:

- Use the payment-method registry instead of a growing `if/elseif` chain.
- Escape all XML values safely.
- Preserve the existing `EPayment.xsd` ordering and attributes.
- Validate generated XML against the schema before transport.
- Make unsupported payment methods fail clearly before an HTTP request.

The response parser should expose typed values such as:

- transaction ID
- entrance code
- transaction URL
- status
- amount and paid amount
- currency
- payment method
- payment method details

Raw XML may remain available as an explicit diagnostic field, but should not be the primary integration interface.

## Resolving the shorthand inconsistency

The legacy `Payment()` method remains unchanged for compatibility. In V2:

- `PaymentRequestBuilder` constructs the request.
- `PaymentsClient::create()` performs it.
- No method silently performs a request while also applying a different set of defaults.
- Payment method selection is available before execution.
- The hosted-payment-selection case should be explicit, for example `PaymentMethod::HOSTED_SELECTION`, rather than relying on an implicit iDEAL default.
- Redirecting the browser remains the application’s responsibility; the client returns a typed creation response.

## Error handling

Choose one consistent V2 model. Recommended:

- `InvalidRequestException` for local validation failures.
- `TransportException` for network failures.
- `BluemApiException` for an error response from Bluem.
- Typed successful response DTOs for successful calls.

Do not return an error object in the same union as every successful response. The legacy error-response behavior remains unchanged in the old API.

## Legacy integration strategy

Initially, V2 and the legacy API can share:

- `HttpTransportInterface`
- endpoint resolution
- configuration validation
- XML schema validation
- date/time helpers
- response error mapping where behavior is already stable

The legacy API should not be rewritten wholesale. Once V2 serializers and parsers are covered by contract tests, selected legacy methods may delegate internally, but only where their exact XML and return behavior is preserved.

## Implementation phases

### Phase 1: Characterize current behavior

- Capture existing request XML fixtures for payments, statuses, webhooks, and each currently exposed payment method.
- Record XSD-valid and XSD-invalid cases.
- Document current defaults and legacy quirks.
- Confirm the actual Bluem contract for CreditCard, ApplePay, Pay by Bank, and Wero payloads.

### Phase 2: Add V2 foundations

- Add the `Api\V2` namespace.
- Add immutable configuration, `Money`, enums, and exception types.
- Add an immutable payment builder and validated request DTO.
- Add `BluemClient` and `PaymentsClient` with injected transport.

### Phase 3: Add XML and response layers

- Implement the payment request serializer.
- Implement typed transaction and status response DTOs.
- Add payment-method registry and typed method details.
- Keep the existing XSD validator in the execution path.

### Phase 4: Add payment methods incrementally

Recommended order:

1. Hosted selection and iDEAL
2. Wero
3. Bancontact, Sofort, Carte Bancaire, and Giropay
4. CreditCard, after confirming the current XSD/API contract
5. ApplePay
6. Pay by Bank

Each method should include request XML fixtures, response fixtures, XSD validation, and an integration/acceptance test where Bluem credentials support it.

### Phase 5: Documentation and migration

- Add a V2 getting-started example.
- Add a legacy-to-V2 mapping table.
- Explain how to migrate payment creation and status checks.
- Document payment-method-specific details and unsupported combinations.
- Mark legacy shorthand methods as deprecated only when the V2 API is stable.

## Testing strategy

Every V2 payment method should have:

- Immutable builder tests.
- Invalid-input tests.
- Exact XML fixture tests.
- XSD validation tests.
- Response parsing tests.
- Status mapping tests.
- Fake-transport execution tests.
- Live acceptance coverage where the account and sender ID support the method.

Add contract tests that compare legacy and V2 behavior where compatibility is intended, especially for:

- entrance codes
- return URLs
- request attributes
- transaction references
- error mapping
- status retrieval

## Release criteria

The V2 API is ready for an initial opt-in release when:

- Existing unit, acceptance, integration, and webhook tests remain green.
- The V2 payment flow works without using legacy request classes.
- At least hosted selection, iDEAL, and Wero are covered end-to-end.
- XML is schema validated before transport.
- No V2 API requires floating-point amounts or mutable request state.
- Migration documentation and examples are available.
- The public V2 namespace and compatibility policy are documented.

## Open decisions

- Whether the public namespace should be `Api\V2` or a non-versioned `Modern` namespace.
- Whether V2 errors should be exception-based or use a typed result object.
- The exact CreditCard XML contract.
- Whether webhooks should receive the same response DTOs as polling status calls.
- Whether the V2 API should eventually become the default facade in Composer 3.0.
