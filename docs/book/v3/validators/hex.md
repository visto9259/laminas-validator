# Hex Validator

`Laminas\Validator\Hex` allows you to validate if a given value contains only
hexadecimal characters. These are all characters from **0 to 9** and **A to F**,
case insensitive. There is no length limitation for the input you want to
validate.

```php
$validator = new Laminas\Validator\Hex();
if ($validator->isValid('123ABC')) {
    // value contains only hex chars
} else {
    // false
}
```

<!-- markdownlint-disable-next-line MD001 -->
> ### Invalid Characters
>
> All other characters will return false, including whitespace and decimal
> points. Additionally, unicode zeros and numbers from other scripts than latin
> will not be treated as valid.

## Supported Options

There are no additional options for `Laminas\Validator\Hex`.
