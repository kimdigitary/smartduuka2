<?php

namespace App\Jobs;

use App\Mail\SendEmails;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendEmailsJob implements ShouldQueue
{
    use Queueable;

    /**
     * @param  string|array<int, string>  $to
     */
    public function __construct(public string|array $to, public string $subj, public string $template, public mixed $data = null) {}

    public function handle(): void
    {
        Mail::to(app()->isLocal() ? 'omodingmike@gmail.com' : $this->to)
            ->send(new SendEmails($this->subj, $this->data, $this->template));
    }
}
