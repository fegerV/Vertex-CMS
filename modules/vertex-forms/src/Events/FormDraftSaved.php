<?php

namespace Vertex\Forms\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Vertex\Forms\Models\Form;
use Vertex\Forms\Models\FormSubmission;

class FormDraftSaved
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Form $form,
        public readonly FormSubmission $submission,
    ) {}
}
