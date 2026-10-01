<?php

namespace Questionnaire\Survey;

class SurveyEndpoint
{
    public static function getSurvey(int $surveyId): string
    {
        return SurveyApiHandler::getSurvey($surveyId);
    }
}