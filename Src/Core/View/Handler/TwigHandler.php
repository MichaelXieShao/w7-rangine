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

namespace W7\Core\View\Handler;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;
use W7\App;

class TwigHandler extends HandlerAbstract {
	/**
	 * @var Environment
	 */
	private static $twig;

	protected function init() {
		if (self::$twig) {
			return true;
		}

		$loader = new FilesystemLoader(self::$templatePath);
		self::$twig = new Environment($loader);
		$this->addFunction();
	}

	private function addFunction() {
		self::$twig->addFunction(new TwigFunction('irandom', function () {
			return irandom(...func_get_args());
		}));
		self::$twig->addFunction(new TwigFunction('irequest', function () {
			return App::getApp()->getContext()->getRequest();
		}));
		self::$twig->addFunction(new TwigFunction('icache', function () {
			return icache();
		}));
		self::$twig->addFunction(new TwigFunction('getClientIp', function () {
			return getClientIp();
		}));
		self::$twig->addFunction(new TwigFunction('ienv', function () {
			return ienv(...func_get_args());
		}));
		self::$twig->addFunction(new TwigFunction('idd', function () {
			return idd(...func_get_args());
		}));
	}

	public function render($name, $context = []) : string {
		return self::$twig->render($name, $context);
	}
}