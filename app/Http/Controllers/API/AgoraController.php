<?php

namespace App\Http\Controllers\API;

use App\Helper\RtcTokenBuilder;
use App\Http\Controllers\Controller;
use App\Http\Requests\AgoraTokenRequest;
use App\Models\AgoraToken;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AgoraController extends Controller
{
    public function tokenGenerate(AgoraTokenRequest $request)
    {
        try {
            $data = $request->all();

            $agoraToken = AgoraToken::where(function ($q) use ($data) {
                return $q->where('sender_id', $data['sender_id'])->where('receiver_id', $data['receiver_id']);
            })->orWhere(function ($q) use ($data) {
                return $q->where('sender_id', $data['receiver_id'])->where('receiver_id', $data['sender_id']);
            })->first();

            $currentTimestamp = now()->getTimestamp();
            $channelName = !empty($agoraToken) ? $agoraToken->channel_name : $data['sender_id'] . "_" . $data['receiver_id'] . "_pinak" . rand(999, 1999) . "_" . $currentTimestamp;
//dd($channelName);
            $appID = env('AGORA_APP_ID');
            $appCertificate = env('AGORA_APP_CERTIFICATE');

            $expireTimeInSeconds = 3600;

            $uid = 2882341273;
            $uidStr = "2882341273";
            $role = RtcTokenBuilder::RoleAttendee;

            $currentTimestamp = (new \DateTime("now", new \DateTimeZone('UTC')))->getTimestamp();
            $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;

            $role = RtcTokenBuilder::RolePublisher;
            $expireTimeInSeconds = 3600;
            $currentTimestamp = now()->getTimestamp();
            $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;

            $token = RtcTokenBuilder::buildTokenWithUserAccount($appID, $appCertificate, $channelName, "", $role, $privilegeExpiredTs);

            if (empty($agoraToken)) {
                $agoraToken = AgoraToken::create([
                    'sender_id' => $data['sender_id'],
                    'receiver_id' => $data['receiver_id'],
                    'channel_name' => $channelName,
                    'common_token' => $token,
                    'audio_token' => $token,
                ]);
            } else {
                $agoraToken->common_token = $token;
                $agoraToken->audio_token = $token;
                $agoraToken->channel_name = $channelName;
                $agoraToken->save();
            }
            $user = User::where('id', $data['sender_id'])->first();

            $type = "agora_call";

            if (!empty($user)) {
                $notification_data = [
                    'id' => $user->id,
                    'type' => $type,
                    'user_name' => $user->first_name,
                    'subject' => $type,
                    'message' => "You've received call",
                    'isVideoCall' => !empty($data['is_video_call']) && $data['is_video_call'] === true,
                    'agora_token' => $token,
                    'channel_id' => $agoraToken->channel_name,
                    'image' => getSingleMedia($user, 'profile_image', null)
                ];

                $user = User::getUserByKeyValue('id', $data['receiver_id']);

                notificationSend($user, $type, $notification_data);
            }

            $response = [
                'message' => "Token Generated",
                'data' => [
                    'common_token' => $agoraToken->common_token,
                    'audio_token' => $agoraToken->audio_token,
                    'channel_name' => $agoraToken->channel_name

                ]
            ];
            return comman_custom_response($response);
        } catch (\Exception $e) {
            echo $e->getMessage() . " " . $e->getFile() . " " . $e->getLine();
            die;
            Log::error("Error in Generate Token ===<<< ", [$e->getMessage()]);

            return comman_custom_response([
                'message' => $e->getMessage(),
                'data' => []
            ], 500);
        }
    }
}
