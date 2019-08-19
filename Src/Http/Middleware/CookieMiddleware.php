<?php

/**
 * WeEngine Api System
 *
 * (c) We7Team 2019 <https://www.w7.cc>
 *
 * This is not a free software
 * Using it under the license terms
 * visited https://www.w7.cc for more details
 */

namespace W7\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use W7\Core\Middleware\MiddlewareAbstract;

class CookieMiddleware extends MiddlewareAbstract {
	public function __construct() {
		$this->initCookieEnv();
	}

	private function initCookieEnv() {
		$config = iconfig()->getUserAppConfig('cookie');

		if (isset($config['http_only'])) {
			ini_set('session.cookie_httponly', $config['http_only']);
		}
		if (isset($config['path'])) {
			ini_set('session.cookie_path', $config['path']);
		}
		if (isset($config['domain'])) {
			ini_set('session.cookie_domain', $config['domain']);
		}
		if (isset($config['secure'])) {
			ini_set('session.cookie_secure', $config['secure']);
		}
		if (!isset($config['expires'])) {
			$config['expires'] = ini_get('session.gc_maxlifetime');
		}
		ini_set('session.cookie_lifetime', (int)$config['expires']);
	}

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
		return $handler->handle($request);
	}
}