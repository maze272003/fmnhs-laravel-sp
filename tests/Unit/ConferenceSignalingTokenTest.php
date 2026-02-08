<?php

namespace Tests\Unit;

use App\Support\ConferenceSignalingToken;
use Tests\TestCase;

class ConferenceSignalingTokenTest extends TestCase
{
    public function test_token_can_be_issued_and_verified(): void
    {
        $token = ConferenceSignalingToken::issue([
            'conferenceId' => 42,
            'actorId' => 'teacher-1',
            'actorName' => 'Teacher One',
            'actorRole' => 'teacher',
        ]);

        $claims = ConferenceSignalingToken::verify($token);

        $this->assertIsArray($claims);
        $this->assertSame(42, $claims['conferenceId']);
        $this->assertSame('teacher-1', $claims['actorId']);
        $this->assertSame('Teacher One', $claims['actorName']);
        $this->assertSame('teacher', $claims['actorRole']);
        $this->assertArrayHasKey('iat', $claims);
        $this->assertArrayHasKey('exp', $claims);
    }

    public function test_tampered_token_is_rejected(): void
    {
        $token = ConferenceSignalingToken::issue([
            'conferenceId' => 42,
            'actorId' => 'student-9',
            'actorName' => 'Student Nine',
            'actorRole' => 'student',
        ]);

        $suffix = substr($token, -1);
        $tampered = substr($token, 0, -1).($suffix === 'a' ? 'b' : 'a');

        $this->assertNull(ConferenceSignalingToken::verify($tampered));
    }
}
