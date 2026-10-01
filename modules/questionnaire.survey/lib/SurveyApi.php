<?php

namespace Questionnaire\Survey;

class SurveyApi
{
    public static function getSurvey(int $surveyId): array
    {
        return SurveyService::getSurvey($surveyId);
    }
}