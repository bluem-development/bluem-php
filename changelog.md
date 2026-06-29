# Changes per release

## Major version 2
### 2.5: 
- Migrated away from deprecated XML signature validation package
- Set minimum PHP to 8.3

### 2.4:
- Added Bancontact support

### 2.3.2.4
- Added bank 'N26' to ePayments BIC list.
- Restructured code for Magento compatibility.

### 2.3.2.3
Updated certificates.

### 2.3.2.2
Updated BIC epayments list.

### 2.3.2
Added BIC to mandate request.

### 2.3.1
Added BIC to identity request.

### 2.3
Added PHP 8+ support.

### 2.2
Webhooks and new payment methods
- Added explicit webhook functionality and relevant documentation
- Support for PayPal, Creditcards, SOFORT and Carte Bancaire

### 2.1
Major improvement in code style.
- Added `$bluem->getConfig($key)` method to retrieve a configuration value.
- Added `$bluem->setConfig($key, $value)` method to set a configuration value.
- Added several validation steps
- Added more unit testing coverage
- Separated more responsibilities for cleaner code

###s before 2.1

#### 2.0.12
Allowing the verification if the current IP is based in the Netherlands utilizing a geolocation integration *(IP-API).

```php
$bluem->VerifyIPIsNetherlands();
// returns bool true if NL or error, returns false if no error and other country.
```
*This feature can be used to determine whether to use iDIN identity checking in any application, as this supports only Dutch banks.*

#### 2.0.2:

Triodos Bank, BIC TRIONL2U no longer supported for Identity requests as of 1 june 2021. See: https://www.triodos.nl/veelgestelde-vragen/kan-ik-idin-gebruiken?id=4de127e85eee

- If you use the [Preselection of banks using the DebtorWallet](https://github.com/bluem-development/bluem-php#debtorwallet-preselecting-a-bank-for-mandate-payment-or-identity-request), you will have to update this library to ensure Triodos is no longer an option for iDIN. If you do not do this, customers that select Triodos will be presented with an error.

- If you use the Bluem portal, you don't need to act. This change is already applied within the Bluem portal.

#### 2.0.1:
Major release with more stability, validation and features.

Please note: The main Integration class is called Bluem, so to include it, use:
```php
$bluem = new Bluem($config);
```
Or use a class alias to ensure code functioning. This is a refactor since version 1.x.

Furthermore, all generally available functions are still available.

---

No earlier changelog was recorded. Please refer to the [commit log](https://github.com/bluem-development/bluem-php/commits/master) for more information.
