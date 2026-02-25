<?php

namespace Metafori\Etno\Enums;

use Filament\Support\Contracts\HasLabel;
use Metafori\Core\Enums\Concerns\HasTranslatedLabel;

enum AcquisitionMethod: string implements HasLabel
{
    use HasTranslatedLabel;

    case Donation = 'donation';
    case Deposit = 'deposit';
    case Purchase = 'purchase';
    case Loan = 'loan';
    case Subscription = 'subscription';
    case Transfer = 'transfer';
    case Exchange = 'exchange';
    case LegalDeposit = 'legal_deposit';
    case EmployeeWork = 'employee_work';
    case Collection = 'collection';
}
