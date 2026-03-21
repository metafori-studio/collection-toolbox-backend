<?php

namespace Metafori\Etno\Enums;

use Filament\Support\Contracts\HasLabel;
use Metafori\Core\Enums\Concerns\HasTranslatedLabel;

enum AccrualMethod: string implements HasLabel
{
    use HasTranslatedLabel;

    case Deposit = 'deposit';
    case Donation = 'donation';
    case Exchange = 'exchange';
    case Harvesting = 'harvesting';
    case LegalDeposit = 'legal-deposit';
    case Loan = 'loan';
    case Purchase = 'purchase';
    case Subscription = 'subscription';
    case Transfer = 'transfer';
    case WorkMadeForHire = 'work-made-for-hire';
}
