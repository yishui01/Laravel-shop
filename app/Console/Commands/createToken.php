<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
class createToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shop:createToken';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'generate a one-year token';

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
        $user_id = $this->ask('please entry user_id to generate the token');
        $user = User::find($user_id);

        if (!$user) {
            return $this->ask('user does not exist');
        }

        //生成一个一年的TOKEN
        $ttl = 365*24*60;
        $this->info(Auth::guard('api')->setTTL($ttl)->fromUser($user));
    }
}
