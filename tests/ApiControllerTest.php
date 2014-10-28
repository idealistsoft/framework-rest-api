<?php

use infuse\Request;
use infuse\Response;

use app\api\libs\ApiController;
use app\api\libs\ApiRoute;

class ApiControllerTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateRoute()
    {
        $api = new ApiController();
        $req = new Request();
        $res = new Response();

        $route = $api->create($req, $res, false);

        $this->assertEquals($req, $route->getRequest());
        $this->assertEquals($res, $route->getResponse());
        $this->assertEquals($api, $route->getController());
    }

    public function testFindAllRoute()
    {
        $api = new ApiController();
        $req = new Request();
        $res = new Response();

        $route = $api->findAll($req, $res, false);

        $this->assertEquals($req, $route->getRequest());
        $this->assertEquals($res, $route->getResponse());
        $this->assertEquals($api, $route->getController());
    }

    public function testFindOneRoute()
    {
        $api = new ApiController();
        $req = new Request();
        $res = new Response();

        $route = $api->findOne($req, $res, false);

        $this->assertEquals($req, $route->getRequest());
        $this->assertEquals($res, $route->getResponse());
        $this->assertEquals($api, $route->getController());
    }

    public function testEditRoute()
    {
        $api = new ApiController();
        $req = new Request();
        $res = new Response();

        $route = $api->edit($req, $res, false);

        $this->assertEquals($req, $route->getRequest());
        $this->assertEquals($res, $route->getResponse());
        $this->assertEquals($api, $route->getController());
    }

    public function testDeleteRoute()
    {
        $api = new ApiController();
        $req = new Request();
        $res = new Response();

        $route = $api->delete($req, $res, false);

        $this->assertEquals($req, $route->getRequest());
        $this->assertEquals($res, $route->getResponse());
        $this->assertEquals($api, $route->getController());
    }

    public function testParseRouteBase()
    {
        $route = new ApiRoute();
        $req = Mockery::mock('\\infuse\\Request');
        $req->shouldReceive('basePath')->andReturn('/api');
        $req->shouldReceive('path')->andReturn('/users');
        $route->setRequest($req);

        $api = new ApiController();
        $this->assertNull($api->parseRouteBase($route));
        $this->assertEquals('/api/users', $route->getQuery('route_base'));
    }

    public function testParseFetchModelFromParamsAlreadySet()
    {
        $route = new ApiRoute();
        $route->addQueryParams([
            'module' => 'test',
            'model' => 'Test']);

        $api = new ApiController();
        $this->assertTrue($api->parseFetchModelFromParams($route));
    }

    public function testParseFetchModelFromParamsNoController()
    {
        $route = new ApiRoute();

        $req = new Request();
        $req->setParams([
            'module' => 'test',
            'model' => 'Test']);
        $route->setRequest($req);

        $res = new Response();
        $route->setResponse($res);

        $api = new ApiController();
        $this->assertFalse($api->parseFetchModelFromParams($route));
        $this->assertEquals(404, $res->getCode());
    }

    public function testParseFetchModelFromParams()
    {
        $this->markTestIncomplete();
    }

    public function testParseRequireApiScaffolding()
    {
        $model = new stdClass();
        $model->scaffoldApi = true;

        $route = new ApiRoute();
        $route->addQueryParams(['model' => $model]);

        $api = new ApiController();
        $this->assertNull($api->parseRequireApiScaffolding($route));
    }

    public function testParseRequireApiScaffoldingFail()
    {
        $route = new ApiRoute();
        $route->addQueryParams(['model' => new \stdClass()]);
        $res = Mockery::mock('\\infuse\\Response');
        $res->shouldReceive('setCode')->withArgs([404])->once();
        $route->setResponse($res);

        $api = new ApiController();
        $this->assertFalse($api->parseRequireApiScaffolding($route));
    }

    public function testParseRequireJson()
    {
        $route = new ApiRoute();
        $req = Mockery::mock('\\infuse\\Request');
        $req->shouldReceive('isJson')->andReturn(true);
        $route->setRequest($req);

        $api = new ApiController();
        $this->assertNull($api->parseRequireJson($route));
    }

    public function testParseRequireJsonFail()
    {
        $route = new ApiRoute();
        $req = Mockery::mock('\\infuse\\Request');
        $req->shouldReceive('isJson')->andReturn(false);
        $route->setRequest($req);
        $res = Mockery::mock('\\infuse\\Response');
        $res->shouldReceive('setCode')->withArgs([415])->once();
        $route->setResponse($res);

        $api = new ApiController();
        $this->assertFalse($api->parseRequireJson($route));
    }

    public function testParseRequireFindPermission()
    {
        $this->markTestIncomplete();

        $model = Mockery::mock();

        $route = new ApiRoute();
        $route->addQueryParams(['model' => $model]);
    }

    public function testParseRequireViewPermission()
    {
        $this->markTestIncomplete();
    }

    public function testParseRequireCreatePermission()
    {
        $this->markTestIncomplete();
    }

    public function testParseModelCreateParameters()
    {
        $route = new ApiRoute();

        $test = ['test' => 'hello'];
        $req = new Request(['expand' => 'invoice'], $test);
        $route->setRequest($req);

        $api = new ApiController();
        $this->assertNull($api->parseModelCreateParameters($route));

        $this->assertEquals($test, $route->getQuery('properties'));
        $this->assertEquals(['invoice'], $route->getQuery('expand'));
    }

    public function testParseModelFindAllParameters()
    {
        $route = new ApiRoute();

        $req = new Request([
            'start' => 10,
            'limit' => 90,
            'sort' => 'name ASC',
            'search' => 'test',
            'filter' => [
                'name' => 'john',
                'year' => 2012,
                // the elements below are invalid and should be removed
                'test',
                'OR1=1' => 'whatever',
                'test' => ['test'],
                'test2' => new stdClass() ],
            'expand' => [
                'customer.address',
                'invoice' ]]);
        $route->setRequest($req);

        $api = new ApiController();
        $this->assertNull($api->parseModelFindAllParameters($route));

        $expected = [
            'start' => 10,
            'limit' => 90,
            'sort' => 'name ASC',
            'search' => 'test',
            'where' => [
                'name' => 'john',
                'year' => 2012 ],
            'expand' => [
                'customer.address',
                'invoice' ]];
        $this->assertEquals($expected, $route->getQuery());
    }

    public function testParseModelFindOneParameters()
    {
        $route = new ApiRoute();

        $req = new Request();
        $req->setParams(['id' => 101]);
        $route->setRequest($req);

        $api = new ApiController();
        $this->assertNull($api->parseModelFindOneParameters($route));

        $expected = [
            'model_id' => 101,
            'expand' => [],
            'include' => []];
        $this->assertEquals($expected, $route->getQuery());
    }

    public function testParseModelEditParameters()
    {
        $route = new ApiRoute();

        $test = [ 'test' => 'hello' ];
        $req = new Request( null, $test );
        $req->setParams( [ 'id' => 101 ] );
        $route->setRequest($req);

        $api = new ApiController();
        $this->assertNull($api->parseModelEditParameters($route));
        $this->assertEquals( 101, $route->getQuery('model_id'));
        $this->assertEquals( $test, $route->getQuery('properties'));
    }

    public function testParseModelDeleteParameters()
    {
        $route = new ApiRoute();

        $req = new Request();
        $req->setParams( [ 'id' => 102 ] );
        $route->setRequest($req);

        $api = new ApiController();
        $this->assertNull($api->parseModelDeleteParameters($route));
        $this->assertEquals( 102, $route->getQuery('model_id'));
    }

    public function testQueryModelCreate()
    {
        $this->markTestIncomplete();
    }

    public function testQueryModelFindAll()
    {
        $this->markTestIncomplete();
    }

    public function testQueryModelFindOne()
    {
        $this->markTestIncomplete();
    }

    public function testQueryModelEdit()
    {
        $this->markTestIncomplete();
    }

    public function testQueryModelDelete()
    {
        $this->markTestIncomplete();
    }

    public function testTransformModelCreate()
    {
        $this->markTestIncomplete();
    }

    public function testTransformFindAll()
    {
        $this->markTestIncomplete();
    }

    public function testTransformPaginate()
    {
        $this->markTestIncomplete();
    }

    public function testTransformModelFindOne()
    {
        $this->markTestIncomplete();
    }

    public function testTransformModelEdit()
    {
        $route = new ApiRoute();

        $result = true;

        $api = new ApiController();
        $api->transformModelEdit($result, $route);

        $expected = new stdClass();
        $expected->success = true;

        $this->assertEquals($expected, $result);
    }

    public function testTransformModelEditFail()
    {
        $route = new ApiRoute();
        $res = Mockery::mock('\\infuse\\Response');
        $res->shouldReceive('setCode')->withArgs([403])->once();
        $route->setResponse($res);

        $result = false;

        $api = new ApiController();

        $app = new App();
        $errors = Mockery::mock();
        $errors->shouldReceive('messages')->andReturn(['error_message_1','error_message_2']);
        $errors->shouldReceive('errors')->andReturn([['error'=>'no_permission']]);
        $app['errors'] = $errors;
        $api->injectApp($app);

        $api->transformModelEdit($result, $route);

        $expected = new stdClass();
        $expected->error = ['error_message_1','error_message_2'];

        $this->assertEquals( $expected, $result );
    }

    public function testTransformModelDelete()
    {
        $route = new ApiRoute();

        $result = true;

        $api = new ApiController();
        $api->transformModelDelete($result, $route);

        $expected = new stdClass();
        $expected->success = true;

        $this->assertEquals( $expected, $result );
    }

    public function testTransformModelDeleteFail()
    {
        $route = new ApiRoute();
        $res = Mockery::mock('\\infuse\\Response');
        $res->shouldReceive('setCode')->withArgs([403])->once();
        $route->setResponse($res);

        $result = false;

        $api = new ApiController();

        $app = new App();
        $errors = Mockery::mock();
        $errors->shouldReceive('messages')->andReturn(['error_message_1','error_message_2']);
        $errors->shouldReceive('errors')->andReturn([['error'=>'no_permission']]);
        $app['errors'] = $errors;
        $api->injectApp($app);

        $api->transformModelDelete($result, $route);

        $expected = new stdClass();
        $expected->error = ['error_message_1','error_message_2'];

        $this->assertEquals( $expected, $result );
    }

    public function testTansformOutputJson()
    {
        $route = new ApiRoute();

        $res = new Response();
        $route->setResponse($res);

        $result = new stdClass();
        $result->answer = 42;

        $api = new ApiController();
        $api->transformOutputJson($result, $route);

        $this->assertEquals('{"answer":42}', $res->getBody());
    }
}