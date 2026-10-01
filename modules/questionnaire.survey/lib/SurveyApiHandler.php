<?php

namespace Questionnaire\Survey;

class SurveyApiHandler
{
    public static function getSurvey(int $surveyId): string
    {
        $survey = SurveyApi::getSurvey($surveyId);

        return json_encode($survey);
    }
}