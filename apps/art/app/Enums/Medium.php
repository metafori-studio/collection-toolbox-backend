<?php

namespace Metafori\Art\Enums;

use Filament\Support\Contracts\HasLabel;
use Metafori\Core\Enums\Concerns\HasTranslatedLabel;

enum Medium: string implements HasLabel
{
    use HasTranslatedLabel;

    case Bronze = 'bronze';
    case Canvas = 'canvas';
    case Cardboard = 'cardboard';
    case Copper = 'copper';
    case Hardboard = 'hardboard';
    case Paper = 'paper';
    case PaperCardboard = 'paper-cardboard';
    case PaperPolyester = 'paper-polyester';
    case Paperboard = 'paperboard';
    case Plaster = 'plaster';
    case Plastic = 'plastic';
    case Plywood = 'plywood';
    case SilverGelatinPaper = 'silver-gelatin-paper';
    case Tin = 'tin';
    case Wool = 'wool';
}
