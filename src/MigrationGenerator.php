<?php

namespace Laravel\MigrationFromDatabase;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MigrationGenerator {

    /**
     * The filesystem instance.
     *
     * @var Filesystem
     */
    protected $files;

    /**
     * The migration field generator instance.
     *
     * @var MigrationFieldGenerator
     */
    protected $fieldGenerator;

    /**
     * Create a new migration creator instance.
     *
     * @param Filesystem $files
     * @param MigrationFieldGenerator $fieldGenerator
     *
     * @return void
     */
    public function __construct(Filesystem $files, MigrationFieldGenerator $fieldGenerator)
    {
        $this->files = $files;
        $this->fieldGenerator = $fieldGenerator;
    }

    /**
     * Get the migration stub file.
     *
     * @param string $tableName
     * @param string $path
     *
     * @return string
     * @throws FileNotFoundException
     */
    public function create(string $tableName, string $path): string
    {

        $stub = $this->getStub();

        $this->files->put(
            $path = $this->getPath($tableName, $path),
            $this->populateStub($tableName, $stub, $this->getMigrationFields($tableName))
        );

        return $path;
    }

    /**
     * Replace placeholders in the stub with appropriate strings.
     *
     * @param string $tableName
     * @param string $stub
     * @param string $migrationFields
     * @return string
     */
    protected function populateStub(string $tableName, string $stub, string $migrationFields): string
    {
        $stub = str_replace('DummyClass', $this->getClassName($tableName), $stub);

        $stub = str_replace('DummyTable', $tableName, $stub);

        return str_replace('[database_fields]', $migrationFields, $stub);
    }

    /**
     * Generate migration fields for a given table.
     *
     * @param string $tableName
     * @return string
     */
    protected function getMigrationFields(string $tableName): string
    {
        return $this->fieldGenerator->generateFromTable($tableName, $this->getCurrentDBName());
    }

    /**
     * Get the migration stub file.
     *
     * @return string
     * @throws FileNotFoundException
     */
    protected function getStub(): string
    {
        return $this->files->get($this->stubPath()."/create.stub");
    }

    /**
     * Get the path to the stubs.
     *
     * @return string
     */
    public function stubPath(): string
    {
        return __DIR__.'/stubs';
    }

    /**
     * Get the date prefix for the migration.
     *
     * @return string
     */
    protected function getDatePrefix(): string
    {
        return date('Y_m_d_His');
    }

    /**
     * Get the full path to the migration.
     *
     * @param string $tableName
     * @param string $path
     * @return string
     */
    protected function getPath(string $tableName, string $path): string
    {
        return $path.'/'.$this->getDatePrefix().'_create_'.$tableName.'_table.php';
    }

    /**
     * Get the class name of a migration name.
     *
     * @param string $name
     * @return string
     */
    protected function getClassName(string $name): string
    {
        return Str::studly('create_' . $name . '_table');
    }

    /**
     * Get the current database name.
     *
     * @return string
     */
    protected function getCurrentDBName(): string
    {
        return DB::connection()->getDatabaseName();
    }
}
