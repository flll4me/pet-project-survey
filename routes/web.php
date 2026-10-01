<?php

use Bitrix\Main\Routing\RoutingConfigurator;
use Questionnaire\Survey\SurveyController;

return function (RoutingConfigurator $routes) {
    $routes->get(
        '/api/surveys/{surveyId}',
        [SurveyController::class, 'getSurvey']
    );

    $routes->post(
        '/api/surveys/{surveyId}/submit',
        [SurveyController::class, 'submitSurvey']
    );
};