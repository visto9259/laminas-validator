# Barcode Validator

`Laminas\Validator\Barcode` allows you to check if a given value can be represented
as a barcode.

## Supported Barcodes

`Laminas\Validator\Barcode` supports multiple barcode standards and can be extended
with proprietary barcode implementations. The following barcode standards are
supported:

### CODABAR

Also known as Code-a-bar.

This barcode has no length limitation. It supports only digits, and 6 special
chars. Codabar is a self-checking barcode. This standard is very old. Common use
cases are within airbills or photo labs where multi-part forms are used with
dot-matrix printers.

### CODE128

CODE128 is a high density barcode.

This barcode has no length limitation. It supports the first 128 ascii
characters. When used with printing characters it has an checksum which is
calculated modulo 103. This standard is used worldwide as it supports upper and
lowercase characters.

### CODE25

Often called "two of five" or "Code25 Industrial".

This barcode has no length limitation. It supports only digits, and the last
digit can be an optional checksum which is calculated with modulo 10. This
standard is very old and nowadays not often used. Common use cases are within
the industry.

### CODE25INTERLEAVED

Often called "Code 2 of 5 Interleaved".

This standard is a variant of CODE25. It has no length limitation, but it must
contain an even amount of characters. It supports only digits, and the last
digit can be an optional checksum which is calculated with modulo 10. It is used
worldwide and common on the market.

### CODE39

CODE39 is one of the oldest available codes.

This barcode has a variable length. It supports digits, upper cased alphabetical
characters and 7 special characters like whitespace, point and dollar sign. It
can have an optional checksum which is calculated with modulo 43. This standard
is used worldwide and common within the industry.

### CODE39EXT

CODE39EXT is an extension of CODE39.

This barcode has the same properties as CODE39. Additionally it allows the usage
of all 128 ASCII characters. This standard is used worldwide and common within
the industry.

### CODE93

CODE93 is the successor of CODE39.

This barcode has a variable length. It supports digits, alphabetical characters
and 7 special characters. It has an optional checksum which is calculated with
modulo 47 and contains 2 characters. This standard produces a denser code than
CODE39 and is more secure.

### CODE93EXT

CODE93EXT is an extension of CODE93.

This barcode has the same properties as CODE93. Additionally it allows the usage
of all 128 ASCII characters. This standard is used worldwide and common within
the industry.

### EAN2

EAN is the shortcut for "European Article Number".

These barcode must have 2 characters. It supports only digits and does not have
a checksum. This standard is mainly used as addition to EAN13 (ISBN) when
printed on books.

### EAN5

EAN is the shortcut for "European Article Number".

These barcode must have 5 characters. It supports only digits and does not have
a checksum. This standard is mainly used as addition to EAN13 (ISBN) when
printed on books.

### EAN8

EAN is the shortcut for "European Article Number".

These barcode can have 7 or 8 characters. It supports only digits. When it has a
length of 8 characters it includes a checksum. This standard is used worldwide
but has a very limited range. It can be found on small articles where a longer
barcode could not be printed.

### EAN12

EAN is the shortcut for "European Article Number".

This barcode must have a length of 12 characters. It supports only digits, and
the last digit is always a checksum which is calculated with modulo 10. This
standard is used within the USA and common on the market. It has been superseded
by EAN13.

### EAN13

EAN is the shortcut for "European Article Number".

This barcode must have a length of 13 characters. It supports only digits, and
the last digit is always a checksum which is calculated with modulo 10. This
standard is used worldwide and common on the market.

### EAN14

EAN is the shortcut for "European Article Number".

This barcode must have a length of 14 characters. It supports only digits, and
the last digit is always a checksum which is calculated with modulo 10. This
standard is used worldwide and common on the market. It is the successor for
EAN13.

### EAN18

EAN is the shortcut for "European Article Number".

This barcode must have a length of 18 characters. It support only digits. The
last digit is always a checksum digit which is calculated with modulo 10. This
code is often used for the identification of shipping containers.

### GTIN12

GTIN is the shortcut for "Global Trade Item Number".

This barcode uses the same standard as EAN12 and is its successor. It's commonly
used within the USA.

### GTIN13

GTIN is the shortcut for "Global Trade Item Number".

This barcode uses the same standard as EAN13 and is its successor. It is used
worldwide by industry.

### GTIN14

GTIN is the shortcut for "Global Trade Item Number".

This barcode uses the same standard as EAN14 and is its successor. It is used
worldwide and common on the market.

### IDENTCODE

Identcode is used by Deutsche Post and DHL. It's an specialized implementation of Code25.

This barcode must have a length of 12 characters. It supports only digits, and
the last digit is always a checksum which is calculated with modulo 10. This
standard is mainly used by the companies DP and DHL.

### INTELLIGENTMAIL

Intelligent Mail is a postal barcode.

This barcode can have a length of 20, 25, 29 or 31 characters. It supports only
digits, and contains no checksum. This standard is the successor of PLANET and
POSTNET. It is mainly used by the United States Postal Services.

### ISSN

ISSN is the abbreviation for International Standard Serial Number.

This barcode can have a length of 8 or 13 characters. It supports only digits,
and the last digit must be a checksum digit which is calculated with modulo 11.
It is used worldwide for printed publications.

### ITF14

ITF14 is the GS1 implementation of an Interleaved Two of Five bar code.

