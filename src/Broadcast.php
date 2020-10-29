<?php

class Broadcast {
    private $authorization;

    public function __construct($authorization)
    {
        $this->authorization = $authorization;
    }
    private function list($broadcastStatus = 'active')
    {
        $list = [];

        $http = new GuzzleHttp\Client([
            'base_uri' => 'https://youtube.googleapis.com/youtube/v3/',
            'timeout' => 10.0,
        ]);

        $response = $http->request(
            'GET',
            "liveBroadcasts?broadcastStatus={$broadcastStatus}&broadcastType=all&maxResults=10",
            [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => $this->authorization
                ]
            ]
        );
        if (200 == $response->getStatusCode()) {
            $body = (string) $response->getBody();
            $result = json_decode($body);

            foreach ($result->items as $item) {
                $list[] = (object) [
                    'id' => $item->id,
                    'channelId' => $item->snippet->channelId,
                    'title' => $item->snippet->title,
                    'scheduledStartTime' => $item->snippet->scheduledStartTime,
                    'privacyStatus' => $item->status->privacyStatus,                    
                ];
            }
        }
        return $list;
    }
    private function filter($list, $channelId, $privacyStatus)
    {
        $result = [];
        foreach ($list as $item) {
            if (($channelId == $item->channelId)
                && ($privacyStatus == $item->privacyStatus)) {
                $result[] = $item;
            }
        }
        return $result;
    }
    public function getLive($channelId)
    {
        $list = $this->list('active');
        $list = $this->filter($list, $channelId, 'public');
        return $list;
    }
    public function getUpcoming($channelId)
    {
        $list = $this->list('upcoming');
        $list = $this->filter($list, $channelId, 'public');
        return $list;
    }
}
