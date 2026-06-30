<?php

return [
    'report_threshold' => (int) env('MODERATION_REPORT_THRESHOLD', 5),
    'appeal_restore_threshold' => (int) env('MODERATION_APPEAL_RESTORE_THRESHOLD', 3),
    'active_account_days' => (int) env('MODERATION_ACTIVE_ACCOUNT_DAYS', 7),
    'active_vote_count' => (int) env('MODERATION_ACTIVE_VOTE_COUNT', 3),
    'reasons' => [
        'spam',
        'abuse_hate',
        'personal_info',
        'duplicate',
        'wrong_language',
        'other',
    ],
];