This barcode is a special variant of Interleaved 2 of 5. It must have a length
of 14 characters and is based on GTIN14. It supports only digits, and the last
digit must be a checksum digit which is calculated with modulo 10. It is used
worldwide and common within the market.

### LEITCODE

Leitcode is used by Deutsche Post and DHL. It's an specialized implementation of Code25.

This barcode must have a length of 14 characters. It supports only digits, and
the last digit is always a checksum which is calculated with modulo 10. This
standard is mainly used by the companies DP and DHL.

### PLANET

Planet is the abbreviation for Postal Alpha Numeric Encoding Technique.

This barcode can have a length of 12 or 14 characters. It supports only digits,
and the last digit is always a checksum. This standard is mainly used by the
United States Postal Services.

### POSTNET

Postnet is used by the US Postal Service.

This barcode can have a length of 6, 7, 10 or 12 characters. It supports only
digits, and the last digit is always a checksum. This standard is mainly used by
the United States Postal Services.

### ROYALMAIL

Royalmail is used by Royal Mail.

This barcode has no defined length. It supports digits, uppercase letters, and
the last digit is always a checksum. This standard is mainly used by Royal Mail
for their Cleanmail Service. It is also called RM4SCC.

### SSCC

SSCC is the shortcut for "Serial Shipping Container Code".

This barcode is a variant of EAN barcode. It must have a length of 18 characters
and supports only digits. The last digit must be a checksum digit which is
calculated with modulo 10. It is commonly used by the transport industry.

### UPCA

UPC is the shortcut for "Universal Product Code".

This barcode preceded EAN13. It must have a length of 12 characters and supports
only digits. The last digit must be a checksum digit which is calculated with
modulo 10. It is commonly used within the USA.

### UPCE

UPCE is the short variant from UPCA.

This barcode is a smaller variant of UPCA. It can have a length of 6, 7 or 8
characters and supports only digits. When the barcode is 8 chars long it
includes a checksum which is calculated with modulo 10. It is commonly used with
small products where a UPCA barcode would not fit.

## Supported Options

The following options are supported for `Laminas\Validator\Barcode`:

- `adapter`: Sets the barcode adapter which will be used. All the adapters listed above are supported and the option accepts either a FQCN, an adapter instance or a short string such as 'Upca' or 'Gtin13' that corresponds to the class name. Short strings should be avoided in favour of FQCN's.
- `checksum`: `false` Checksum validation is *off* by default. If you are validating barcodes that are capable of validating a checksum, you should explicitly enable this option by setting it to `true`

## Basic Usage

To validate if a given string is a barcode you must know its type. See the
following example for an EAN13 barcode:

```php
$validator = new Laminas\Validator\Barcode([
    'adapter' => Laminas\Validator\Barcode\Ean13::class,
]);
if ($validator->isValid($input)) {
    // input appears to be valid
} else {
    // input is invalid
}
```

## Optional Checksum

Checksum validation of barcodes, for those types that support it, is optional. If you are validating a barcode type that supports checksum validation, you should enable the feature to ensure that input is valid.

```php
$valid = new Laminas\Validator\Barcode([
    'adapter' => Laminas\Validator\Barcode\Ean13::class,
    'checksum' => true,
]);
if ($valid->isValid($input)) {
    // A valid EAN13 barcode, and checksum validation succeeded
} else {
    // input is invalid
}
```

> ### Reduced Security by Disabling Checksum Validation
>
> By switching off checksum validation you will also reduce the security of the
> used barcodes. Additionally, you should note that you can also turn off the
> checksum validation for those barcode types which must contain a checksum
> value. Barcodes which would not be valid could then be returned as valid even
> if they are not.

## Writing Custom Adapters

You may write custom barcode validators for usage with `Laminas\Validator\Barcode`;
this is often necessary when dealing with proprietary barcode types. To write
your own barcode validator, you need the following information.

- `Length`: The length your barcode must have. It can have one of the following
  values:
    - `Integer`: A value greater 0, which means that the barcode must have this
    length.
    - `-1`: There is no limitation for the length of this barcode.
    - `"even"`: The length of this barcode must have a even amount of digits.
    - `"odd"`: The length of this barcode must have a odd amount of digits.
    - `array`: A list of integer values. The length of this barcode must have
  one of the set array values.
- `Characters`: An alphabet of characters that can be used in your type of barcode
- `Checksum`: A method that calculates and verifies a checksum if appropriate

Your custom barcode validator must implement `Laminas\Validator\Barcode\AdapterInterface`.

As an example, let's create a barcode validation adapter that expects an even number of characters that includes only digits, and which requires a checksum.

```php
namespace My\Barcode;

use Laminas\Validator\Barcode;
use Laminas\Validator\Barcode\AdapterInterface;use function ctype_digit;

final class CustomBarcodeAdapter implements AdapterInterface
{
    public function hasValidLength(string $value): bool
    {
        return strlen($value) % 2 === 0;
    }

    public function hasValidCharacters(string $value): bool
    {
        return ctype_digit($value);
    }

    public function hasValidChecksum(string $value): bool
    {
        // do some validation and return a boolean
        return true;
    }

    public function getLength(): string
    {
        return 'even';
    }
}

$validator = new Barcode([
    'adapter' => CustomBarcodeAdapter::class,
]);

if ($validator->isValid($input)) {
    // input appears to be valid
} else {
    // input is invalid
}
```
