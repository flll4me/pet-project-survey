<?php

use PHPUnit\Framework\TestCase;
use Questionnaire\Survey\SurveyResponseTable;
use Questionnaire\Survey\SurveyTable;

final class SurveyResponseTableTest extends TestCase
{
    public function testCanCreateAndGetSurveyResponse(): void
    {
        // Arrange
        $survey = SurveyTable::add([
            'TITLE' => 'Опрос для ответа',
            'ACTIVE' => true,
        ]);

        $surveyId = $survey->getId();

        // Act
        $result = SurveyResponseTable::add([
            'SURVEY_ID' => $surveyId,
            'USER_ID' => 1,
        ]);

        $responseId = $result->getId();

        $response = SurveyResponseTable::getById($responseId)->fetch();

        // Assert
        $this->assertNotNull($response);
        $this->assertEquals($surveyId, $response['SURVEY_ID']);
        $this->assertEquals(1, $response['USER_ID']);
        $this->assertNotNull($response['CREATED_AT']);
    }
}