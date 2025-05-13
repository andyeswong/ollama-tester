<?php

namespace App\Events;

use App\Models\OllamaModelTest;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TestStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $test;
    public $status;
    public $responseTime;
    public $serverId;
    public $testGroupId;

    /**
     * Create a new event instance.
     */
    public function __construct(OllamaModelTest $test, string $status)
    {
        $this->test = $test;
        $this->status = $status;
        $this->responseTime = $test->response_time;
        $this->serverId = $test->ollama_server_id;
        $this->testGroupId = $test->metadata['test_group'] ?? null;
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->test->id,
            'status' => $this->status,
            'response_time' => $this->responseTime,
            'updated_at' => $this->test->updated_at->toIso8601String(),
            'test_group' => $this->testGroupId,
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('server.' . $this->serverId),
            new Channel('test-group.' . $this->testGroupId),
        ];
    }
} 