# GreaterThan Validator

CAUTION: **Deprecated**
This validator is deprecated in favour of the [NumberComparison validator](number-comparison.md) and the [DateComparison validator](date-comparison.md) for validation of dates.
This validator will be removed in version 3.0.

`Laminas\Validator\GreaterThan` allows you to validate if a given value is greater
than a minimum border value.

<!-- markdownlint-disable-next-line MD001 -->
> ### Only supports numbers
>
> `Laminas\Validator\GreaterThan` supports only the validation of numbers. Strings
> or dates can not be validated with this validator.

## Supported options

The following options are supported for `Laminas\Validator\GreaterThan`:

- `inclusive`: Defines if the validation is inclusive of the minimum value,
  or exclusive. It defaults to `false`.
- `min`: Sets the minimum allowed value.

## Basic usage

To validate if a given value is greater than a defined minimum:

```php
$valid  = new Laminas\Validator\GreaterThan(['min' => 10]);
$value  = 8;
$return = $valid->isValid($value);
// returns false
```

The above example returns `true` for all values which are greater than 10.

## Inclusive validation

Sometimes it is useful to validate a value by including the minimum value.

```php
$valid  = new Laminas\Validator\GreaterThan([
    'min' => 10,
    'inclusive' => true,
]);
$value  = 10;
$result = $valid->isValid($value);
// returns true
```

The example is identical to our first example, with the exception that we
included the minimum value. Now the value '10' is allowed and will return
`true`.
