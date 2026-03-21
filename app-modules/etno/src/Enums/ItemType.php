<?php

namespace Metafori\Etno\Enums;

use Filament\Support\Contracts\HasLabel;
use Metafori\Core\Enums\Concerns\HasTranslatedLabel;

enum ItemType: string implements HasLabel
{
    use HasTranslatedLabel;

    case AudioRecording = 'audio-recording';
    case BachelorsThesis = 'bachelors-thesis';
    case Chronicle = 'chronicle';
    case Copy = 'copy';
    case Correspondence = 'correspondence';
    case DissertationThesis = 'dissertation-thesis';
    case Drawing = 'drawing';
    case Excerpt = 'excerpt';
    case FloorPlan = 'floor-plan';
    case Illustration = 'illustration';
    case Interview = 'interview';
    case Map = 'map';
    case MastersThesis = 'masters-thesis';
    case MediaResearch = 'media-research';
    case MemoryBook = 'memory-book';
    case Monograph = 'monograph';
    case MusicalScore = 'musical-score';
    case NewspaperArticle = 'newspaper-article';
    case PersonalPapers = 'personal-papers';
    case Photograph = 'photograph';
    case PhotographicNegative = 'photographic-negative';
    case PhotographicSlide = 'photographic-slide';
    case PhysicalObject = 'physical-object';
    case Questionnaire = 'questionnaire';
    case Reproduction = 'reproduction';
    case ResearchPaper = 'research-paper';
    case ResearchReport = 'research-report';
    case Song = 'song';
    case StatisticalData = 'statistical-data';
    case TermPaperSeminarPaper = 'term-paper-seminar-paper';
    case Transcription = 'transcription';
    case Translation = 'translation';
    case VideoRecording = 'video-recording';
    case WebPage = 'web-page';
}
