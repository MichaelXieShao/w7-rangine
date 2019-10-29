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

namespace W7\Core\Database\Command\Migrate;

use Illuminate\Filesystem\Filesystem;
use W7\Core\Database\Migrate\DatabaseMigrationRepository;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Input\InputOption;
use W7\Core\Database\Migrate\Migrator;

class StatusCommand extends MigrateCommandAbstract {
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'migrate:status';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Show the status of each migration';

	/**
	 * The migrator instance.
	 *
	 * @var \Illuminate\Database\Migrations\Migrator
	 */
	protected $migrator;

	/**
	 * Create a new migration rollback command instance.
	 *
	 * @param  \Illuminate\Database\Migrations\Migrator $migrator
	 * @return void
	 */
	public function __construct(string $name = null) {
		parent::__construct($name);
		$this->migrator = new Migrator(new DatabaseMigrationRepository(idb(), 'migration'), idb(), new Filesystem());
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	protected function handle($options) {
		$this->migrator->setConnection($this->option('database'));

		if (! $this->migrator->repositoryExists()) {
			return $this->output->error('Migration table not found.');
		}

		$ran = $this->migrator->getRepository()->getRan();

		$batches = $this->migrator->getRepository()->getMigrationBatches();

		if (count($migrations = $this->getStatusFor($ran, $batches)) > 0) {
			$this->output->table(['Ran?', 'Migration', 'Batch'], $migrations);
		} else {
			$this->output->error('No migrations found');
		}
	}

	/**
	 * Get the status for the given ran migrations.
	 *
	 * @param  array  $ran
	 * @param  array  $batches
	 * @return \Illuminate\Support\Collection
	 */
	protected function getStatusFor(array $ran, array $batches) {
		return Collection::make($this->getAllMigrationFiles())
					->map(function ($migration) use ($ran, $batches) {
						$migrationName = $this->migrator->getMigrationName($migration);

						return in_array($migrationName, $ran)
								? ['<info>Yes</info>', $migrationName, $batches[$migrationName]]
								: ['<fg=red>No</fg=red>', $migrationName];
					});
	}

	/**
	 * Get an array of all of the migration files.
	 *
	 * @return array
	 */
	protected function getAllMigrationFiles() {
		return $this->migrator->getMigrationFiles($this->getMigrationPaths());
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions() {
		return [
			['database', null, InputOption::VALUE_OPTIONAL, 'The database connection to use'],

			['path', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'The path(s) to the migrations files to use'],

			['realpath', null, InputOption::VALUE_NONE, 'Indicate any provided migration file paths are pre-resolved absolute paths'],
		];
	}
}
