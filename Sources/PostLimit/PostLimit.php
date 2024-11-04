<?php

declare(strict_types=1);

/**
 * Post Limit mod (SMF)
 *
 * @package PostLimit
 * @version 1.1
 * @author Michel Mendiola <suki@missallsunday.com>
 * @copyright Copyright (c) 2024  Michel Mendiola
 * @license https://www.mozilla.org/en-US/MPL/2.0/
 */

namespace PostLimit;

// No DI :(
require 'PostLimitService.php';
require 'PostLimitAdmin.php';
require 'PostLimitUtils.php';
require 'PostLimitEntity.php';
require 'PostLimitRepository.php';

class PostLimit
{
    public const NAME = 'PostLimit';
    public const DEFAULT_PERCENTAGE_TO_ALERT = 80;
    public const DEFAULT_POST_LIMIT = 0;
    private PostLimitService $service;

    public function __construct(?PostLimitService $service = null)
    {
        //No DI :(
        $this->service = $service ?? new PostLimitService();
    }

    public function handle(): void
    {
        if (!$this->service->isEnable() || !$this->service->isUserLimited()) {
            return;
        }

        $entity = $this->service->getEntityByUser();
        $postCount = $entity->getPostCount();
        $limit = $entity->getPostLimit();
        $messagesLeftCount = $limit - $postCount;

        if ($postCount < $limit && $messagesLeftCount <= self::DEFAULT_PERCENTAGE_TO_ALERT) {
            // @TODO: handle showing the notification on posting
            $notification = $this->service->getNotificationContent($messagesLeftCount);

            return;
        }

        if ($postCount >= $limit) {
            fatal_error($this->service->getFatalErrorMessage(), false);
        }
    }

    public function updateCount($msgOptions, $topicOptions, $posterOptions, $message_columns, $message_parameters): void
    {
        $this->service->updateCount();
    }

    public function createCount(&$regOptions, &$theme_vars, &$memberID)
    {
        $this->service->createDefaultEntity($memberID);
    }

    public function profile(&$profileAreas): void
    {
        global $txt;

        if (!$this->service->isEnable()) {
            return;
        }

        loadLanguage(PostLimit::NAME);

        $profileAreas['info']['areas'][strtolower(self::NAME)] = [
            'label' => $txt[self::NAME . '_profile_panel'],
            'icon' => 'members',
            'function' => fn () => $this->service->profilePage(),
            'permission' => [
                'own' => 'is_not_guest',
                'any' => 'profile_view',
            ],
        ];
    }
}
