<?php

namespace Laravel\MigrationFromDatabase\Console\Commands;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Console\Migrations\BaseCommand;
use Illuminate\Support\Facades\DB;
use Laravel\MigrationFromDatabase\MigrationGenerator;

class ConvertToMigrations extends BaseCommand {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:convert
        {--path= : The location where the migration file should be created}
        {--realpath : Indicate any provided migration file paths are pre-resolved absolute paths}';

    /**
     * The console command description.
     *
     * @var string|null
     */
    protected $description = 'Generate migrations for tables that do not have any.';

    protected $migrator, $excludedTables, $generator;

    /**
     * Create a new migration rollback command instance.
     *
     * @param MigrationGenerator $generator
     * @return void
     */
    public function __construct(MigrationGenerator $generator)
    {
        parent::__construct();

        $this->migrator = app("migrator");
        $this->excludedTables = ['migrations'];
        $this->generator = $generator;
    }

    /**
     * Run the command.
     *
     * @return void
     * @throws FileNotFoundException
     */
    public function handle()
    {
        if (! $this->migrator->repositoryExists()) {
            $this->error('Migration table not found.');
            return;
        }

        $this->excludeExistingMigrations();

        $tables = DB::connection()->getDoctrineSchemaManager()->listTableNames();

        $count = 0;
        foreach ($tables as $table) {
            if(!in_array($table, $this->excludedTables)) {
                $this->writeMigration($table);
                $count++;
            }
            if($count == 1) break;
        }
    }

    /**
     * Read through available migration files and add their respective tables to excluded list.
     *
     * @return void
     */
    protected function excludeExistingMigrations()
    {
        $migrationFiles = $this->migrator->getMigrationFiles($this->getMigrationPaths());

        foreach ($migrationFiles as $filename=>$filepath) {
            $lines = file($filepath);
            foreach ($lines as $line) {
                if (strpos($line, 'Schema::create') !== false) {
                    $arr = explode('\'', $line);
                    if(sizeof($arr) >= 2) {
                        $this->info($arr[1]);
                        $this->excludedTables[] = $arr[1];
                        break;
                    }
                }
            }
        }
    }

    /**
     * Write the migration file to disk.
     *
     * @param string $tableName
     * @return void
     * @throws FileNotFoundException
     */
    protected function writeMigration(string $tableName)
    {
        $file = pathinfo($this->generator->create(
            $tableName, $this->getMigrationPath()), PATHINFO_FILENAME);

        $this->line("<info>Created Migration:</info> {$file}");
    }

    /**
     * Get migration path (either specified by '--path' option or default location).
     *
     * @return string
     */
    protected function getMigrationPath(): string
    {
        if (! is_null($targetPath = $this->input->getOption('path'))) {
            return ! $this->usingRealPath()
                ? $this->laravel->basePath().'/'.$targetPath
                : $targetPath;
        }

        return parent::getMigrationPath();
    }
}
