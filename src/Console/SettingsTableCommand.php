<?php namespace Hongyukeji\LaravelSetting\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Composer;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class SettingsTableCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'settings:table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a migration for the settings database table';

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * @var \Illuminate\Support\Composer
     */
    protected $composer;

    /**
     * Create a new settings table command instance.
     *
     * @param \Illuminate\Filesystem\Filesystem $files
     * @param \Illuminate\Support\Composer $composer
     */
    public function __construct(Filesystem $files, Composer $composer)
    {
        parent::__construct();

        $this->files = $files;
        $this->composer = $composer;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $table = config('setting.table');

        $fullPath = $this->createBaseMigration($table);

        //$this->files->put($fullPath, $this->files->get(__DIR__ . '/stubs/settings.stub'));
        $this->files->put($fullPath, $this->buildParent($table));

        $this->info('Migration created successfully!');

        $this->composer->dumpAutoloads();
    }

    /**
     * Create a base migration file for the table.
     *
     * @param $table
     * @return string
     */
    protected function createBaseMigration($table)
    {

        $name = "create_" . Str::plural($table) . "_table";

        $path = $this->laravel->databasePath() . '/migrations';

        return $this->laravel['migration.creator']->create($name, $path);
    }

    /**
     * Replace content
     *
     * @param $table
     * @return string|string[]
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function buildParent($table)
    {
        $stub = $this->files->get($this->getStub());
        $stub = str_replace(
            ['SettingClass', 'setting_table'],
            [Str::studly($table), Str::plural($table)],
            $stub
        );
        return $stub;
    }

    protected function getStub()
    {
        $stub = $stub ?? '/stubs/settings.stub';
        return __DIR__ . $stub;
    }
}
