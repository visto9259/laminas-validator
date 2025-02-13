<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Generator;
use Laminas\Validator\EmailAddress;
use Laminas\Validator\Hostname;
use LaminasTest\Validator\TestAsset\Translator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

use function array_key_exists;
use function checkdnsrr;
use function count;
use function current;
use function implode;
use function json_encode;
use function next;
use function preg_replace;
use function sprintf;
use function str_repeat;

final class EmailAddressTest extends TestCase
{
    private EmailAddress $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new EmailAddress();
    }

    /**
     * Ensures that a basic valid e-mail address passes validation
     */
    public function testBasic(): void
    {
        $validator = new EmailAddress();
        self::assertTrue($validator->isValid('username@example.com'));
    }

    /**
     * Ensures that localhost address is valid
     */
    public function testLocalhostAllowed(): void
    {
        $validator = new EmailAddress([
            'allow' => Hostname::ALLOW_ALL,
        ]);

        self::assertTrue($validator->isValid('username@localhost'));
    }

    /**
     * Ensures that local domain names are valid
     */
    public function testLocalDomainAllowed(): void
    {
        $validator = new EmailAddress([
            'allow' => Hostname::ALLOW_ALL,
        ]);

        self::assertTrue($validator->isValid('username@localhost.localdomain'));
    }

    /**
     * Ensures that IP hostnames are valid
     */
    public function testIPAllowed(): void
    {
        $validator      = new EmailAddress([
            'allow' => Hostname::ALLOW_DNS | Hostname::ALLOW_IP,
        ]);
        $valuesExpected = [
            [Hostname::ALLOW_DNS, true, ['bob@212.212.20.4']],
            [Hostname::ALLOW_DNS, false, ['bob@localhost']],
        ];
        foreach ($valuesExpected as $element) {
            foreach ($element[2] as $input) {
                self::assertSame($element[1], $validator->isValid($input), implode("\n", $validator->getMessages()));
            }
        }
    }

    /**
     * Ensures that validation fails when the local part is missing
     */
    public function testLocalPartMissing(): void
    {
        $validator = new EmailAddress();
        self::assertFalse($validator->isValid('@example.com'));

        $messages = $validator->getMessages();

        self::assertCount(1, $messages);
        self::assertArrayHasKey(EmailAddress::INVALID_FORMAT, $messages);
    }

    /**
     * Ensures that validation fails and produces the expected messages when the local part is invalid
     */
    public function testLocalPartInvalid(): void
    {
        $validator = new EmailAddress();
        self::assertFalse($validator->isValid('Some User@example.com'));

        $messages = $validator->getMessages();

        self::assertCount(3, $messages);

        self::assertArrayHasKey(EmailAddress::DOT_ATOM, $messages);
        self::assertArrayHasKey(EmailAddress::QUOTED_STRING, $messages);
        self::assertArrayHasKey(EmailAddress::INVALID_LOCAL_PART, $messages);

        foreach ($messages as $message) {
            self::assertStringContainsString('Some User', $message);
        }
    }

    /**
     * Ensures that no validation failure message is produced when the local part follows the quoted-string format
     */
    public function testLocalPartQuotedString(): void
    {
        $validator = new EmailAddress();
        self::assertTrue($validator->isValid('"Some User"@example.com'));

        $messages = $validator->getMessages();
        self::assertCount(0, $messages);
    }

    /**
     * Ensures that validation fails when the hostname is invalid
     */
    public function testHostnameInvalid(): void
    {
        $validator = new EmailAddress();
        self::assertFalse($validator->isValid('username@ example . com'));

        $messages = $validator->getMessages();

        self::assertGreaterThanOrEqual(1, count($messages));
        self::assertArrayHasKey(EmailAddress::INVALID_HOSTNAME, $messages);
    }

    /**
     * Ensures that quoted-string local part is considered valid
     */
    public function testQuotedString(): void
    {
        $emailAddresses = [
            '""@domain.com', // Optional
            '" "@domain.com', // x20
            '"!"@domain.com', // x21
            '"\""@domain.com', // \" (escaped x22)
            '"#"@domain.com', // x23
            '"$"@domain.com', // x24
            '"Z"@domain.com', // x5A
            '"["@domain.com', // x5B
            '"\\\"@domain.com', // \\ (escaped x5C)
            '"]"@domain.com', // x5D
            '"^"@domain.com', // x5E
            '"}"@domain.com', // x7D
            '"~"@domain.com', // x7E
            '"username"@example.com',
            '"bob%jones"@domain.com',
            '"bob jones"@domain.com',
            '"bob@jones"@domain.com',
            '"[[ bob ]]"@domain.com',
            '"jones"@domain.com',
        ];

        foreach ($emailAddresses as $input) {
            $validator = new EmailAddress();
            self::assertTrue(
                $validator->isValid($input),
                "$input failed to pass validation:\n" . implode("\n", $validator->getMessages()),
            );
        }
    }

    /**
     * Ensures that quoted-string local part is considered invalid
     */
    public function testInvalidQuotedString(): void
    {
        $emailAddresses = [
            "\"\x00\"@example.com",
            "\"\x01\"@example.com",
            "\"\x1E\"@example.com",
            "\"\x1F\"@example.com",
            '"""@example.com', // x22 (not escaped)
            '"\"@example.com', // x5C (not escaped)
            "\"\x7F\"@example.com",
        ];

        foreach ($emailAddresses as $input) {
            $validator = new EmailAddress();
            self::assertFalse(
                $validator->isValid($input),
                "$input failed to pass validation:\n" . implode("\n", $validator->getMessages()),
            );
        }
    }

    /**
     * Ensures that validation fails when the e-mail is given as for display,
     * with angle brackets around the actual address
     */
    public function testEmailDisplay(): void
    {
        $validator = new EmailAddress();
        self::assertFalse($validator->isValid('User Name <username@example.com>'));

        $messages = $validator->getMessages();

        self::assertGreaterThanOrEqual(3, count($messages));
        self::assertStringContainsString('not a valid hostname', current($messages));
        self::assertStringContainsString('cannot match TLD', next($messages));
        self::assertStringContainsString('does not appear to be a valid local network name', next($messages));
    }

    /**
     * @psalm-return Generator<string, array{0: string}>
     */
    public static function validEmailAddresses(): Generator
    {
        $list = [
            'bob@domain.com',
            'bob.jones@domain.co.uk',
            'bob.jones.smythe@domain.co.uk',
            'BoB@domain.museum',
            'bobjones@domain.info',
            'bob+jones@domain.us',
            'bob+jones@domain.co.uk',
            'bob@some.domain.uk.com',
            'bob@verylongdomainsupercalifragilisticexpialidociousspoonfulofsugar.com',
            "B.O'Callaghan@domain.com",
            'иван@письмо.рф',
            'öäü@ä-umlaut.de',
            'frédéric@domain.com',
            'bob@тест.рф',
            'bob@xn--e1aybc.xn--p1ai',
        ];

        foreach ($list as $email) {
            yield $email => [$email];
        }
    }

    /**
     * Ensures that the validator follows expected behavior for valid email addresses
     */
    #[DataProvider('validEmailAddresses')]
    public function testBasicValid(string $value): void
    {
        self::assertTrue(
            $this->validator->isValid($value),
            sprintf(
                '%s failed validation: %s',
                $value,
                implode("\n", $this->validator->getMessages()),
            ),
        );
    }

    /**
     * @psalm-return array<string, array{0: string}>
     */
    public static function invalidEmailAddresses(): array
    {
        // @codingStandardsIgnoreStart
        return [
            '[empty]'                                                                  => [''],
            'bob jones@domain.com'                                                     => ['bob jones@domain.com'],
            '.bobJones@studio24.com'                                                   => ['.bobJones@studio24.com'],
            'bobJones.@studio24.com'                                                   => ['bobJones.@studio24.com'],
            'bob.Jones.@studio24.com'                                                  => ['bob.Jones.@studio24.com'],
            'bob@verylongdomainsupercalifragilisticexpialidociousaspoonfulofsugar.com' => ['bob@verylongdomainsupercalifragilisticexpialidociousaspoonfulofsugar.com'],
            'bob+domain.com'                                                           => ['bob+domain.com'],
            'bob.domain.com'                                                           => ['bob.domain.com'],
            'bob @domain.com'                                                          => ['bob @domain.com'],
            'bob@ domain.com'                                                          => ['bob@ domain.com'],
            'bob @ domain.com'                                                         => ['bob @ domain.com'],
            'Abc..123@example.com'                                                     => ['Abc..123@example.com'],
            '"bob%jones@domain.com'                                                    => ['"bob%jones@domain.com'],
            'multiline'                                                                => ['bob

            @domain.com'],
        ];
        // @codingStandardsIgnoreEnd
    }

    /**
     * Ensures that the validator follows expected behavior for invalid email addresses
     */
    #[DataProvider('invalidEmailAddresses')]
    public function testBasicInvalid(string $value): void
    {
        self::assertFalse($this->validator->isValid($value));
    }

    /**
     * Ensures that the validator follows expected behavior for valid email addresses with complex local parts
     */
    public function testComplexLocalValid(): void
    {
        $emailAddresses = [
            'Bob.Jones@domain.com',
            'Bob.Jones!@domain.com',
            'Bob&Jones@domain.com',
            '/Bob.Jones@domain.com',
            '#Bob.Jones@domain.com',
            'Bob.Jones?@domain.com',
            'Bob~Jones@domain.com',
        ];

        foreach ($emailAddresses as $input) {
            self::assertTrue($this->validator->isValid($input));
        }
    }

    /**
     * Ensures that the validator follows expected behavior for valid email addresses with the non-strict option
     */
    public function testNonStrict(): void
    {
        $validator      = new EmailAddress(['strict' => false]);
        $emailAddresses = [
            // RFC 5321 does mention a limit of 64 for the username,
            // but it also states "To the maximum extent possible,
            // implementation techniques that impose no limits on the
            // length of these objects should be used.".
            // http://tools.ietf.org/html/rfc5321#section-4.5.3.1
            'line length 320' => str_repeat('x', 309) . '@domain.com',
            'line length 321' => str_repeat('x', 310) . '@domain.com',
            'line length 911' => str_repeat('x', 900) . '@domain.com',
        ];

        foreach ($emailAddresses as $input) {
            self::assertTrue($validator->isValid($input));
        }
    }

    /** @return array<string, array{0: string, 1: bool}> */
    public static function emailAddressesForMxChecks(): array
    {
        return [
            'Bob.Jones@php.net'                => ['Bob.Jones@php.net', true],
            'Bob.Jones@zend.com'               => ['Bob.Jones@zend.com', true],
            'Bob.Jones@bad.example.com'        => ['Bob.Jones@bad.example.com', false],
            'Bob.Jones@anotherbad.example.com' => ['Bob.Jones@anotherbad.example.com', false],
        ];
    }

    /**
     * Ensures that the validator follows expected behavior for checking MX records
     */
    #[DataProvider('emailAddressesForMxChecks')]
    public function testMXRecords(string $emailAddress, bool $expect): void
    {
        $validator = new EmailAddress([
            'allow'      => Hostname::ALLOW_DNS,
            'useMxCheck' => true,
        ]);

        self::assertSame($expect, $validator->isValid($emailAddress), implode("\n", $validator->getMessages()));
    }

    /**
     * Ensures that the validator follows expected behavior for checking MX records with A record fallback.
     * This behavior is documented in RFC 2821, section 5: "If no MX records are found, but an A RR is
     * found, the A RR is treated as if it was associated with an implicit MX RR, with a preference of 0,
     * pointing to that host.
     */
    public function testNoMxRecordARecordFallback(): void
    {
        $validator = new EmailAddress([
            'allow'      => Hostname::ALLOW_DNS,
            'useMxCheck' => true,
        ]);

        $email = 'good@www.getlaminas.org';
        $host  = preg_replace('/.*@/', '', $email);

        //Assert that email host contains no MX records.
        self::assertFalse(checkdnsrr($host, 'MX'), 'Email host contains MX records');

        //Assert that email host contains at least one A record.
        self::assertTrue(checkdnsrr($host, 'A'), 'Email host contains no A records');

        //Assert that validator falls back to A record.
        self::assertTrue($validator->isValid($email), implode("\n", $validator->getMessages()));
    }

    /**
     * Test changing hostname settings via EmailAddress object
     */
    public function testHostnameSettings(): void
    {
        $validator = new EmailAddress([
            'hostnameValidator' => new Hostname([
                'useIdnCheck' => false,
            ]),
        ]);

        $valuesExpected = [
            [false, ['name@b�rger.de', 'name@h�llo.de', 'name@h�llo.se']],
        ];

        foreach ($valuesExpected as $element) {
            foreach ($element[1] as $input) {
                self::assertSame($element[0], $validator->isValid($input), implode("\n", $validator->getMessages()));
            }
        }

        // Check no TLD matching
        $validator      = new EmailAddress([
            'hostnameValidator' => new Hostname([
                'useTldCheck' => false,
            ]),
        ]);
        $valuesExpected = [
            [true, ['name@domain.xx', 'name@domain.zz', 'name@domain.madeup']],
        ];

        foreach ($valuesExpected as $element) {
            foreach ($element[1] as $input) {
                self::assertSame($element[0], $validator->isValid($input), implode("\n", $validator->getMessages()));
            }
        }
    }

    /**
     * Ensures that getMessages() returns expected default value (an empty array)
     */
    public function testGetMessages(): void
    {
        self::assertSame([], $this->validator->getMessages());
    }

    #[Group('Laminas-2861')]
    public function testHostnameValidatorMessagesShouldBeTranslated(): void
    {
        $translations = [
            'hostnameIpAddressNotAllowed'   => 'hostnameIpAddressNotAllowed translation',
            'hostnameUnknownTld'            => 'The input appears to be a DNS hostname '
            . 'but cannot match TLD against known list',
            'hostnameDashCharacter'         => 'hostnameDashCharacter translation',
            'hostnameInvalidHostnameSchema' => 'hostnameInvalidHostnameSchema translation',
            'hostnameUndecipherableTld'     => 'hostnameUndecipherableTld translation',
            'hostnameInvalidHostname'       => 'hostnameInvalidHostname translation',
            'hostnameInvalidLocalName'      => 'hostnameInvalidLocalName translation',
            'hostnameLocalNameNotAllowed'   => 'hostnameLocalNameNotAllowed translation',
        ];

        $hostnameValidator = new Hostname();
        $validator         = new EmailAddress([
            'hostnameValidator' => $hostnameValidator,
            'translator'        => new Translator($translations),
        ]);

        $validator->isValid('_XX.!!3xx@0.239,512.777');
        $messages = $hostnameValidator->getMessages();
        $found    = false;
        foreach ($messages as $code => $message) {
            if (array_key_exists($code, $translations)) {
                self::assertSame($translations[$code], $message);

                $found = true;

                break;
            }
        }

        self::assertTrue($found);
    }

    #[Group('Laminas-4888')]
    public function testEmailsExceedingLength(): void
    {
        // @codingStandardsIgnoreStart
        $emailAddresses = [
            'thislocalpathoftheemailadressislongerthantheallowedsizeof64characters@domain.com',
            'bob@verylongdomainsupercalifragilisticexpialidociousspoonfulofsugarverylongdomainsupercalifragilisticexpialidociousspoonfulofsugarverylongdomainsupercalifragilisticexpialidociousspoonfulofsugarverylongdomainsupercalifragilisticexpialidociousspoonfulofsugarexpialidociousspoonfulofsugar.com',
        ];
        // @codingStandardsIgnoreEnd

        foreach ($emailAddresses as $input) {
            self::assertFalse($this->validator->isValid($input));
        }
    }

    #[Group('Laminas-4352')]
    public function testNonStringValidation(): void
    {
        self::assertFalse($this->validator->isValid([1 => 1]));
    }

    #[Group('Laminas-7490')]
    public function testSettingHostnameMessagesThroughEmailValidator(): void
    {
        $translations = [
            'hostnameIpAddressNotAllowed'   => 'hostnameIpAddressNotAllowed translation',
            'hostnameUnknownTld'            => 'hostnameUnknownTld translation',
            'hostnameDashCharacter'         => 'hostnameDashCharacter translation',
            'hostnameInvalidHostnameSchema' => 'hostnameInvalidHostnameSchema translation',
            'hostnameUndecipherableTld'     => 'hostnameUndecipherableTld translation',
            'hostnameInvalidHostname'       => 'hostnameInvalidHostname translation',
            'hostnameInvalidLocalName'      => 'hostnameInvalidLocalName translation',
            'hostnameLocalNameNotAllowed'   => 'hostnameLocalNameNotAllowed translation',
        ];

        $validator = new EmailAddress([
            'messages' => $translations,
        ]);

        $validator->isValid('_XX.!!3xx@0.239,512.777');
        $messages = $validator->getMessages();
        $found    = false;
        foreach ($messages as $code => $message) {
            if (array_key_exists($code, $translations)) {
                self::assertSame($translations[$code], $message);

                $found = true;

                break;
            }
        }

        self::assertTrue($found);
    }

    public function testEmailAddressMessagesCanBeCustomisedViaOptions(): void
    {
        $validator = new EmailAddress([
            'messages' => [
                EmailAddress::INVALID => 'TestMessage',
            ],
        ]);

        $validator->isValid([]);

        $messages = $validator->getMessages();
        self::assertArrayHasKey(EmailAddress::INVALID, $messages, json_encode($messages));
        self::assertSame('TestMessage', $messages[EmailAddress::INVALID]);
    }

    public function testHostnameValidatorMessagesCanBeCustomisedViaOptions(): void
    {
        $validator = new EmailAddress([
            'messages' => [
                Hostname::IP_ADDRESS_NOT_ALLOWED => 'Bad Hostname',
            ],
        ]);

        self::assertFalse($validator->isValid('me@127.0.0.1'));

        $messages = $validator->getMessages();
        self::assertArrayHasKey(Hostname::IP_ADDRESS_NOT_ALLOWED, $messages, json_encode($messages));
        self::assertSame('Bad Hostname', $messages[Hostname::IP_ADDRESS_NOT_ALLOWED]);
    }

    #[Group('Laminas-11222')]
    #[Group('Laminas-11451')]
    public function testEmailAddressesWithTrailingDotInHostPartAreRejected(): void
    {
        self::assertFalse($this->validator->isValid('example@gmail.com.'));
        self::assertFalse($this->validator->isValid('test@test.co.'));
        self::assertFalse($this->validator->isValid('test@test.co.za.'));
    }

    #[Group('Laminas-130')]
    public function testUseMxCheckBasicValid(): void
    {
        $validator = new EmailAddress([
            'useMxCheck'     => true,
            'useDeepMxCheck' => true,
        ]);

        $emailAddresses = [
            'bob@gmail.com',
            'bob.jones@bbc.co.uk',
            'bob.jones.smythe@bbc.co.uk',
            'BoB@aol.com',
            'bobjones@nist.gov',
            "B.O'Callaghan@usmc.mil",
            'bob+jones@php.net',
            'bob+jones@dailymail.co.uk',
            'bob@teaparty.uk.com',
            'bob@thelongestdomainnameintheworldandthensomeandthensomemoreandmore.com',
            'test@кц.рф', // Registry for .рф-TLD
            'test@xn--j1ay.xn--p1ai',
        ];

        foreach ($emailAddresses as $input) {
            self::assertTrue(
                $validator->isValid($input),
                "$input failed to pass validation:\n" . implode("\n", $validator->getMessages()),
            );
        }
    }

    #[Group('Laminas-130')]
    public function testUseMxRecordsBasicInvalid(): void
    {
        $validator = new EmailAddress([
            'useMxCheck'     => true,
            'useDeepMxCheck' => true,
        ]);

        $emailAddresses = [
            '',
            'bob

            @domain.com',
            'bob jones@domain.com',
            '.bobJones@studio24.com',
            'bobJones.@studio24.com',
            'bob.Jones.@studio24.com',
            '"bob%jones@domain.com',
            'bob@verylongdomainsupercalifragilisticexpialidociousaspoonfulofsugar.com',
            'bob+domain.com',
            'bob.domain.com',
            'bob @domain.com',
            'bob@ domain.com',
            'bob @ domain.com',
            'Abc..123@example.com',
            'иван@письмо.рф',
            'xn--@-7sbfxdyelgv5j.xn--p1ai',
        ];

        foreach ($emailAddresses as $input) {
            self::assertFalse($validator->isValid($input), implode("\n", $this->validator->getMessages()) . $input);
        }
    }

    public function testRootAtLocalhostIsValid(): void
    {
        $validator = new EmailAddress([
            'allow' => Hostname::ALLOW_ALL,
        ]);
        self::assertTrue($validator->isValid('root@localhost'));
    }

    #[Depends('testRootAtLocalhostIsValid')]
    public function testRootAtLocalhostIsNotValidWhenDeepMxChecksAreActive(): void
    {
        $validator = new EmailAddress([
            'allow'          => Hostname::ALLOW_ALL,
            'useMxCheck'     => true,
            'useDeepMxCheck' => true,
        ]);
        self::assertFalse($validator->isValid('root@localhost'));
    }

    public function testWillNotCheckEmptyDeepMxChecks(): void
    {
        $validator = new EmailAddress([
            'useMxCheck'     => true,
            'useDeepMxCheck' => true,
        ]);

        self::assertFalse($validator->isValid('jon@example.com'));
    }
}
