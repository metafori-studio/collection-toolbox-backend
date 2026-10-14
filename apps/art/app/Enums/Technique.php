<?php

namespace Metafori\Art\Enums;

use Filament\Support\Contracts\HasLabel;
use Metafori\Core\Enums\Concerns\HasTranslatedLabel;

enum Technique: string implements HasLabel
{
    use HasTranslatedLabel;

    case Acrylic = 'acrylic';
    case Aquatint = 'aquatint';
    case ArtProtis = 'art-protis';
    case BlackAndWhitePhotography = 'black-and-white-photography';
    case BlackChalk = 'black-chalk';
    case Casting = 'casting';
    case Chalk = 'chalk';
    case Charcoal = 'charcoal';
    case Collage = 'collage';
    case ColorEtching = 'color-etching';
    case ColorLithography = 'color-lithography';
    case ColorPhotography = 'color-photography';
    case ColorPrint = 'color-print';
    case ColorWoodcut = 'color-woodcut';
    case ColoredInk = 'colored-ink';
    case ColoredInkDrawing = 'colored-ink-drawing';
    case ColoredMaterialPrint = 'colored-material-print';
    case Drawing = 'drawing';
    case Drypoint = 'drypoint';
    case Etching = 'etching';
    case Gilding = 'gilding';
    case Gouache = 'gouache';
    case HammeredRelief = 'hammered-relief';
    case Ink = 'ink';
    case InkWash = 'ink-wash';
    case Linocut = 'linocut';
    case Lithography = 'lithography';
    case LithographyTint = 'lithography-tint';
    case MaterialPrint = 'material-print';
    case MixedMedia = 'mixed-media';
    case Oil = 'oil';
    case Other = 'other';
    case Pastel = 'pastel';
    case PenDrawing = 'pen-drawing';
    case Pencil = 'pencil';
    case Photography = 'photography';
    case Print = 'print';
    case Relief = 'relief';
    case Serigraphy = 'serigraphy';
    case Sgraffito = 'sgraffito';
    case TapestryWeaving = 'tapestry-weaving';
    case Tempera = 'tempera';
    case Watercolor = 'watercolor';
    case WoodEngraving = 'wood-engraving';
    case Woodcut = 'woodcut';
}
