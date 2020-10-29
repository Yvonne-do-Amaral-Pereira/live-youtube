<?php

class Broadcast {
    private $authorization;

    public function __construct($authorization)
    {
        $this->authorization = $authorization;
    }
    private function list($broadcastStatus = 'active', $privacyStatus)
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
                if ((! empty($privacyStatus)) && ($privacyStatus == $item->status->privacyStatus)) {
                    $list[] = (object) [
                        'id' => $item->id,
                        'channelId' => $item->snippet->channelId,
                        'title' => $item->snippet->title,
                        'scheduledStartTime' => $item->snippet->scheduledStartTime,
                        'privacyStatus' => $item->status->privacyStatus,
                    ];
                }
            }
        }
        return $list;
    }
    public function getLive($privacyStatus)
    {
        $list = $this->list('active', $privacyStatus);
        return $list;
    }
    public function getUpcoming($privacyStatus)
    {
        $list = $this->list('upcoming', $privacyStatus);
        return $list;
    }
}
