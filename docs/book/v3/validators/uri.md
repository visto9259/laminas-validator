# Uri Validator

`Laminas\Validator\Uri` allows you to validate a URI using the `Laminas\Uri\Uri`
handler to parse the URI. The validator allows for both validation of absolute
and/or relative URIs. There is the possibility to exchange the handler for
another one in case the parsing of the uri should be done differently.

> MISSING: **Installation Requirements**
> The default handler depends on [laminas-uri](https://docs.laminas.dev/laminas-uri/) to parse the URI, so be sure to have it installed before getting started:
>
> ```bash
> composer require laminas/laminas-uri
> ```

## Supported Options

The following options are supported for `Laminas\Validator\Uri`:

- `uriHandler`: Defines the handler to be used to parse the uri. This options
  defaults to a new instance of `Laminas\Uri\Uri`.
- `allowRelative`: Defines if relative paths are allowed. This option defaults
  to `true`.
- `allowAbsolute`: Defines if absolute paths are allowed. This option defaults
  to `true`.

## Basic Usage

```php
$validator = new Laminas\Validator\Uri();
$uri = 'https://getlaminas.org/manual';

if ($validator->isValid($uri)) {
    // $uri was valid
} else {
    // false. You can use $validator->getMessages() to retrieve error messages
}
```
