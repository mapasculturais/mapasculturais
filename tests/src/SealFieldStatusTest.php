<?php

namespace Tests;

use DateTime;
use DateTimeZone;
use MapasCulturais\Entities\SealRelationField;
use Tests\Abstract\TestCase;

/**
 * Unit tests for SealRelationField::getFieldStatus() state machine.
 */
class SealFieldStatusTest extends TestCase
{
    protected function getStatusFor(?string $dateSpec): string
    {
        $field = new SealRelationField();
        if ($dateSpec !== null) {
            $field->expiryDate = new DateTime($dateSpec, new DateTimeZone('UTC'));
        }

        return $field->getFieldStatus();
    }

    public function testNoExpirationReturnsNoExpiration(): void
    {
        $this->assertSame('no_expiration', $this->getStatusFor(null));
    }

    public function testFutureExpiryReturnsValid(): void
    {
        $this->assertSame('valid', $this->getStatusFor('+30 days'));
    }

    public function testWithinWarningWindowReturnsAboutToExpire(): void
    {
        $this->assertSame('about_to_expire', $this->getStatusFor('+3 days'));
    }

    public function testPastExpiryReturnsExpired(): void
    {
        $this->assertSame('expired', $this->getStatusFor('-1 day'));
    }

    public function testBoundaryExactlySevenDaysIsAboutToExpire(): void
    {
        // warning_date = expiry - 7 days; when expiry is exactly today + 7, warning_date == today
        $this->assertSame('about_to_expire', $this->getStatusFor('+7 days'));
    }

    public function testBoundaryEightDaysIsValid(): void
    {
        $this->assertSame('valid', $this->getStatusFor('+8 days'));
    }

    public function testExpiryTodayIsAboutToExpire(): void
    {
        // expiry == today means it has not crossed into 'expired' (< today), but is inside the window
        $this->assertSame('about_to_expire', $this->getStatusFor('today'));
    }
}
