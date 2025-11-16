<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class PublicFeedbackResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $name = $this->user->name ?? 'Community Member';
        $initials = collect(preg_split('/\s+/', trim((string) $name)))
            ->filter()
            ->map(function (string $segment) {
                return strtoupper(Str::substr($segment, 0, 1));
            })
            ->take(2)
            ->implode('');

        $seedSource = strtolower($this->user->email ?? $name ?? (string) $this->id);

        return [
            'id' => $this->id,
            'user_name' => $name,
            'user_initials' => $initials ?: 'WC',
            'type' => $this->type,
            'rating' => (int) $this->rating,
            'comments' => $this->comments,
            'created_at' => optional($this->created_at)->toIso8601String(),
            'submitted_for_humans' => optional($this->created_at)?->diffForHumans(),
            'avatar_seed' => substr(md5($seedSource), 0, 12),
        ];
    }
}
