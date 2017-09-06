<?php

namespace App\Console\Commands;

use App\Jobs\GetFeedsUserSocial;
use Illuminate\Console\Command;

class UpdateFeedSocial extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */

    protected $signature = 'update:feeds {type}';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Feeds Social Users';

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
        $social = $this->argument('type');

        dispatch(new GetFeedsUserSocial($social));
        exit();
        //dispatch((new GetFeedsUserSocial($environment))->onQueue('high'));
    }

}
