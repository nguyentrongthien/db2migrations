<?php

namespace Laravel\MigrationFromDatabase;

use Illuminate\Support\Facades\DB;

class MigrationFieldGenerator
{

    /**
     * Generate migration fields from a given table.
     *
     * @param string $tableName
     * @param string $dbName
     *
     * @return string
     */
    public function generateFromTable(string $tableName, string $dbName): string
    {
        $columns = $this->getColumns($tableName, $dbName);

        $migrationFields = "";

        foreach ($columns as $column) {
            $migrationFields .= $this->writeMigrationField(json_decode(json_encode($column), true));
        }

        return $migrationFields;
    }

    /**
     * Get columns and their properties from a given table of a database.
     *
     * @param string $tableName
     * @param string $dbName
     *
     * @return array
     */
    protected function getColumns(string $tableName, string $dbName): array
    {
        return DB::select ("SELECT * FROM `information_schema`.`COLUMNS`
            WHERE TABLE_SCHEMA = '{$dbName}' AND TABLE_NAME = '{$tableName}' ORDER BY `ORDINAL_POSITION`;");
    }

    /**
     * Translate column's properties into equivalent Eloquent syntax.
     *
     * @param $column
     *
     * @return string
     */
    protected function writeMigrationField($column): string
    {
        $migrationField = '            $table';

        if(strpos(strtolower($column['COLUMN_TYPE']), 'bigint') !== false)
            $migrationField .= "->bigInteger('{$column['COLUMN_NAME']}')";
        elseif(strpos(strtolower($column['COLUMN_TYPE']), 'tinyint') !== false)
            $migrationField .= "->boolean('{$column['COLUMN_NAME']}')";
        elseif(strpos(strtolower($column['COLUMN_TYPE']), 'int') !== false)
            $migrationField .= "->integer('{$column['COLUMN_NAME']}')";

        if(strpos(strtolower($column['COLUMN_TYPE']), 'text') !== false)
            $migrationField .= "->text('{$column['COLUMN_NAME']}')";

        if(strpos(strtolower($column['COLUMN_TYPE']), 'timestamp') !== false)
            $migrationField .= "->timestamp('{$column['COLUMN_NAME']}')";

        if(strpos(strtolower($column['COLUMN_TYPE']), 'datetime') !== false)
            $migrationField .= "->dateTime('{$column['COLUMN_NAME']}')";

        if(strpos(strtolower($column['COLUMN_TYPE']), 'double') !== false)
            $migrationField .= "->double('{$column['COLUMN_NAME']}')";

        if(strpos(strtolower($column['COLUMN_TYPE']), 'varchar') !== false)
            $migrationField .= "->string('{$column['COLUMN_NAME']}')";

        if(strpos(strtolower($column['COLUMN_TYPE']), 'unsigned') !== false)
            $migrationField .= "->unsigned()";

        if(strpos(strtolower($column['EXTRA']), 'auto_increment') !== false)
            $migrationField .= "->autoIncrement()";

        if(strpos(strtolower($column['COLUMN_KEY']), 'pri') !== false)
            $migrationField .= "->primary()";

        if(strtolower($column['IS_NULLABLE']) === 'yes')
            $migrationField .= "->nullable()";
        elseif($column['COLUMN_DEFAULT'])
            $migrationField .= is_numeric($column['COLUMN_DEFAULT']) ?
                "->default({$column['COLUMN_DEFAULT']})" :
                "->default('{$column['COLUMN_DEFAULT']}')";

        return $migrationField . ";\r\n";
    }
}
