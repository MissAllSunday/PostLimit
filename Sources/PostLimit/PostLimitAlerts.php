<?php

namespace PostLimit;

class PostLimitAlerts
{
    protected PostLimitService $service;
    protected PostLimitUtils $utils;

    public function __construct()
    {
        $this->service = new PostLimitService();
        $this->utils = new PostLimitUtils();
    }
    public function handle(array &$alerts, array &$formats): void
    {
        $postLimitAlert = [];
        $refId = 0;
        foreach ($alerts as $id => $alert) {
            if ($alert['content_type'] === strtolower(PostLimit::NAME)) {
                $postLimitAlert = $alert;
                $refId = $id;
                break;
            }
        }

        if ($refId === 0) {
            return;
        }

        $postLimitAlert['text'] = $this->buildAlertText($postLimitAlert);
        $alerts[$refId] = $postLimitAlert;
    }

    protected function buildAlertText($postLimitAlert): string
    {
        $entity = $this->service->getEntityByUser((int) $postLimitAlert['sender_id']);
        $alertPercentage = $this->service->calculatePercentage($entity);

        $alertTextKey = $alertPercentage['postsLeft'] <= 0 ?
            'message' : 'alert_message';
        $alertTextTemplate = $this->utils->setting(('custom_' . $alertTextKey),
            $this->utils->text($alertTextKey . '_default'));

        return strtr($alertTextTemplate,
            [
                '{user_name}' => $postLimitAlert['sender_name'],
                '{limit}' => $alertPercentage['limit'],
                '{posts_left}' => $alertPercentage['postsLeft'],
                '{percentage}' => $alertPercentage['percentage'],
            ],
        );
    }
}