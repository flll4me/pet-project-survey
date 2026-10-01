<?php

use PHPUnit\Framework\TestCase;
use Bitrix\Main\Application;
use Questionnaire\Survey\SurveyController;
use Bitrix\Main\HttpRequest;

final class SurveyRouteTest extends TestCase
{
    public function testSurveyRouteExists(): void
    {
        $router = Application::getInstance()->getRouter();
        $routes = $router->getRoutes();

        $route = $routes[0];

        $this->assertSame('/api/surveys/{surveyId}', $route->getUri());
        $this->assertSame(
            [
                SurveyController::class,
                'getSurvey',
            ],
            $route->getController()
        );
    }

    public function testSurveyRouteAllowsGet(): void
    {
        $router = Application::getInstance()->getRouter();
        $routes = $router->getRoutes();

        $options = $routes[0]->getOptions();

        $this->assertContains('GET', $options->getMethods());
    }

    public function testSurveyRouteMatchesGetRequest(): void
    {
        $router = Application::getInstance()->getRouter();

        $server = new \Bitrix\Main\Server([
            'REQUEST_URI' => '/api/surveys/123',
            'REQUEST_METHOD' => 'GET',
        ]);

        $request = new HttpRequest(
            $server,
            [],
            [],
            [],
            []
        );

        $route = $router->match($request);

        $this->assertNotNull($route);
        $this->assertSame('123', $route->getParameterValue('surveyId'));
    }

    public function testSurveyRouteDoesNotMatchPostRequest(): void
    {
        $router = Application::getInstance()->getRouter();

        $server = new \Bitrix\Main\Server([
            'REQUEST_URI' => '/api/surveys/123',
            'REQUEST_METHOD' => 'POST',
        ]);

        $request = new HttpRequest(
            $server,
            [],
            [],
            [],
            []
        );

        $route = $router->match($request);

        $this->assertNull($route);
    }

}