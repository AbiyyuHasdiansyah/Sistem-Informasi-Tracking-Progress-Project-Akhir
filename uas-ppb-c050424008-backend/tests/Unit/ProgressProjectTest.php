<?php

namespace Tests\Unit;

use App\Models\ProgressProject;
use PHPUnit\Framework\TestCase;

class ProgressProjectTest extends TestCase
{
    public function test_status_is_normalized_for_approval_and_rejection(): void
    {
        $this->assertSame('Disetujui', ProgressProject::normalizeStatus('Disetujui'));
        $this->assertSame('Disetujui', ProgressProject::normalizeStatus('Diterima'));
        $this->assertSame('Ditolak', ProgressProject::normalizeStatus('Ditolak'));
        $this->assertSame('Ditolak', ProgressProject::normalizeStatus('Revisi'));
    }

    public function test_progress_can_only_be_validated_once(): void
    {
        $pendingProgress = new ProgressProject(['status' => 'Pending']);
        $this->assertFalse($pendingProgress->hasBeenValidated());

        $reviewedProgress = new ProgressProject(['status' => 'Disetujui']);
        $this->assertTrue($reviewedProgress->hasBeenValidated());
    }
}
