<?php

namespace App\Models;

use App\Services\EntrySocialImageGenerator;
use App\Support\NormalizesTurkmenText;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class AnonymousSubmission extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_PUBLISHED = 'published';

    public const VOTE_APPROVE = 'approve';
    public const VOTE_REJECT = 'reject';

    public const MINIMUM_APPROVALS = 2;
    public const APPROVAL_RATIO = 0.66;

    protected $fillable = [
        'term',
        'normalized_term',
        'meaning',
        'example',
        'status',
        'published_entry_id',
        'published_definition_id',
        'submitter_ip_hash',
        'submitter_user_agent_hash',
    ];

    public static function makePending(array $attributes): self
    {
        return static::create([
            'term' => $attributes['term'],
            'normalized_term' => NormalizesTurkmenText::normalize($attributes['term']),
            'meaning' => $attributes['meaning'],
            'example' => $attributes['example'] ?? null,
            'submitter_ip_hash' => $attributes['submitter_ip_hash'] ?? null,
            'submitter_user_agent_hash' => $attributes['submitter_user_agent_hash'] ?? null,
        ]);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(AnonymousSubmissionVote::class);
    }

    public function publishedEntry(): BelongsTo
    {
        return $this->belongsTo(Entry::class, 'published_entry_id');
    }

    public function publishedDefinition(): BelongsTo
    {
        return $this->belongsTo(Definition::class, 'published_definition_id');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function approvalCount(): int
    {
        return $this->votes->where('vote', self::VOTE_APPROVE)->count();
    }

    public function rejectCount(): int
    {
        return $this->votes->where('vote', self::VOTE_REJECT)->count();
    }

    public function approvalPercent(): int
    {
        $total = $this->votes->count();

        if ($total === 0) {
            return 0;
        }

        return (int) floor(($this->approvalCount() / $total) * 100);
    }

    public function hasPublishApproval(): bool
    {
        $total = $this->votes()->count();
        $approvals = $this->votes()->where('vote', self::VOTE_APPROVE)->count();

        return $approvals >= self::MINIMUM_APPROVALS
            && $total > 0
            && ($approvals / $total) >= self::APPROVAL_RATIO;
    }

    public function publishIfApproved(): bool
    {
        if (! $this->isPending() || ! $this->hasPublishApproval()) {
            return false;
        }

        DB::transaction(function (): void {
            $submission = static::query()->lockForUpdate()->findOrFail($this->id);

            if (! $submission->isPending() || ! $submission->hasPublishApproval()) {
                return;
            }

            $entry = Entry::query()
                ->visible()
                ->where('normalized_term', $submission->normalized_term)
                ->first();

            if (! $entry) {
                $entry = Entry::create([
                    'user_id' => null,
                    'term' => $submission->term,
                    'slug' => Entry::uniqueSlug($submission->term),
                    'normalized_term' => $submission->normalized_term,
                ]);
            }

            $definition = $entry->definitions()->create([
                'user_id' => null,
                'meaning' => $submission->meaning,
                'example' => $submission->example,
            ]);

            $submission->update([
                'status' => self::STATUS_PUBLISHED,
                'published_entry_id' => $entry->id,
                'published_definition_id' => $definition->id,
            ]);

            $this->published_entry_id = $entry->id;
        });

        if ($this->published_entry_id) {
            $publishedEntry = Entry::find($this->published_entry_id);

            if ($publishedEntry) {
                app(EntrySocialImageGenerator::class)->generate($publishedEntry);
            }
        }

        return $this->fresh()->status === self::STATUS_PUBLISHED;
    }
}
