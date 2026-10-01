<?php

use PHPUnit\Framework\TestCase;
use Questionnaire\Survey\SurveyTable;

final class SurveyTableTest extends TestCase
{
    public function testCanCreateAndGetSurveyById(): void
    {
        // Arrange
        $title = 'Тестовый опрос';

        // Act
        $result = SurveyTable::add([
            'TITLE' => $title,
            'ACTIVE' => true,
        ]);

        $surveyId = $result->getId();

        $survey = SurveyTable::getById($surveyId)->fetch();

        // Assert
        $this->assertNotNull($survey);
        $this->assertSame($title, $survey['TITLE']);
    }

    public function testCanCreateSurvey(): void
    {
        $result = SurveyTable::add([
            'TITLE' => 'TDD тестовый опрос',
            'ACTIVE' => true,
        ]);

        $this->assertTrue($result->isSuccess());

        $surveyId = $result->getId();

        $this->assertGreaterThan(0, $surveyId);

        $survey = SurveyTable::getById($surveyId)->fetch();

        $this->assertNotFalse($survey);
        $this->assertSame('TDD тестовый опрос', $survey['TITLE']);
        $this->assertTrue((bool)$survey['ACTIVE']);
    }

    public function testCanUpdateSurvey(): void
    {
        $result = SurveyTable::add([
            'TITLE' => 'Опрос до изменения',
            'ACTIVE' => true,
        ]);

        $surveyId = $result->getId();

        $result = SurveyTable::update($surveyId, [
            'TITLE' => 'Опрос после изменения',
            'ACTIVE' => false,
        ]);

        $this->assertTrue($result->isSuccess());

        $survey = SurveyTable::getById($surveyId)->fetch();

        $this->assertNotFalse($survey);
        $this->assertSame('Опрос после изменения', $survey['TITLE']);
        $this->assertFalse((bool)$survey['ACTIVE']);
    }

}