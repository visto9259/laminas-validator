# Validator Chains

## Basic Usage

Often, multiple validations should be applied to some value in a particular
order. The following code demonstrates a way to solve the example from the
[introduction](intro.md), where a username must be between 6 and 12 alphanumeric
characters:

```php
use Laminas\Validator\Regex;
use Laminas\Validator\StringLength;
use Laminas\Validator\ValidatorChain;

// Create a validator chain and add validators to it
$validatorChain = new ValidatorChain();
$validatorChain->attach(new StringLength(['min' => 6, 'max' => 12]));
$validatorChain->attach(new Regex(['pattern' => '/^[a-z0-9]+$/i']));

// Validate the username
if ($validatorChain->isValid($username)) {
    // username passed validation
} else {
    // username failed validation; print reasons
    foreach ($validatorChain->getMessages() as $message) {
        echo "$message\n";
    }
}
```

Validators are run in the order they were added to the `ValidatorChain`. In the
above example, the username is first checked to ensure that its length is
between 6 and 12 characters, and then it is checked to ensure that it contains
only alphanumeric characters. The second validation, for alphanumeric
characters, is performed regardless of whether the first validation, for length
between 6 and 12 characters, succeeds. This means that if both validations fail,
`getMessages()` will return failure messages from both validators.

In some cases, it makes sense to have a validator *break the chain* if its
validation process fails. `ValidatorChain` supports such use cases with the
second parameter to the `attach()` method. By setting `$breakChainOnFailure` to
`true`, if the validator fails, it will short-circuit execution of the chain,
preventing subsequent validators from executing.  If the above example were
written as follows, then the alphanumeric validation would not occur if the
string length validation fails:

```php
$chain->attach(new StringLength(['min' => 6, 'max' => 12]), true);
$chain->attach(new Regex(['pattern' => '/^[a-z0-9]+$/i']));
```

Any object that implements `Laminas\Validator\ValidatorInterface` may be used in a
validator chain.

## Setting Validator Chain Order

For each validator added to the `ValidatorChain`, you can set a *priority* to
define the chain order. The default value is `1`. Higher values indicate earlier
execution, while lower values execute later; use negative values to force late
execution.

In the following example, the username is first checked to ensure that its
length is between 7 and 9 characters, and then it is checked to ensure that its
length is between 3 and 5 characters.

```php
use Laminas\Validator\StringLength;
use Laminas\Validator\ValidatorChain;

$username = 'ABCDFE';

// Create a validator chain and add validators to it
$validatorChain = new ValidatorChain();
$validatorChain->attach(
    new StringLength(['min' => 3, 'max' => 5]),
    true, // break chain on failure
    1
);
$validatorChain->attach(
    new StringLength(['min' => 7, 'max' => 9]),
    true, // break chain on failure
    2     // higher priority!
);

// Validate the username
if ($validatorChain->isValid($username)) {
    // username passed validation
    echo "Success";
} else {
    // username failed validation; print reasons
    foreach ($validatorChain->getMessages() as $message) {
        echo "$message\n";
    }
}

// This first example will display: The input is less than 7 characters long
```

## The Validator Chain Factory

It is often desirable to create validator chains from configuration arrays.
The `ValidatorChainFactory` enables this and expects configuration in the following shape:

```php
use Laminas\Validator\NotEmpty;
use Laminas\Validator\StringLength;

$chainConfiguration = [
    'First' => [
        'name' => NotEmpty::class,
        'break_chain_on_failure' => true,
        'options' => [],
        'priority' => 1,
    ],
    'Second' => [
        'name' => StringLength::class,
        'break_chain_on_failure' => true,
        'options' => [
            'min' => 5,
            'max' => 10,
        ],
        'priority' => 1,
    ],
];
```

Note: The top-level array keys `First` and `Second` above are entirely optional and only serve to improve readability; a list is perfectly acceptable.

Each element of the array **must** contain the `name` key that resolves to a validator that is configured for use in the `ValidatorPluginManager`.
The other 3 keys `break_chain_on_failure`, `options` and `priority` are optional.

- `options` are passed to the requested validator type unaltered.
- `break_chain_on_failure`, when true, will prevent later validators from executing if validation fails
- `priority` can be any arbitrary integer. Lower numbers execute first.

Retrieve the `ValidatorChainFactory` from your application's DI container and pass the chain configuration to the factory's `fromArray` method:

```php
use Laminas\Validator\ValidatorChainFactory;

$factory = $container->get(ValidatorChainFactory::class);
$chain = $factory->fromArray($chainConfiguration);
$chain->isValid('Some Value');
```

## About the `$context` Parameter

Typically, `laminas-validator` is used via [`laminas-inputfilter`](https://docs.laminas.dev/laminas-inputfilter/) which is often, in turn, used via [`laminas-form`](https://docs.laminas.dev/laminas-form/).
Some validators accept a second parameter to the `isValid()` method that contains the entire payload in an unfiltered and un-validated state.
This parameter `$context` is normally the entire `$_POST` payload.

`laminas-inputfilter` always passes this parameter to the `isValid` method, but, because it is not part of the `ValidatorInterface` contract, it's documentation has often been overlooked.

`ValidatorChain` accepts this parameter and will pass the context to all composed validators in the chain during validation.
