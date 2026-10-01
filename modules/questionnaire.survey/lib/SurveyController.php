<?php

namespace Questionnaire\Survey;

use Bitrix\Main\Engine\JsonController;
use Bitrix\Main\Engine\ActionFilter\Attribute\Rule\HttpMethod;

class SurveyController extends JsonController
{
    #[HttpMethod(['GET'])]
    public function getSurveyAction(int $surveyId): string
    {
        return SurveyEndpoint::getSurvey($surveyId);
    }

    #[HttpMethod(['POST'])]
    public function submitSurveyAction(
        int $surveyId
    ): int {
        $data = json_decode(
            $this->getRequest()->getInput(),
            true
        );

        $answers = $data['answers'] ?? [];

        $userId = $this->getCurrentUser()?->getId() ?? 0;

        return SurveySubmitService::submit(
            $surveyId,
            $userId,
            $answers
        );
    }

    protected function getDefaultPreFilters(): array
    {
        return [];
    }
}