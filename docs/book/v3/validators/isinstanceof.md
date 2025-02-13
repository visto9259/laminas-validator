# IsInstanceOf Validator

`Laminas\Validator\IsInstanceOf` allows you to validate whether a given object is
an instance of a specific class or interface.

## Supported Options

The following options are supported for `Laminas\Validator\IsInstanceOf`:

- `className`: Defines the fully-qualified class name which objects must be an
  instance of.

## Basic Usage

```php
$validator = new Laminas\Validator\IsInstanceOf([
    'className' => Laminas\Validator\Digits::class,
]);
$object = new Laminas\Validator\Digits();

if ($validator->isValid($object)) {
    // $object is an instance of Laminas\Validator\Digits
} else {
    // false. You can use $validator->getMessages() to retrieve error messages
}
```
