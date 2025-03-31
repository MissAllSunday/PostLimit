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

class PostLimit
{
    public const NAME = 'PostLimit';
    public const DEFAULT_PERCENTAGE_TO_ALERT = 80;
    public const DEFAULT_POST_LIMIT = 0;
    protected PostLimitService $service;

    public function __construct(?PostLimitService $service = null)
    {
        global $sourcedir;

        //No DI :(
        require_once($sourcedir . '/PostLimit/PostLimitRepository.php');
        require_once($sourcedir . '/PostLimit/PostLimitEntity.php');
        require_once ($sourcedir . '/PostLimit/PostLimitUtils.php');
        require_once($sourcedir . '/PostLimit/PostLimitService.php');

        $this->service = $service ?? new PostLimitService();
    }

    public function s(): bool
    {
        global $sourcedir;

        $repository = new PostLimitRepository();
        $repository->resetPostCount();

        // Make sure we are the last hook call, don't ask, just sage nod and move on
        $this->reOrderHookCall($repository);

        //No point in keeping alerts
        $repository->deleteAllAlerts();

        return true;
    }

    public function allowGeneral(&$user_permissions, array $permission)
    {
        global $user_info, $board_info;

        $permissionName = $permission[0];

        if (!$this->service->isEnable() || !isset($board_info) || !$this->service->checkPermissions($permissionName)) {
            return;
        }

        $entity = $this->service->getEntityByUser((int) $user_info['id']);

        if ($this->service->isLimitReachedByUser($entity, (int) $board_info['id'])) {
            $key = array_search($permissionName, $user_permissions);
            unset($user_permissions[$key]);
        }
    }

    public function checkLimit($msgOptions, $topicOptions, $posterOptions, $message_columns, $message_parameters): void
    {
        $posterId = (int) $posterOptions['id'];
        $boardId = (int) $topicOptions['board'];
        $entity = $this->service->getEntityByUser($posterId);

        if ($this->service->isLimitReachedByUser($entity, $boardId)) {
            $errorMessage = $this->service->buildErrorMessage($entity, $posterOptions['name']);

            fatal_lang_error($errorMessage);
        }
    }

    public function checkAlert($msgOptions, $topicOptions, $posterOptions, $message_columns, $message_parameters): void
    {
        $posterId = (int) $posterOptions['id'];

        $this->service->updateCount($posterId);
        $entity = $this->service->getEntityByUser($posterId);

        $this->service->buildAlert($entity);
    }

    public function createCount(&$regOptions, &$theme_vars, &$memberID)
    {
        $this->service->createDefaultEntity((int) $memberID);
    }

    protected function reOrderHookCall(PostLimitRepository $repository): void
    {
        $hookReference = 'PostLimit\PostLimit::checkLimit';
        $hooks = $repository->getCreatePostHooks();
        $explodedHooks = explode(',', $hooks);

        if (count($explodedHooks) <= 1) {
            return;
        }

        $key = array_search($hookReference, $explodedHooks, true);

        if ($key === false) {
            return;
        }

        unset($explodedHooks[$key]);
        $explodedHooks[] = $hookReference;

        $repository->updateCreatePostHooks(trim(implode(',', array_map('trim', $explodedHooks))));
    }
}



