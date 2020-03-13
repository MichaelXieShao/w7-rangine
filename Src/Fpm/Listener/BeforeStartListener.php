<?php

/**
 * This file is part of Rangine
 *
 * (c) We7Team 2019 <https://www.rangine.com/>
 *
 * document http://s.w7.cc/index.php?c=wiki&do=view&id=317&list=2284
 *
 * visited https://www.rangine.com/ for more details
 */

namespace W7\Fpm\Listener;

use W7\Core\Listener\ListenerAbstract;
use W7\Core\Route\RouteMapping;
use FastRoute\Dispatcher\GroupCountBased;
use W7\Fpm\Server\Dispatcher;

class BeforeStartListener extends ListenerAbstract {
	public function run(...$params) {
		$this->registerRouter();
	}

	private function registerRouter() {
		/**
		 * @var Dispatcher $dispatcher
		 */
		$dispatcher = iloader()->get(Dispatcher::class);
		$routeInfo = iloader()->get(RouteMapping::class)->getMapping();
		$dispatcher->setRouter(new GroupCountBased($routeInfo));
	}
}