# Payments & Mandates testing notes

This note captures the main discrepancies and noteworthy behaviors I found while turning the Payments and Mandates example flows into unit tests.

## Summary

The core happy paths work, but a few docs-vs-code mismatches and legacy behaviors are worth tightening up before relying on the examples as the public contract.

The same pattern also shows up in the Identity and IBAN name-check flows: the requests are usable, but the examples lag a little behind the actual API and response behavior.

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

## Additional findings from Identity and IBAN testing

### 9) Identity example signature and request-type list need a quick cleanup
- `Bluem::CreateIdentityRequest()` requires a `returnURL` argument, but the example snippet does not show it in the call.
- The identity request-type list in `examples/identity.md` has a formatting issue and should be kept in sync with `Bluem::GetIdentityRequestTypes()`.
- The docs should consistently use `AgeCheckRequest` rather than mixing request naming styles.

**Impact:** the example is close, but not copy/paste safe as written.

### 10) Identity status reports can be absent, and the response now treats that as `null`
- `IdentityStatusBluemResponse::GetIdentityReport()` returns `null` when the response has no `<IdentityReport>` node.

**Impact:** callers should guard that access before dereferencing the report object.

### 11) IBAN name-check example only shows two result states
- The docs only show `INVALID` and `KNOWN`, but the response tests also cover `SERVICE_TEMPORARILY_NOT_AVAILABLE`.

**Impact:** the example should mention the third state so consumers know it is a valid runtime outcome.

### 12) IBAN account-details fields are optional and parse as empty strings
- When `<AccountDetails>` is missing, the getters for account type, joint-account flag, number of holders, and country name all return `''`.

**Impact:** this is safe, but it should be documented if callers rely on those fields for business rules.

## Suggested next improvements

- Make the return URL builder handle URLs that already contain query parameters.
- Clarify the currency restriction in the payment example/docs.
- Normalize the mandate example method/property names to match the actual public API.
- Consider documenting the exception behavior for missing mandate acceptance data.
- Fix the identity example to show the required `returnURL` argument.
- Add the `SERVICE_TEMPORARILY_NOT_AVAILABLE` IBAN result to the example docs.


