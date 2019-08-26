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

namespace W7\Console;

use Symfony\Component\Console\Application as SymfontApplication;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class Application extends SymfontApplication {
	public function __construct() {
		$version = $this->version();
		parent::__construct('w7swoole', $version);

		$this->setAutoExit(false);
		$this->registerCommands();
	}

	/**
	 * Gets the default input definition.
	 *
	 * @return InputDefinition An InputDefinition instance
	 */
	protected function getDefaultInputDefinition() {
		return new InputDefinition([
			new InputArgument('command', InputArgument::REQUIRED, 'The command to execute'),

			new InputOption('--help', '-h', InputOption::VALUE_NONE, 'Display this help message'),
			new InputOption('--quiet', '-q', InputOption::VALUE_NONE, 'Do not output any message'),
			new InputOption('--version', '-v', InputOption::VALUE_NONE, 'Display this application version'),
			new InputOption('--ansi', '', InputOption::VALUE_NONE, 'Force ANSI output'),
			new InputOption('--no-ansi', '', InputOption::VALUE_NONE, 'Disable ANSI output'),
			new InputOption('--no-interaction', '-n', InputOption::VALUE_NONE, 'Do not ask any interactive question'),
		]);
	}

	public function run(InputInterface $input = null, OutputInterface $output = null) {
		$output = ioutputer();
		return parent::run($input, $output); // TODO: Change the autogenerated stub
	}

	public function doRun(InputInterface $input, OutputInterface $output) {
		if (true === $input->hasParameterOption(['--version', '-v'], true)) {
			$output->writeln($this->logo());
			$output->writeln($this->getLongVersion());
			return 0;
		}

		if (!$this->checkCommand($input)) {
			$output->writeln($this->logo());
			$input = new ArgvInput(['command' => 'list']);
		} elseif (true === $input->hasParameterOption(['--help', '-h'], true)) {
			$output->writeln($this->logo());
		}

		try {
			return parent::doRun($input, $output);
		} catch (\Throwable $e) {
			if ($e instanceof \Error) {
				$e = new RuntimeException('message: ' . $e->getMessage() . "\nfile: " . $e->getFile() . "\nline: " . $e->getLine(), $e->getCode());
			}
			$this->renderException($e, $output);
		}
	}

	private function registerCommands() {
		$this->autoRegisterCommands(RANGINE_FRAMEWORK_PATH  . '/Console/Command', '\\W7\\Console', 'rangine');
		$this->autoRegisterCommands(APP_PATH  . '/Command', '\\W7\\App', 'app');
	}

	public function autoRegisterCommands($path, $namespace, $group) {
		$commands = $this->findCommands($path, $namespace, $group);
		foreach ($commands as $name => $class) {
			$commandObj = new $class($name);
			$this->add($commandObj);
		}
	}

	private function findCommands($path, $namespace, $group) {
		$commands = [];

		$files = Finder::create()
			->in($path)
			->files()
			->ignoreDotFiles(true)
			->name('/^[\w\W\d]+Command.php$/');

		/**
		 * @var SplFileInfo $file
		 */
		foreach ($files as $file) {
			$dir = trim(str_replace([$path, '/'], ['', '\\'], $file->getPath()), '\\');
			//如果command没有组,默认属于$group下
			$parent = str_replace('\\', ':', $dir == '' ? $group : $dir);
			$name = strtolower($parent . ':' . $file->getBasename('Command.php'));

			$commands[$name] = $namespace . '\\Command\\' . ($dir !== '' ? $dir . '\\' : '') . $file->getBasename('.php');
		}

		return $commands;
	}

	private function checkCommand($input) {
		$command = $this->getCommandName($input);
		if ($this->has($command) && strpos($command, ':') !== false) {
			return true;
		}
		return false;
	}

	private function logo() {
		return "
__      _______ _______                   _      
\ \    / /  ___  / ___|_      _____   ___ | | ___ 
 \ \ /\ / /   / /\___ \ \ /\ / / _ \ / _ \| |/ _ \
  \ V  V /   / /  ___) \ V  V / (_) | (_) | |  __/
   \_/\_/   /_/  |____/ \_/\_/ \___/ \___/|_|\___|
";
	}

	private function version() {
		$frameworkVersion = \iconfig()::VERSION;
		$phpVersion = PHP_VERSION;
		$swooleVersion = SWOOLE_VERSION;
		$version = "framework: $frameworkVersion, php: $phpVersion, swoole: $swooleVersion";

		return $version;
	}
}
