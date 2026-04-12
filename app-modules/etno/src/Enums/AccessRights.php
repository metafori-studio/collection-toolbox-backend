<?php

namespace Metafori\Etno\Enums;

use Filament\Support\Contracts\HasLabel;
use Metafori\Core\Enums\Concerns\HasTranslatedLabel;

enum AccessRights: string implements HasLabel
{
    use HasTranslatedLabel;

    case OpenAccess = 'open-access';
    case RestrictedAccess = 'restricted-access';
    case EmbargoedAccess = 'embargoed-access';
    case MetadataOnlyAccess = 'metadata-only-access';
    case PaidAccess = 'paid-access';
    case ClosedAccess = 'closed-access';

    public static function published(): array
    {
        return [
            self::OpenAccess,
            self::RestrictedAccess,
            self::EmbargoedAccess,
            self::MetadataOnlyAccess,
            self::PaidAccess,
        ];
    }
}
