<?php

namespace App\Jobs;

use App\Models\ClickUpTask;
use App\Services\ClickUpService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FetchClickupTasks implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $listId;
    /**
     * Create a new job instance.
     */
    public function __construct($listId)
    {
        $this->listId = $listId;
    }

    /**
     * Execute the job.
     */
    public function handle(ClickUpService $clickUpService)
    {
        try {
            $tasks = $clickUpService->getTasks($this->listId);

            foreach ($tasks['tasks'] as $task) {
                ClickUpTask::updateOrCreate(
                    ['task_id' => $task['id']], 
                    [
                        'name' => $task['name'],
                        'description' => $task['description'] ?? '',
                        'status' => $task['status']['status'],
                        'creator' => $task['creator']['username'] ?? '',
                    ]
                );
            }

            Log::info('Fetched and stored ClickUp tasks successfully');
        } catch (\Exception $e) {
            Log::error('Failed to fetch ClickUp tasks', ['error' => $e->getMessage()]);
        }
    }
}