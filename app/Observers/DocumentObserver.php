<?php

namespace App\Observers;

use App\Models\Document;
use App\Services\AutomationWebhookDispatcher;

class DocumentObserver
{
    public function __construct(private readonly AutomationWebhookDispatcher $dispatcher)
    {
    }

    public function created(Document $document): void
    {
        $this->dispatcher->dispatch('document.created', $document);
    }

    public function updated(Document $document): void
    {
        $this->dispatcher->dispatch('document.updated', $document);
    }

    public function deleted(Document $document): void
    {
        $this->dispatcher->dispatch('document.deleted', $document);
    }

    public function restored(Document $document): void
    {
        $this->dispatcher->dispatch('document.restored', $document);
    }
}
