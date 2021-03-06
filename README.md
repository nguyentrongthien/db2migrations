# Database to Eloquent Migrations

A very simple package that generate migration files from a given database. 

## Features

- Create migrations for tables that don't already have one.
- Read column's properties and write appropriate Eloquent statements.
- Option to scan only for tables with a given prefix.
- Add newly generated migrations into migrations table with the latest batch number.

## Possible Limitations

This package is written and tested only against MySQL database for the time being.
This might or might not work with other database systems like Postgres.
There might be a problem if the db user provided is too restricted because this package also scan
information_schema table for column's properties.

## Field Types

These are the field types that this package can handle currently:

- Integer
- Big Integer
- Double
- String
- Text
- Date Time (needs more in-dept logic)
- Timestamp (needs more in-dept logic)
