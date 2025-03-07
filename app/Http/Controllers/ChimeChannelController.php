<?php

namespace App\Http\Controllers;

use App\Chime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;

class ChimeChannelController extends Controller
{
    public function show(Request $request, Chime $chime, $channelName)
    {
        abort_unless(Auth::user()->canEditChime($chime->id), 403);

        $validChannelNames = [
            'session-status',
        ];

        abort_unless(in_array($channelName, $validChannelNames), 404);

        $redisEntry = "presence-{$channelName}.{$chime->id}:members";
        $membersJson = Redis::get($redisEntry);

        if (!$membersJson) {
            return response()->json([
                'chime_id' => $chime->id,
                'channel_name' => $channelName,
                'user_ids' => [],
                'user_count' => 0,
            ]);
        }

        $members = json_decode($membersJson, true);

        $uniqUserIds = collect($members)
            ->unique(function ($member) {
                return $member['user_id'];
            })
            ->pluck('user_id')
            ->toArray();

        return response()->json([
            'chime_id' => $chime->id,
            'channel_name' => $channelName,
            'user_ids' => $uniqUserIds,
            'user_count' => count($uniqUserIds),
        ]);
    }
}
