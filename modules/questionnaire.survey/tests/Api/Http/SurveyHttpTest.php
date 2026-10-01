<?php

use PHPUnit\Framework\TestCase;
use Questionnaire\Survey\SurveyEndpoint;
use Questionnaire\Survey\SurveyTable;

final class SurveyHttpTest extends TestCase
{
    public function testGetSurveyEndpointReturnsJson(): void
    {
        // Arrange
        $survey = SurveyTable::add([
            'TITLE' => 'HTTP тест',
            'ACTIVE' => true,
        ]);

        // Act
        $response = SurveyEndpoint::getSurvey($survey->getId());

        // Assert
        $this->assertJson($response);

        $data = json_decode($response, true);

        $this->assertSame(
            'HTTP тест',
            $data['TITLE']
        );
    }
}