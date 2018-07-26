<?php
/**
 * @author donknap
 * @date 18-7-24 下午5:31
 */

namespace W7\Http\Server;

use Psr\Http\Message\ServerRequestInterface;
use W7\App;
use W7\Core\Helper\RouteData;
use W7\Core\Base\DispatcherAbstract;
use W7\Core\Base\MiddlewareHandler;
use W7\Core\Helper\Context;
use W7\Core\Helper\Middleware;
use w7\HttpRoute\HttpServer;

class Dispather extends DispatcherAbstract {

	public $lastMiddleware = \W7\Http\Middleware\RequestMiddleware::class;



	public function dispatch(...$params) {
		list($request, $response) = $params;

		$psr7Request = \w7\Http\Message\Server\Request::loadFromSwooleRequest($request);
        $psr7Response = new \w7\Http\Message\Server\Response($response);

		Context::setRequest($psr7Request);
        Context::setResponse($psr7Response);

        //根据router配置，获取到匹配的controller信息

		//获取到全部中间件数据，最后附加Http组件的特定的last中间件，用于处理调用Controller
        /**
         * @var Middleware $middlewarehelper
         */

        $middlewarehelper = iloader()->singleton(Middleware::class);
        $middlewarehelper->insertMiddlewareCached();
        $middlewarehelper->setLastMiddleware($this->lastMiddleware);
        $middlewares = Context::getContextDataByKey(Middleware::MIDDLEWARE_MEMORY_TABLE_NAME);
        $middlewareHandler = new MiddlewareHandler($middlewares);
        try {
            $response = $middlewareHandler->handle($psr7Request);
        }catch (\Throwable $throwable){
            $response = Context::getResponse()->json($throwable->getMessage(), $throwable->getCode());
        }

        $response->send();
	}

}