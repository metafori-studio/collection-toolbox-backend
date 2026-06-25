<?php

namespace Metafori\Etno\Enums;

use Filament\Support\Contracts\HasLabel;
use Metafori\Core\Enums\Concerns\HasTranslatedLabel;

enum CollectionMethod: string implements HasLabel
{
    use HasTranslatedLabel;

    case AcquisitionOfCollectionsAndFonds = 'acquisition-of-collections-and-fonds';
    case ArchivalAndDocumentaryResearch = 'archival-and-documentary-research';
    case ContentAndMediaAnalysis = 'content-and-media-analysis';
    case DigitalEthnography = 'digital-ethnography';
    case FieldResearch = 'field-research';
    case PhotographicAndAudiovisualDocumentation = 'photographic-and-audiovisual-documentation';
    case QuestionnaireAndSurveyResearch = 'questionnaire-and-survey-research';
    case ReproductionTranscriptionAndExcerption = 'reproduction-transcription-and-excerption';
    case TranslationAndLinguisticProcessing = 'translation-and-linguistic-processing';
}
