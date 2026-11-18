<?php

namespace Tests;

use EvaluationMethodTechnical\Quotas;
use MailNotification\JobTypes\MailMessage;
use MapasCulturais\App;
use ReflectionClass;
use Symfony\Component\Mime\Email;
use Tests\Abstract\TestCase;

/**
 * Hardening against CWE-502 (PHP object injection via unserialize).
 *
 * Covers Quotas meta decoding and MailMessage job payload encoding.
 * Does not use exploit gadget chains — only verifies objects are not instantiated
 * from untrusted strings and that legitimate JSON/scalar/array payloads still work.
 */
class UnserializeSecurityHardeningTest extends TestCase
{
    private function quotasDecoder(): Quotas
    {
        $ref = new ReflectionClass(Quotas::class);
        /** @var Quotas $instance */
        $instance = $ref->newInstanceWithoutConstructor();
        return $instance;
    }

    private function invokeQuotas(string $method, mixed $value): mixed
    {
        $ref = new ReflectionClass(Quotas::class);
        $m = $ref->getMethod($method);
        $m->setAccessible(true);
        return $m->invoke($this->quotasDecoder(), $value);
    }

    public function testDecodeMetaValueAcceptsJsonAndPlainStrings(): void
    {
        $this->assertSame(['a' => 1], $this->invokeQuotas('decodeMetaValue', '{"a":1}'));
        $this->assertSame('opcao-x', $this->invokeQuotas('decodeMetaValue', 'opcao-x'));
        $this->assertSame(null, $this->invokeQuotas('decodeMetaValue', null));
        $this->assertSame('', $this->invokeQuotas('decodeMetaValue', ''));
    }

    public function testDecodeMetaValueAcceptsLegacySerializedScalarsAndArrays(): void
    {
        $this->assertSame(['foo', 'bar'], $this->invokeQuotas('decodeMetaValue', serialize(['foo', 'bar'])));
        $this->assertSame(false, $this->invokeQuotas('decodeMetaValue', 'b:0;'));
        $this->assertSame(42, $this->invokeQuotas('decodeMetaValue', serialize(42)));
    }

    public function testDecodeMetaValueDoesNotInstantiateObjects(): void
    {
        $payload = serialize((object) ['marker' => 'should-not-instantiate']);
        $result = $this->invokeQuotas('decodeMetaValue', $payload);

        $this->assertIsString($result);
        $this->assertSame($payload, $result);
        $this->assertNotInstanceOf(\stdClass::class, $result);
    }

    public function testNormalizeQuotaFieldValueAcceptsJsonArrayAndPlainString(): void
    {
        $this->assertSame(['A', 'B'], $this->invokeQuotas('normalizeQuotaFieldValue', '["A","B"]'));
        $this->assertSame(['unica'], $this->invokeQuotas('normalizeQuotaFieldValue', 'unica'));
        $this->assertSame([], $this->invokeQuotas('normalizeQuotaFieldValue', null));
        $this->assertSame(['x', 'y'], $this->invokeQuotas('normalizeQuotaFieldValue', ['x', 'y']));
    }

    public function testNormalizeQuotaFieldValueAcceptsLegacySerializedArraysOnly(): void
    {
        $this->assertSame(['A', 'B'], $this->invokeQuotas('normalizeQuotaFieldValue', serialize(['A', 'B'])));

        $objectPayload = serialize((object) ['x' => 1]);
        $this->assertSame([$objectPayload], $this->invokeQuotas('normalizeQuotaFieldValue', $objectPayload));
    }

    public function testMailMessageJsonRoundTripPreservesEnvelope(): void
    {
        $app = App::i();
        $app->config['mailer.from'] = 'from@mapasculturais.test';
        $app->config['mailer.alwaysTo'] = null;
        $app->config['mailer.bcc'] = null;
        $app->config['mailer.replyTo'] = null;

        $original = $app->createMailMessage([
            'to' => 'dest@example.org',
            'subject' => 'Assunto de teste',
            'html' => '<p>corpo</p>',
            'text' => 'corpo',
        ]);

        $encoded = MailMessage::encodeMessage($original);
        $this->assertStringStartsWith('{', $encoded);
        $this->assertStringContainsString('"mail_message_v1"', $encoded);
        $this->assertStringNotContainsString('O:', $encoded);

        $decoded = MailMessage::decodeMessage($encoded);
        $this->assertInstanceOf(Email::class, $decoded);
        $this->assertSame('Assunto de teste', $decoded->getSubject());
        $this->assertSame('<p>corpo</p>', $decoded->getHtmlBody());
        $this->assertNotEmpty($decoded->getTo());
        $this->assertSame('dest@example.org', $decoded->getTo()[0]->getAddress());
    }

    public function testMailMessageLegacySerializeStillWorksWithAllowlist(): void
    {
        $app = App::i();
        $app->config['mailer.from'] = 'from@mapasculturais.test';
        $app->config['mailer.alwaysTo'] = null;
        $app->config['mailer.bcc'] = null;
        $app->config['mailer.replyTo'] = null;

        $original = $app->createMailMessage([
            'to' => 'legacy@example.org',
            'subject' => 'Legacy',
            'html' => '<b>ok</b>',
        ]);

        $legacy = serialize($original);
        $decoded = MailMessage::decodeMessage($legacy);

        $this->assertInstanceOf(Email::class, $decoded);
        $this->assertSame('Legacy', $decoded->getSubject());
        $this->assertSame('legacy@example.org', $decoded->getTo()[0]->getAddress());
    }

    public function testMailMessageRejectsArbitrarySerializedObjects(): void
    {
        $this->expectException(\RuntimeException::class);
        MailMessage::decodeMessage(serialize((object) ['evil' => true]));
    }

    public function testOpauthStyleUnserializeDoesNotInstantiateObjects(): void
    {
        $arrayPayload = base64_encode(serialize(['auth' => ['uid' => '1'], 'timestamp' => time(), 'signature' => 'x']));
        $arrayResult = unserialize(base64_decode($arrayPayload), ['allowed_classes' => false]);
        $this->assertIsArray($arrayResult);
        $this->assertSame('1', $arrayResult['auth']['uid']);

        $objectPayload = base64_encode(serialize((object) ['auth' => []]));
        $objectResult = unserialize(base64_decode($objectPayload), ['allowed_classes' => false]);
        $this->assertFalse(is_a($objectResult, \stdClass::class));
    }
}
