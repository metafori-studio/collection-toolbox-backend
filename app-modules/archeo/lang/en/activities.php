<?php

return [
    'notifications' => [
        'import_success' => [
            'title' => 'Import completed',
            'body' => 'Created: :created',
            'body_with_updated' => 'Created: :created, Updated: :updated',
            'body_with_errors' => "Created: :created\n\nFailed:\n:errors",
            'body_with_updated_and_errors' => "Created: :created, Updated: :updated\n\nFailed:\n:errors",
        ],
        'import_failed' => [
            'title' => 'Import failed',
            'body' => 'The import could not be completed. Please try again or contact support.',
        ],
        'import_queued' => [
            'title' => 'Import queued',
            'body' => 'Your file is being processed. You will be notified when the import is complete.',
        ],
    ],
];
