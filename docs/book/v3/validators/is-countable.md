# IsCountable Validator

`Laminas\Validator\IsCountable` allows you to validate that a value can be counted
(i.e., it's an array or an object that implements `Countable`), and, optionally:

- the exact count of the value
- the minimum count of the value
- the maximum count of the value

Specifying either of the latter two is inconsistent with the first, and, as
such, the validator does not allow setting both a count and a minimum or maximum
value. You may, however specify both minimum and maximum values to ensure that the number of elements is between a given range.

## Supported Options

The following options are supported for `Laminas\Validator\IsCountable`:

- `count`: Defines if the validation should look for a specific, exact count for
  the value provided.
- `max`: Sets the maximum value for the validation; if the count of the value is
  greater than the maximum, validation fails..
- `min`: Sets the minimum value for the validation; if the count of the value is
  lower than the minimum, validation fails.

## Default Behaviour

Given no options, the validator simply tests to see that the value may be
counted (i.e., it's an array or `Countable` instance):

```php
$validator = new Laminas\Validator\IsCountable();

$validator->isValid(10);                    // false; not an array or Countable
$validator->isValid([10]);                  // true; value is an array
$validator->isValid(new ArrayObject([10])); // true; value is Countable
$validator->isValid(new stdClass);          // false; value is not Countable
```

## Specifying an Exact Count

You can also specify an exact count; if the value is countable, and its count
matches, the the value is valid.

```php
$validator = new Laminas\Validator\IsCountable(['count' => 3]);

$validator->isValid([1, 2, 3]);                  // true; countable, and count is 3
$validator->isValid(new ArrayObject([1, 2, 3])); // true; countable, and count is 3
$validator->isValid([1]);                        // false; countable, but count is 1
$validator->isValid(new ArrayObject([1]));       // false; countable, but count is 1
```

## Specifying a Minimum Count

You may specify a minimum count. When you do, the value must be countable, and
greater than or equal to the minimum count you specify in order to be valid.

```php
$validator = new Laminas\Validator\IsCountable(['min' => 2]);

$validator->isValid([1, 2, 3]);                  // true; countable, and count is 3
$validator->isValid(new ArrayObject([1, 2, 3])); // true; countable, and count is 3
$validator->isValid([1, 2]);                     // true; countable, and count is 2
$validator->isValid(new ArrayObject([1, 2]));    // true; countable, and count is 2
$validator->isValid([1]);                        // false; countable, but count is 1
$validator->isValid(new ArrayObject([1]));       // false; countable, but count is 1
```

## Specifying a Maximum Count

You may specify a maximum count. When you do, the value must be countable, and
less than or equal to the maximum count you specify in order to be valid.

```php
$validator = new Laminas\Validator\IsCountable(['max' => 2]);

$validator->isValid([1, 2, 3]);                  // false; countable, but count is 3
$validator->isValid(new ArrayObject([1, 2, 3])); // false; countable, but count is 3
$validator->isValid([1, 2]);                     // true; countable, and count is 2
$validator->isValid(new ArrayObject([1, 2]));    // true; countable, and count is 2
$validator->isValid([1]);                        // true; countable, and count is 1
$validator->isValid(new ArrayObject([1]));       // true; countable, and count is 1
```

## Specifying Both Minimum and Maximum

If you specify both a minimum and maximum, the count must be _between_ the two,
inclusively (i.e., it may be the minimum or maximum, and any value between).

```php
$validator = new Laminas\Validator\IsCountable([
    'min' => 3,
    'max' => 5,
]);

$validator->isValid([1, 2, 3]);                    // true; countable, and count is 3
$validator->isValid(new ArrayObject([1, 2, 3]));   // true; countable, and count is 3
$validator->isValid(range(1, 5));                  // true; countable, and count is 5
$validator->isValid(new ArrayObject(range(1, 5))); // true; countable, and count is 5
$validator->isValid([1, 2]);                       // false; countable, and count is 2
$validator->isValid(new ArrayObject([1, 2]));      // false; countable, and count is 2
$validator->isValid(range(1, 6));                  // false; countable, and count is 6
$validator->isValid(new ArrayObject(range(1, 6))); // false; countable, and count is 6
```
