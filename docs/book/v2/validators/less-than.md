# LessThan Validator

CAUTION: **Deprecated**
This validator is deprecated in favour of the [NumberComparison validator](number-comparison.md) and the [DateComparison validator](date-comparison.md) for validation of dates.
This validator will be removed in version 3.0.

`Laminas\Validator\LessThan` allows you to validate if a given value is less than a
maximum value.

> Supports only number validation
>
> `Laminas\Validator\LessThan` supports only the validation of numbers. Strings or
> dates can not be validated with this validator.

## Supported options

The following options are supported for `Laminas\Validator\LessThan`:

- `inclusive`: Defines if the validation is inclusive the maximum value or
  exclusive. It defaults to `false`.
- `max`: Sets the maximum allowed value.

## Basic usage

To validate if a given value is less than a defined maximum:

```php
$valid  = new Laminas\Validator\LessThan(['max' => 10]);
$value  = 12;
$return = $valid->isValid($value);
// returns false
```

The above example returns `true` for all values lower than 10.

## Inclusive validation

Sometimes it is useful to validate a value by including the maximum value:

```php
$valid  = new Laminas\Validator\LessThan([
    'max' => 10,
    'inclusive' => true,
]);
$value  = 10;
$result = $valid->isValid($value);
// returns true
```

The example is identical to our first example, with the exception that we've
specified that the maximum is inclusive. Now the value '10' is allowed and will
return `true`.
