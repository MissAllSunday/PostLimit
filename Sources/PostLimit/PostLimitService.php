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

class PostLimitService
{
    public PostLimitUtils $utils;
    private PostLimitRepository $repository;

    public function __construct(?PostLimitUtils $utils = null, ?PostLimitRepository $repository = null)
    {
        global $sourcedir;

        //No DI :(
        require_once($sourcedir . '/PostLimit/PostLimitRepository.php');
        require_once ($sourcedir . '/PostLimit/PostLimitUtils.php');

        $this->utils = $utils ?? new PostLimitUtils();
        $this->repository = $repository ?? new PostLimitRepository();
    }

    public function getEntityByUser(int $userId): ?PostLimitEntity
    {
        $entity = $this->repository->getByUser($userId);
        return (!$entity && $userId !== 0) ? $this->createDefaultEntity($userId) : $entity;
    }

    public function createDefaultEntity(int $userId): ?PostLimitEntity
    {
        return $this->repository->insert(new PostLimitEntity([
            PostLimitEntity::ID_USER => $userId,
            PostLimitEntity::ID_BOARDS => [],
            PostLimitEntity::POST_LIMIT => PostLimit::DEFAULT_POST_LIMIT,
            PostLimitEntity::POST_COUNT => 0,
        ]));
    }

    public function isEnable(): bool
    {
        global $user_info;

        return !$user_info['is_guest'] || !$this->utils->setting('enable');
    }

    public function isUserLimited(PostLimitEntity $entity, int $boardId): bool
    {
        if ($entity->isUserExempted() || !$this->isEnable()) {
            return false;
        }

        $boards = $entity->getIdBoards();
        $globalLimit = $entity->isGlobalLimitApplied();

        if (!$globalLimit) {
            return in_array($boardId, $boards);
        }

        return true;
    }

    public function isLimitReachedByUser(PostLimitEntity $entity, int $boardId): bool
    {
        $percentage = $this->calculatePercentage($entity);

        return $this->isUserLimited($entity, $boardId) &&
            $percentage['postCount'] >= $percentage['limit'];
    }

    public function buildErrorMessage(PostLimitEntity $entity, string $userName): string
    {
        $replacements = [
            'username' => $userName,
            'limit' => $entity->getPostLimit(),
        ];

        $find = $replace = [];

        foreach ($replacements as $f => $r) {
            $find[] = '{' . $f . '}';
            $replace[] = $r;
        }

        $customMessage = $this->utils->setting('custom_message', false);

        return (str_replace($find, $replace, $customMessage ??
            $this->utils->text('message_default')));
    }

    public function buildAlert(PostLimitEntity $entity): bool
    {
        if ($entity->isUserExempted()) {
            return false;
        }

        $alertPercentage = $this->calculatePercentage($entity);
        $hasUnreadAlerts = $this->repository->hasUnreadAlerts($entity->getIdUser());

        if ($alertPercentage['percentage'] >= $alertPercentage['postCountAlert'] && !$hasUnreadAlerts) {
            $this->repository->insertAlert([
                'idUser' => $entity->getIdUser(),
                'time' => time(),
            ]);

            return true;
        }

       return false;
    }

    public function calculatePercentage(PostLimitEntity $entity): array
    {
        $postCount = $entity->getPostCount();
        $limit = $entity->getPostLimit();

        $percentage = $this->utils->calculatePercentage($postCount, $limit);
        $postCountAlert = $this->utils->setting('post_count_alert');

        return [
            'percentage' => $percentage,
            'postCountAlert' => $postCountAlert,
            'postsLeft' => $limit - $postCount,
            'limit' => $limit,
            'postCount' => $postCount
        ];
    }

    public function updateEntity(PostLimitEntity $entity): void
    {
        $this->repository->update($entity);
    }

    public function updateCount(int $userId): void
    {
        $this->repository->updateCount($userId);
    }

    public function checkPermissions(string $permissionName) : bool
    {
        return in_array($permissionName, [
            'post_new',
            'post_unapproved_topics',
            'post_unapproved_replies_own',
            'post_reply_own',
            'post_reply_any',
            'post_unapproved_replies_any',
            'post_attachment',
            'post_unapproved_attachments',
        ]);
    }
}
