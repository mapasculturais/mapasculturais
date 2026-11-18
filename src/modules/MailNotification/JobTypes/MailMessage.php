<?php
namespace MailNotification\JobTypes;

use MapasCulturais\App;
use MapasCulturais\Definitions\JobType;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Header\DateHeader;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Header\IdentificationHeader;
use Symfony\Component\Mime\Header\MailboxHeader;
use Symfony\Component\Mime\Header\MailboxListHeader;
use Symfony\Component\Mime\Header\ParameterizedHeader;
use Symfony\Component\Mime\Header\PathHeader;
use Symfony\Component\Mime\Header\UnstructuredHeader;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\AlternativePart;
use Symfony\Component\Mime\Part\Multipart\MixedPart;
use Symfony\Component\Mime\Part\Multipart\RelatedPart;
use Symfony\Component\Mime\Part\TextPart;

class MailMessage extends JobType
{
    const SLUG = "MailMessage";
    const PAYLOAD_FORMAT = 'mail_message_v1';

    protected function _generateId(array $data, string $start_string, string $interval_string, int $iterations)
    {
        $serialized_message = $data['message'] ?? null;

        if(!$serialized_message) {
            throw new \Exception("Message obrigatório");
        }

        return "MailMessage-" . md5($serialized_message);
    }

    protected function _execute(\MapasCulturais\Entities\Job $job){
        $app = App::i();

        $message = self::decodeMessage((string) $job->message);

        $app->sendMailMessage($message);
    }

    /**
     * Encodes an Email as JSON so the job queue does not rely on PHP object serialization.
     */
    public static function encodeMessage(Email $message): string
    {
        $addresses = static function (array $list): array {
            return array_values(array_map(static fn (Address $address) => $address->toString(), $list));
        };

        $payload = [
            '_format' => self::PAYLOAD_FORMAT,
            'subject' => $message->getSubject(),
            'from' => $addresses($message->getFrom()),
            'to' => $addresses($message->getTo()),
            'cc' => $addresses($message->getCc()),
            'bcc' => $addresses($message->getBcc()),
            'replyTo' => $addresses($message->getReplyTo()),
            'html' => $message->getHtmlBody(),
            'text' => $message->getTextBody(),
        ];

        return json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Rebuilds an Email from the job payload (JSON preferred; legacy PHP serialize with class allowlist).
     */
    public static function decodeMessage(string $payload): Email
    {
        $trimmed = ltrim($payload);
        if ($trimmed !== '' && ($trimmed[0] === '{' || $trimmed[0] === '[')) {
            $data = json_decode($payload, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($data) && ($data['_format'] ?? null) === self::PAYLOAD_FORMAT) {
                return self::emailFromPayload($data);
            }
        }

        $message = @unserialize($payload, ['allowed_classes' => self::legacyAllowedClasses()]);
        if (!$message instanceof Email) {
            throw new \RuntimeException('Invalid mail job payload');
        }

        return $message;
    }

    /**
     * @param array<string, mixed> $data
     */
    protected static function emailFromPayload(array $data): Email
    {
        $message = new Email();

        $applyAddresses = static function (string $method, mixed $list) use ($message): void {
            if ($list === null || $list === '' || $list === []) {
                return;
            }
            $addresses = is_array($list) ? array_values($list) : [$list];
            $addresses = array_values(array_filter($addresses, static fn ($address) => $address !== null && $address !== ''));
            if (!$addresses) {
                return;
            }
            $message->$method(...$addresses);
        };

        $applyAddresses('from', $data['from'] ?? null);
        $applyAddresses('to', $data['to'] ?? null);
        $applyAddresses('cc', $data['cc'] ?? null);
        $applyAddresses('bcc', $data['bcc'] ?? null);
        $applyAddresses('replyTo', $data['replyTo'] ?? null);

        if (!empty($data['subject'])) {
            $message->subject((string) $data['subject']);
        }
        if (array_key_exists('html', $data) && $data['html'] !== null && $data['html'] !== '') {
            $message->html((string) $data['html']);
        }
        if (array_key_exists('text', $data) && $data['text'] !== null && $data['text'] !== '') {
            $message->text((string) $data['text']);
        }

        return $message;
    }

    /**
     * Classes permitted when decoding legacy jobs that still store a serialized Email object.
     *
     * @return list<class-string>
     */
    protected static function legacyAllowedClasses(): array
    {
        return [
            Email::class,
            Address::class,
            Headers::class,
            MailboxListHeader::class,
            MailboxHeader::class,
            UnstructuredHeader::class,
            IdentificationHeader::class,
            ParameterizedHeader::class,
            PathHeader::class,
            DateHeader::class,
            TextPart::class,
            DataPart::class,
            AlternativePart::class,
            MixedPart::class,
            RelatedPart::class,
            \DateTimeImmutable::class,
            \DateTime::class,
        ];
    }
}
