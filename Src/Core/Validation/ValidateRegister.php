<?php

namespace W7\Core\Validation;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\Translator;
use Illuminate\Validation\DatabasePresenceVerifier;
use Illuminate\Validation\Factory;
use W7\Core\Service\ServiceAbstract;

class ValidateRegister extends ServiceAbstract {
	public function register() {
		iloader()->set('translator', function () {
			return new Translator($this->getFileLoader(), 'zh-CN');
		});
		iloader()->set('validator', function () {
			$factory = new Factory(iloader()->get('translator'));
			$factory->setPresenceVerifier(new DatabasePresenceVerifier(idb()));

			return $factory;
		});
	}
	
	private function getFileLoader() {
		$paths = [
			BASE_PATH . '/vendor/caouecs/laravel-lang/src/',
			BASE_PATH . '/app/config/lang/',
		];
		$loader = new FileLoader(new Filesystem(), '', $paths);
		if (\is_callable([$loader, 'addJsonPath'])) {
			$loader->addJsonPath(BASE_PATH . '/vendor/caouecs/laravel-lang/json/');
		}
		return $loader;
	}
}