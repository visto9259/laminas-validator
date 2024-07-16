<?php

declare(strict_types=1);

namespace Laminas\Validator;

use Laminas\Stdlib\ArrayUtils;
use Laminas\Translator\TranslatorInterface;
use Traversable;

use function array_key_exists;
use function array_keys;
use function array_unique;
use function current;
use function implode;
use function is_array;
use function is_object;
use function is_string;
use function key;
use function method_exists;
use function str_repeat;
use function str_replace;
use function strlen;
use function substr;
use function ucfirst;
use function var_export;

use const SORT_REGULAR;

/**
 * @psalm-type AbstractOptions = array{
 *     messages: array<string, string>,
 *     messageTemplates: array<string, string>,
 *     messageVariables: array<string, mixed>,
 *     translator: TranslatorInterface|null,
 *     translatorTextDomain: string|null,
 *     translatorEnabled: bool,
 *     valueObscured: bool,
 * }
 * @property array<string, mixed> $options
 * @property array<string, string> $messageTemplates
 * @property array<string, mixed> $messageVariables
 */
abstract class AbstractValidator implements
    Translator\TranslatorAwareInterface,
    ValidatorInterface
{
    /**
     * The value to be validated
     *
     * phpcs:disable WebimpressCodingStandard.Classes.NoNullValues
     */
    protected mixed $value = null;

    /**
     * Default translation object for all validate objects
     */
    protected static ?TranslatorInterface $defaultTranslator = null;

    /**
     * Default text domain to be used with translator
     */
    protected static string $defaultTranslatorTextDomain = 'default';

    /**
     * Limits the maximum returned length of an error message
     *
     * @var int
     */
    protected static $messageLength = -1;

    /** @var AbstractOptions&array<string, mixed> */
    protected array $abstractOptions = [
        'messages'             => [], // Array of validation failure messages
        'messageTemplates'     => [], // Array of validation failure message templates
        'messageVariables'     => [], // Array of additional variables available for validation failure messages
        'translator'           => null, // Translator instance to use -> TranslatorInterface
        'translatorTextDomain' => null, // Translation text domain
        'translatorEnabled'    => true, // Is translation enabled?
        'valueObscured'        => false, // Flag indicating whether value should be obfuscated in error messages
    ];

    /**
     * Abstract constructor for all validators
     * A validator should accept following parameters:
     *  - nothing f.e. Validator()
     *  - one or multiple scalar values f.e. Validator($first, $second, $third)
     *  - an array f.e. Validator(array($first => 'first', $second => 'second', $third => 'third'))
     *  - an instance of Traversable f.e. Validator($config_instance)
     *
     * @param array<string, mixed>|Traversable<string, mixed> $options
     */
    public function __construct($options = null)
    {
        // The abstract constructor allows no scalar values
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }

        /** @psalm-suppress RedundantConditionGivenDocblockType */
        if (isset($this->messageTemplates) && is_array($this->messageTemplates)) {
            $this->abstractOptions['messageTemplates'] = $this->messageTemplates;
        }

        /** @psalm-suppress RedundantConditionGivenDocblockType */
        if (isset($this->messageVariables) && is_array($this->messageVariables)) {
            $this->abstractOptions['messageVariables'] = $this->messageVariables;
        }

        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    /**
     * Returns an option
     *
     * @deprecated Since 2.61.0 This method will be removed in 3.0
     *
     * @param string $option Option to be returned
     * @return mixed Returned option
     * @throws Exception\InvalidArgumentException
     */
    public function getOption($option)
    {
        if (array_key_exists($option, $this->abstractOptions)) {
            return $this->abstractOptions[$option];
        }

        /** @psalm-suppress RedundantConditionGivenDocblockType */
        if (isset($this->options) && array_key_exists($option, $this->options)) {
            return $this->options[$option];
        }

        throw new Exception\InvalidArgumentException("Invalid option '$option'");
    }

    /**
     * Returns all available options
     *
     * @deprecated Since 2.61.0 This method will be removed in 3.0
     *
     * @return array<string, mixed> Array with all available options
     */
    public function getOptions()
    {
        $result = $this->abstractOptions;
        /** @psalm-suppress RedundantConditionGivenDocblockType */
        if (isset($this->options) && is_array($this->options)) {
            $result += $this->options;
        }
        return $result;
    }

    /**
     * Sets one or multiple options
     *
     * @deprecated Since 2.61.0 This method will be removed in 3.0 - Options should be passed to the constructor
     *
     * @param array<string, mixed>|Traversable<string, mixed> $options Options to set
     * @return self Provides fluid interface
     * @throws Exception\InvalidArgumentException If $options is not an array or Traversable.
     */
    public function setOptions($options = [])
    {
        /** @psalm-suppress DocblockTypeContradiction */
        if (! is_array($options) && ! $options instanceof Traversable) {
            throw new Exception\InvalidArgumentException(__METHOD__ . ' expects an array or Traversable');
        }

        /**
         * @psalm-suppress RedundantConditionGivenDocblockType
         * @psalm-var mixed $option
         */
        foreach ($options as $name => $option) {
            $fname  = 'set' . ucfirst($name);
            $fname2 = 'is' . ucfirst($name);
            if (($name !== 'setOptions') && method_exists($this, $name)) {
                $this->{$name}($option);
            } elseif (($fname !== 'setOptions') && method_exists($this, $fname)) {
                $this->{$fname}($option);
            } elseif (method_exists($this, $fname2)) {
                $this->{$fname2}($option);
            } elseif (isset($this->options) && is_array($this->options)) {
                $this->options[$name] = $option;
            } else {
                $this->abstractOptions[$name] = $option;
            }
        }

        return $this;
    }

    /**
     * Returns array of validation failure messages
     *
     * @return array<string, string>
     */
    public function getMessages()
    {
        return array_unique($this->abstractOptions['messages'], SORT_REGULAR);
    }

    /**
     * Invoke as command
     *
     * @return bool
     */
    public function __invoke(mixed $value)
    {
        return $this->isValid($value);
    }

    /**
     * Returns an array of the names of variables that are used in constructing validation failure messages
     *
     * @deprecated Since 2.61.0 This method will be removed in 3.0
     *
     * @return list<string>
     */
    public function getMessageVariables()
    {
        return array_keys($this->abstractOptions['messageVariables']);
    }

    /**
     * Returns the message templates from the validator
     *
     * @deprecated Since 2.61.0 This method will be removed in 3.0
     *
     * @return array<string, string>
     */
    public function getMessageTemplates()
    {
        return $this->abstractOptions['messageTemplates'];
    }

    /**
     * Sets the validation failure message template for a particular key
     *
     * @param  string      $messageString
     * @param  string|null $messageKey     OPTIONAL
     * @return $this Provides a fluent interface
     * @throws Exception\InvalidArgumentException
     */
    public function setMessage($messageString, $messageKey = null)
    {
        if ($messageKey === null) {
            $keys = array_keys($this->abstractOptions['messageTemplates']);
            foreach ($keys as $key) {
                $this->setMessage($messageString, $key);
            }
            return $this;
        }

        if (! isset($this->abstractOptions['messageTemplates'][$messageKey])) {
            throw new Exception\InvalidArgumentException("No message template exists for key '$messageKey'");
        }

        $this->abstractOptions['messageTemplates'][$messageKey] = $messageString;
        return $this;
    }

    /**
     * Sets validation failure message templates given as an array, where the array keys are the message keys,
     * and the array values are the message template strings.
     *
     * @deprecated Since 2.61.0 This method will be removed in 3.0
     *             Provide customised messages via the `messages` constructor option
     *
     * @param array<string, string> $messages
     * @return $this
     */
    public function setMessages(array $messages)
    {
        foreach ($messages as $key => $message) {
            $this->setMessage($message, $key);
        }
        return $this;
    }

    /**
     * Magic function returns the value of the requested property, if and only if it is the value or a
     * message variable.
     *
     * @deprecated Since 2.61.0 This method will be removed in 3.0 - It will no longer be possible to fetch any internal
     *             properties
     *
     * @param string $property
     * @return mixed
     * @throws Exception\InvalidArgumentException
     */
    public function __get($property)
    {
        if ($property === 'value') {
            return $this->value;
        }

        if (array_key_exists($property, $this->abstractOptions['messageVariables'])) {
            /** @psalm-var mixed $result */
            $result = $this->abstractOptions['messageVariables'][$property];
            if (is_array($result)) {
                return $this->{key($result)}[current($result)];
            }
            return $this->{$result};
        }

        /** @psalm-suppress RedundantConditionGivenDocblockType */
        if (isset($this->messageVariables) && array_key_exists($property, $this->messageVariables)) {
            /** @psalm-var mixed $result */
            $result = $this->{$this->messageVariables[$property]};
            if (is_array($result)) {
                return $this->{key($result)}[current($result)];
            }
            return $this->{$result};
        }

        throw new Exception\InvalidArgumentException("No property exists by the name '$property'");
    }

    /**
     * Constructs and returns a validation failure message with the given message key and value.
     *
     * Returns null if and only if $messageKey does not correspond to an existing template.
     *
     * If a translator is available and a translation exists for $messageKey,
     * the translation will be used.
     */
    protected function createMessage(string $messageKey, mixed $value): ?string
    {
        if (! isset($this->abstractOptions['messageTemplates'][$messageKey])) {
            return null;
        }

        $message = $this->abstractOptions['messageTemplates'][$messageKey];

        $message = $this->translateMessage($messageKey, $message);

        if (is_object($value)) {
            $value = method_exists($value, '__toString')
                ? (string) $value
                : $value::class . ' object';
        } elseif (is_array($value)) {
            $value = var_export($value, true);
        } else {
            $value = (string) $value;
        }

        if ($this->isValueObscured()) {
            $value = str_repeat('*', strlen($value));
        }

        $message = str_replace('%value%', $value, $message);
        foreach ($this->abstractOptions['messageVariables'] as $ident => $property) {
            if (is_array($property)) {
                $value = $this->{key($property)}[current($property)];
                if (is_array($value)) {
                    $value = '[' . implode(', ', $value) . ']';
                }
            } else {
                $value = $this->$property;
            }
            $message = str_replace("%$ident%", (string) $value, $message);
        }

        $length = self::getMessageLength();
        if (($length > -1) && (strlen($message) > $length)) {
            $message = substr($message, 0, $length - 3) . '...';
        }

        return $message;
    }

    protected function error(string $messageKey, mixed $value = null): void
    {
        if ($value === null) {
            /** @psalm-var mixed $value */
            $value = $this->value;
        }

        $message = $this->createMessage($messageKey, $value);
        if (! is_string($message)) {
            return;
        }

        $this->abstractOptions['messages'][$messageKey] = $message;
    }

    /**
     * Returns the validation value
     *
     * @return mixed Value to be validated
     */
    protected function getValue()
    {
        return $this->value;
    }

    /**
     * Sets the value to be validated and clears the messages and errors arrays
     *
     * @return void
     */
    protected function setValue(mixed $value)
    {
        $this->value                       = $value;
        $this->abstractOptions['messages'] = [];
    }

    /**
     * Set flag indicating whether or not value should be obfuscated in messages
     *
     * @deprecated Since 2.61.0 This method will be removed in 3.0 - Use the `valueObscured` option via the constructor
     *
     * @param bool $flag
     * @return $this
     */
    public function setValueObscured($flag)
    {
        /** @psalm-suppress RedundantCastGivenDocblockType */
        $this->abstractOptions['valueObscured'] = (bool) $flag;
        return $this;
    }

    /**
     * Retrieve flag indicating whether or not value should be obfuscated in
     * messages
     *
     * @deprecated Since 2.61.0 This method will be removed in 3.0
     *
     * @return bool
     */
    public function isValueObscured()
    {
        return $this->abstractOptions['valueObscured'];
    }

    /**
     * Set the translator for this instance
     */
    public function setTranslator(?TranslatorInterface $translator = null, ?string $textDomain = null): void
    {
        $this->abstractOptions['translator'] = $translator;
        if (null !== $textDomain) {
            $this->setTranslatorTextDomain($textDomain);
        }
    }

    /**
     * Return the translator for this instance
     */
    public function getTranslator(): ?TranslatorInterface
    {
        if (! $this->isTranslatorEnabled()) {
            return null;
        }

        $translator = $this->abstractOptions['translator'] ?? null;
        if ($translator instanceof TranslatorInterface) {
            return $translator;
        }

        return self::$defaultTranslator;
    }

    /**
     * Does this validator have its own specific translator?
     *
     * @deprecated Since 2.61.0 This method will be removed in 3.0
     */
    public function hasTranslator(): bool
    {
        return $this->abstractOptions['translator'] instanceof TranslatorInterface;
    }

    /**
     * Set translation text domain
     *
     * @deprecated  since 2.61.0 This method will be removed in 3.0 Use the `translatorTextDomain` option, or set
     *              the text domain at the same time as the translator via `setTranslator()`
     */
    public function setTranslatorTextDomain(string $textDomain = 'default'): void
    {
        $this->abstractOptions['translatorTextDomain'] = $textDomain;
    }

    /**
     * Return the translation text domain
     *
     * @deprecated Since 2.61.0 This method will be removed in 3.0
     */
    public function getTranslatorTextDomain(): string
    {
        if (null === $this->abstractOptions['translatorTextDomain']) {
            $this->abstractOptions['translatorTextDomain'] =
                self::getDefaultTranslatorTextDomain();
        }

        return $this->abstractOptions['translatorTextDomain'];
    }

    /**
     * Set the default, static translator for all validators
     */
    public static function setDefaultTranslator(
        ?TranslatorInterface $translator = null,
        ?string $textDomain = null,
    ): void {
        static::$defaultTranslator = $translator;
        if (null !== $textDomain) {
            self::setDefaultTranslatorTextDomain($textDomain);
        }
    }

    /**
     * Return the default translator
     *
     * @deprecated Since 2.61.0 This method will be removed in 3.0
     */
    public static function getDefaultTranslator(): ?TranslatorInterface
    {
        return static::$defaultTranslator;
    }

    /**
     * Is there a default translator available
     *
     * @deprecated Since 2.61.0 This method will be removed in 3.0
     */
    public static function hasDefaultTranslator(): bool
    {
        return self::$defaultTranslator !== null;
    }

    /**
     * Set default translation text domain for all validate objects
     */
    public static function setDefaultTranslatorTextDomain(string $textDomain = 'default'): void
    {
        static::$defaultTranslatorTextDomain = $textDomain;
    }

    /**
     * Get default translation text domain for all validate objects
     *
     * @deprecated Since 2.61.0 This method will be removed in 3.0
     */
    public static function getDefaultTranslatorTextDomain(): string
    {
        return static::$defaultTranslatorTextDomain;
    }

    /**
     * Indicate whether or not translation should be enabled
     *
     * @deprecated Since 2.61.0 This method will be removed in 3.0
     */
    public function setTranslatorEnabled(bool $enabled = true): void
    {
        $this->abstractOptions['translatorEnabled'] = $enabled;
    }

    /**
     * Is translation enabled?
     *
     * @deprecated Since 2.61.0 This method will be removed in 3.0
     */
    public function isTranslatorEnabled(): bool
    {
        return $this->abstractOptions['translatorEnabled'];
    }

    /**
     * Returns the maximum allowed message length
     *
     * @deprecated Since 2.61.0 This method will be removed in 3.0
     */
    public static function getMessageLength(): int
    {
        return static::$messageLength;
    }

    /**
     * Sets the maximum allowed message length
     */
    public static function setMessageLength(int $length = -1): void
    {
        static::$messageLength = $length;
    }

    /**
     * Translate a validation message
     *
     * @param  string $messageKey
     */
    protected function translateMessage($messageKey, string $message): string
    {
        $translator = $this->getTranslator();
        if (! $translator) {
            return $message;
        }

        return $translator->translate($message, $this->getTranslatorTextDomain());
    }
}
