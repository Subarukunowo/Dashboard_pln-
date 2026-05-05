<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class CacheUserAvatars extends Command
{
    protected $signature = 'avatars:cache';
    protected $description = 'Download and cache user avatars locally';

    public function handle()
    {
        User::whereNotNull('avatar')
            ->where('avatar', 'like', 'https://lh3.googleusercontent.com%')
            ->chunk(50, function ($users) {
                foreach ($users as $user) {
                    try {
                        $response = Http::timeout(10)->get($user->avatar);
                        
                        if ($response->successful()) {
                            $filename = 'avatars/' . $user->id . '_' . time() . '.jpg';
                            Storage::disk('public')->put($filename, $response->body());
                            
                            $user->update([
                                'avatar' => Storage::url($filename)
                            ]);
                            
                            $this->info("Cached avatar for user {$user->id}");
                        }
                    } catch (\Exception $e) {
                        $this->error("Failed to cache avatar for user {$user->id}: {$e->getMessage()}");
                    }
                }
            });
            
        return 0;
    }
}