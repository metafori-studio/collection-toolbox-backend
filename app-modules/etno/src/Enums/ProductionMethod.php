<?php

namespace Metafori\Etno\Enums;

use Filament\Support\Contracts\HasLabel;
use Metafori\Core\Enums\Concerns\HasTranslatedLabel;

enum ProductionMethod: string implements HasLabel
{
    use HasTranslatedLabel;

    case AudioRecording = 'audio-recording';
    case DotMatrixPrinting = 'dot-matrix-printing';
    case Drawing = 'drawing';
    case ElectronicText = 'electronic-text';
    case Handwriting = 'handwriting';
    case InkjetPrinting = 'inkjet-printing';
    case LaserPrinting = 'laser-printing';
    case Painting = 'painting';
    case Photocopy = 'photocopy';
    case Photography = 'photography';
    case Typewriting = 'typewriting';
    case VideoRecording = 'video-recording';
}
