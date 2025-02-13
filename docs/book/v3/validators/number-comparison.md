# Number Comparison Validator

`Laminas\Validator\NumberComparison` allows you to validate if a given value is a numeric value that is either:

- Between a min and max value
- Greater than a min value
- Less than a max value

By default, comparisons are inclusive.

CAUTION: **Only supports number validation**
`Laminas\Validator\NumberComparison` supports only the validation of numbers.
Strings or dates can not be validated with this validator.

## Supported Options

The following options are supported for `Laminas\Validator\NumberComparison`:

| Option         | Data Type | Default Value | Description                                                              |
|----------------|-----------|---------------|--------------------------------------------------------------------------|
| `max`          | `numeric` | `null`        | Sets the upper bound for the input.                                      |
| `min`          | `numeric` | `null`        | Sets the lower bound for the input.                                      |
| `inclusiveMin` | `bool`    | `true`        | Defines if the validation is inclusive of the lower bound, or exclusive. |
| `inclusiveMax` | `bool`    | `true`        | Defines if the validation is inclusive of the upper bound, or exclusive. |

## Basic Usage

Per default, this validator checks if a value is between `min` and `max` where both upper and lower bounds are considered valid.

```php
$valid  = new Laminas\Validator\NumberComparison(['min' => 0, 'max' => 10]);
$value  = 10;
$result = $valid->isValid($value);
// returns true
```

In the above example, the result is `true` due to the reason that the default search is inclusive of the border values.
This means in our case that any value from `0` to `10` is allowed; values like `-1` and `11` will return `false`.

## Excluding Upper and Lower Bounds

Sometimes it is useful to validate a value by excluding the bounds. See the following example:

```php
$valid  = new Laminas\Validator\NumberComparison([
    'min' => 0,
    'max' => 10,
    'inclusiveMin' => false,
    'inclusiveMax' => false,
]);

$valid->isValid(10); // false
$valid->isValid(0);  // false
$valid->isValid(9);  // true
```

The example above is almost identical to our first example, but we now exclude the bounds as valid values; as such, the values `0` and `10` are no longer allowed and will return `false`.

## Min and Max behaviour

In order to validate a number that is simply greater than a lower bound, either omit the `max` option, or set it explicitly to `null`:

```php
$validator = new Laminas\Validator\NumberComparison(['min' => 10, 'max' => null]);
$validator->isValid(12345); // true
```

Conversely, to ensure a number is less than an upper bound, omit the `min` option or explicitly set it to `null`:

```php
$validator = new Laminas\Validator\NumberComparison(['max' => 5]);
$validator->isValid(99); // false
```

You *must* provide one of the `min` or the `max` *(or both)* options or an exception will be thrown.
It doesn't make sense to compare the input to nothing for this validator.
