<?php

namespace Metafori\Etno\Enums;

use Filament\Support\Contracts\HasLabel;
use Metafori\Core\Enums\Concerns\HasTranslatedLabel;

enum ItemType: string implements HasLabel
{
    use HasTranslatedLabel;

    case Questionnaire = 'questionnaire';
    case AudioRecording = 'audio_recording';
    case BachelorsThesis = 'bachelors_thesis';
    case Slide = 'slide';
    case MastersThesis = 'masters_thesis';
    case DoctoralThesis = 'doctoral_thesis';
    case Excerpt = 'excerpt';
    case Photograph = 'photograph';
    case Illustration = 'illustration';
    case Interview = 'interview';
    case LanguageTranslation = 'language_translation';
    case Copy = 'copy';
    case Correspondence = 'correspondence';
    case Drawing = 'drawing';
    case Chronicle = 'chronicle';
    case Map = 'map';
    case MediaResearch = 'media_research';
    case Monograph = 'monograph';
    case Negative = 'negative';
    case SheetMusic = 'sheet_music';
    case NewspaperArticle = 'newspaper_article';
    case Memorial = 'memorial';
    case Song = 'song';
    case Estate = 'estate';
    case FloorPlan = 'floor_plan';
    case Object = 'object';
    case Transcript = 'transcript';
    case Reproduction = 'reproduction';
    case SeminarPaper = 'seminar_paper';
    case StatisticalData = 'statistical_data';
    case ScientificStudy = 'scientific_study';
    case VideoRecording = 'video_recording';
    case ResearchReport = 'research_report';
    case WebPage = 'web_page';
}
