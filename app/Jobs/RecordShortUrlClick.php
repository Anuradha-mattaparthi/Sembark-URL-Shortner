<?php
// created job for increament the hits when user open the short url
namespace App\Jobs;

use App\Models\ShortUrlClick;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RecordShortUrlClick implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $shortUrlId;
    public ?int $companyId;
    public ?int $userId;
    public ?string $ip;
    public ?string $userAgent;
    public ?string $referer;
    public ?string $country;

    public function __construct(
        int $shortUrlId,
        ?int $companyId,
        ?int $userId,
        ?string $ip,
        ?string $userAgent,
        ?string $referer,
        ?string $country = null
    ) {
        $this->shortUrlId = $shortUrlId;
        $this->companyId  = $companyId;
        $this->userId     = $userId;
        $this->ip         = $ip;
        $this->userAgent  = $userAgent;
        $this->referer    = $referer;
        $this->country    = $country;
    }

    public function handle(): void
    {
        try {
            ShortUrlClick::create([
                'short_url_id' => $this->shortUrlId,
                'company_id'   => $this->companyId,
                'user_id'      => $this->userId,
                'ip'           => $this->ip,
                'user_agent'   => $this->userAgent,
                'referer'      => $this->referer,
                'country'      => $this->country,
            ]);
        } catch (\Throwable $e) {
            // swallow/log — don't break redirect if analytics fail
            \Log::warning('RecordShortUrlClick failed: '.$e->getMessage());
        }
    }
}
