# Payments & Mandates testing notes

This note captures the main discrepancies and noteworthy behaviors I found while turning the Payments and Mandates example flows into unit tests.

## Summary

The core happy paths work, but a few docs-vs-code mismatches and legacy behaviors are worth tightening up before relying on the examples as the public contract.

## Discrepancies / doc drift

### 1) Mandate example naming does not match the public API exactly
- The example in `examples/mandates.md` uses `CreateMandateId(...)`.
- The actual public method is `CreateMandateID(...)` in `src/Bluem.php`.
- The example also uses `response->error()` in one branch, while the actual response API exposes `Error()`.

**Impact:** copy/paste from the docs can fail unless the caller already knows the legacy casing.

### 2) Payment return URL concatenation is fragile when the base URL already has a query string
- `PaymentBluemRequest` always appends `?entranceCode=...&transactionID=...` to the configured base URL.
- If the base return URL already includes `?`, the generated URL becomes something like:
  `https://example.test/return?a=callback?entranceCode=...`

**Impact:** this is easy to miss in examples and can break callback routing or produce awkward URLs.

### 3) Payment currency handling is stricter than the examples suggest
- The payment docs say currency defaults to `EUR`.
- The implementation currently only accepts `EUR`; any other value throws `InvalidBluemRequestException`.

**Impact:** this is fine if intentional, but the docs should make the restriction explicit.

### 4) Mandate `PurchaseID` is derived, not user-supplied
- The generated mandate XML uses a `PurchaseID` built from customer ID and order ID.
- In current behavior, it resolves to the `customerId-orderId` shape in test configuration.

**Impact:** if the examples imply a different identifier format, the docs should be updated to show the actual output.

## Validation / runtime quirks

### 5) `GetMaximumAmount()` throws when the mandate acceptance report is missing
- `MandateStatusBluemResponse::GetMaximumAmount()` throws `No acceptance report delivered` if the response has no `AcceptanceReport` node.

**Impact:** consumers should treat this as a hard parsing failure, not a nullable field.

### 6) Example status responses are best tested with SimpleXML fixtures
- The current unit coverage validates response parsing offline with representative XML fixtures.
- This keeps the tests fast and deterministic, but it also means the examples should stay aligned with the actual response node names.

**Impact:** if response XML changes, these tests will catch it early.

## Legacy / maintenance notes

### 7) Configuration deprecation was caused by a dynamic `merchantId` property
- `BluemConfigurationValidator` was writing to `merchantId`, which created a dynamic property on PHP 8.4.
- The validator now uses the existing `merchantID` field instead.

**Impact:** this removes a deprecation and makes the config shape more consistent.

### 8) Example flows still mix legacy naming conventions
- Mixed-case public methods and older response property accessors are part of the current API surface.
- That’s okay for backwards compatibility, but it makes the examples feel inconsistent.

**Suggested follow-up:** consider adding a short compatibility note in the examples or README so users know which names are canonical.

## Suggested next improvements

- Make the return URL builder handle URLs that already contain query parameters.
- Clarify the currency restriction in the payment example/docs.
- Normalize the mandate example method/property names to match the actual public API.
- Consider documenting the exception behavior for missing mandate acceptance data.


