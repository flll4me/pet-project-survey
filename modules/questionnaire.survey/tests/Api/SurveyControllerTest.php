<?php

use PHPUnit\Framework\TestCase;
use Questionnaire\Survey\SurveyController;
use Questionnaire\Survey\SurveyTable;
use Questionnaire\Survey\QuestionTable;
use Questionnaire\Survey\AnswerOptionTable;


final class SurveyControllerTest extends TestCase
{
    public function testCanGetSurvey(): void
    {
        // Arrange
        $survey = SurveyTable::add([
            'TITLE' => 'Контроллер тест',
            'ACTIVE' => true,
        ]);

        // Act
        $response = (new SurveyController())
            ->getSurveyAction($survey->getId());

        // Assert
        $this->assertJson($response);

        $data = json_decode($response, true);

        $this->assertSame('Контроллер тест', $data['TITLE']);
        $this->assertSame($survey->getId(), $data['ID']);
    }

    public function testControllerExtendsJsonController(): void
    {
        $this->assertInstanceOf(
            \Bitrix\Main\Engine\JsonController::class,
            new SurveyController()
        );
    }

    public function testControllerHasGetSurveyAction(): void
    {
        $controller = new SurveyController();

        $configuration = $controller->getConfigurationOfActions();

        $this->assertArrayHasKey(
            'getSurvey',
            $configuration
        );
    }

}