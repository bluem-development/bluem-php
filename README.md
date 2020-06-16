# bluem-php

A PHP interface for utilizing the Bluem services such as eMandate, ePayments, iDIN and more.

Utilized by a range of other applications such as WordPress and WordPress WooCommerce plugins 
% add links %

Use this to write your own applications in PHP that communicate with Bluem.

## Installation

Run Composer to install this library and dependences:

```bash
composer require daanrijpkema/bluem-php
```

## Usage

Include the required classes in your code. 
For example, given that this code repository is deployed in the parent folder of your current folder:

```php
require '../bluem-php/BlueMIntegration.php';
```





## Important notes

### Enable secure Webhook reception through a certificate check
To be able to use webhook functionality, retrieve a copy of the Webhook certificate provided by Bluem and put it in a folder named `keys`, writeable by the code in this library.
