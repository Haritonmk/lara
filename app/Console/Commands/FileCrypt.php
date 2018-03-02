<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class FileCrypt extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'file:crypt {path : File path}';
    // example: php artisan file:crypt filename.txt
    // file path: storage/app/

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crypt file';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $path = $this->argument('path');
        Storage::disk('local')->put($path, encrypt(Storage::get($path)));
        $this->info('This Work. File: '.$path);
    }
}
