<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

class ListBroadcastChannels extends Command
{
    protected $signature = 'channels:list';
    protected $description = 'List all registered broadcast channels';

    public function handle()
    {
        $channelsFile = base_path('routes/channels.php');

        if (file_exists($channelsFile)) {
            $this->info('Registered Broadcast Channels:');
            $channelsContent = file_get_contents($channelsFile);
            $this->line($channelsContent);
        } else {
            $this->error('No channels.php file found.');
        }

        return Command::SUCCESS;
    }
}