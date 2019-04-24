<?php

namespace W7\Core\Dispatcher;

use W7\App;
use FastRoute\Dispatcher;
use Psr\Http\Message\ServerRequestInterface;
use W7\Core\Exception\HttpException;
use W7\Core\Helper\Storage\Context;
use W7\Core\Middleware\MiddlewareHandler;

class RequestDispatcher extends DispatcherAbstract {
	public function dispatch(...$params) {
		$psr7Request = $params[0];
		$psr7Response = $params[1];
		$serverContext = App::$server->server->context;

		$contextObj = App::getApp()->getContext();
		$contextObj->setRequest($psr7Request);
		$contextObj->setResponse($psr7Response);

		try {
			//根据router配置，获取到匹配的controller信息
			//获取到全部中间件数据，最后附加Http组件的特定的last中间件，用于处理调用Controller
			$route = $this->getRoute($psr7Request, $serverContext[Context::ROUTE_KEY]);
			$psr7Request = $psr7Request->withAddedHeader("route", json_encode($route));

			$middlewares = $this->getMiddleware($serverContext[Context::MIDDLEWARE_KEY], $route['controller'], $route['method']);
			$requestLogContextData  = $this->getRequestLogContextData($route['controller'], $route['method']);
			$contextObj->setContextDataByKey(Context::LOG_REQUEST_KEY, $requestLogContextData);

			$middlewareHandler = new MiddlewareHandler($middlewares);
			$response = $middlewareHandler->handle($psr7Request);

		} catch (\Throwable $throwable) {
			$errorMessage = sprintf('Uncaught Exception %s: "%s" at %s line %s',
				get_class($throwable),
				$throwable->getMessage(),
				$throwable->getFile(),
				$throwable->getLine()
			);
			ilogger()->error($errorMessage, array('exception' => $throwable));

			$setting = iconfig()->getUserAppConfig('setting');
			if ($throwable instanceof HttpException) {
				$code = $throwable->getCode() ? $throwable->getCode() : '400';
				$message = $throwable->getMessage();
			} elseif (!empty($setting['development'])) {
				$message = $errorMessage;
				$code = '400';
			} else {
				$message = '服务内部错误';
				$code = '500';
			}
			$response = $contextObj->getResponse()->json(['error' => $message], $code);
		}
		return $response;
	}


	private function getRoute(ServerRequestInterface $request, $fastRoute) {
		$httpMethod = $request->getMethod();
		$url = $request->getUri()->getPath();

		$route = $fastRoute->dispatch($httpMethod, $url);

		if ($route[0] == Dispatcher::NOT_FOUND && strpos($url, '/index') === false) {
			//如果未找到，加上默认方法名再试一次
			$url = rtrim($url, '/') . '/index';
			$route = $fastRoute->dispatch($httpMethod, $url);
		}

		switch ($route[0]) {
			case Dispatcher::NOT_FOUND:
				throw new HttpException('Route not found', 404);
				break;
			case Dispatcher::METHOD_NOT_ALLOWED:
				throw new HttpException('Route not allowed', 405);
				break;
			case Dispatcher::FOUND:
				$controller = $method = '';
				list($controller, $method) = $route[1];
				break;
		}

		return [
			"method" => $method,
			'controller' => $controller,
			'args' => $route[2],
		];
	}

	private function getMiddleware($allMiddleware, $controller, $action) {
		$result = $allMiddleware[$controller . '@' . $action] ?? [];
		array_push($result, $allMiddleware['last']);
		return $result;
	}

	private function getRequestLogContextData($controller, $method) {
		$contextData = [
			'controller' => $controller,
			'method' => $method,
			'requestTime' => microtime(true),
		];
		return $contextData;
	}
}