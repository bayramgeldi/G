<?php

namespace App\Support;

use App\Models\Appeal;
use App\Models\Entry;
use App\Models\ModerationEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

final class ModeratesContent
{
    public static function contentLabel(Model $content): string
    {
        return $content instanceof Entry ? 'entry' : 'definition';
    }

    public static function authorId(Model $content): int
    {
        return (int) $content->user_id;
    }

    public static function hideByReports(Model $content, int $reportCount): void
    {
        if ($content->is_hidden) {
            return;
        }

        $content->update(['is_hidden' => true]);

        self::log('report_threshold_hide', $content, null, 'community_reports', [
            'report_count' => $reportCount,
            'threshold' => config('moderation.report_threshold'),
        ], 'community');
    }

    public static function emergencyHide(Model $content, User $actor, ?string $reason = null): void
    {
        $content->update(['is_hidden' => true]);

        self::log('emergency_hide', $content, $actor, $reason ?: 'emergency', [], 'admin');
    }

    public static function restoreFromAppeal(Model $content, Appeal $appeal): void
    {
        $content->update(['is_hidden' => false]);
        $appeal->update(['status' => 'restored']);

        self::log('appeal_restored', $content, null, 'appeal_passed', [
            'appeal_id' => $appeal->id,
            'restore_votes' => $appeal->restore_votes_count,
            'keep_hidden_votes' => $appeal->keep_hidden_votes_count,
        ], 'community');
    }

    public static function log(string $eventType, Model $subject, ?User $actor = null, ?string $reason = null, array $details = [], string $actorType = 'community'): ModerationEvent
    {
        return ModerationEvent::create([
            'actor_id' => $actor?->id,
            'actor_type' => $actorType,
            'event_type' => $eventType,
            'subject_type' => $subject::class,
            'subject_id' => $subject->id,
            'reason' => $reason,
            'details' => $details ?: null,
        ]);
    }
}
