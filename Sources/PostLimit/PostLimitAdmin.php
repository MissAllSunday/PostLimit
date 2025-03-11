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

class PostLimitAdmin
{
    public const SETTINGS = 'settings';
    public const PERMISSIONS = 'permissions';
    public const ACTIONS = [
        self::SETTINGS,
        self::PERMISSIONS,
    ];
    public const URL = 'action=admin;area=postlimit';
    protected PostLimitService $service;
    protected PostLimitUtils $utils;

    public function __construct(?PostLimitService $service = null, ?PostLimitUtils $utils = null)
    {
        $this->utils = $utils ?? new PostLimitUtils();
        $this->service = $service ?? new PostLimitService();
    }

    public function menu(&$admin_areas): void
    {
        $this->loadRequiredFiles();

        $admin_areas['config']['areas'][strtolower(PostLimit::NAME)] = [
            'label' => $this->utils->text('admin_panel'),
            'function' => [$this, 'main'],
            'icon' => 'security',
            'subsections' => [
                self::SETTINGS => [$this->utils->text('admin_settings')],
                self::PERMISSIONS => [$this->utils->text('admin_permissions')],
            ],
        ];
    }

    public function main(): void
    {
        global $context;

        $context[$context['admin_menu_name']]['tab_data'] = [
            'title' => $this->utils->text('admin_panel'),
            'description' => $this->utils->text('admin_panel_desc'),
            'tabs' => [
                self::SETTINGS => [],
                self::PERMISSIONS => []
            ],
        ];

        $action = $this->utils->request('sa');
        $action = $action && in_array($action, self::ACTIONS, true) ?
        $action : self::ACTIONS[0];

        $this->setContext($action);
        $this->{$action}($action);
    }

    public function settings(string $action): void
    {
        $configVars = [
            ['check', PostLimit::NAME . '_enable','subtext' => $this->utils->text('enable_sub')],
            ['large_text', PostLimit::NAME . '_custom_message', 'subtext' => $this->utils->text('custom_message_sub')],
            ['int', PostLimit::NAME . '_post_count_alert', 'subtext' => $this->utils->text('post_count_alert_sub')],
            ['large_text', PostLimit::NAME . '_custom_alert_message', 'subtext' => $this->utils->text('custom_alert_message_sub')],
        ];

        if ($this->utils->isRequestSet('save'))
        {
            $this->saveConfig($configVars, $action);
        }

        prepareDBSettingContext($configVars);
    }

    public function permissions(string $action): void
    {
        $configVars = [
            [
                'permissions',
                PostLimit::NAME . '_can_set_post_limit',
                0,
                $this->utils->text('can_set_post_limit'),
            ]
        ];

        if ($this->utils->isRequestSet('save'))
        {
            $this->saveConfig($configVars, $action);
        }

        prepareDBSettingContext($configVars);
    }

    public function permissionsHook(&$permissionGroups, &$permissionList)
    {
        $simple = PostLimit::NAME . '_per_simple';
        $classic = PostLimit::NAME . '_per_classic';

        $permissionList['membergroup'][PostLimit::NAME . '_can_set_post_limit'] = [
            false,
            $classic,
            $simple
        ];
        $permissionGroups['membergroup']['simple'] = [$simple];
        $permissionGroups['membergroup']['classic'] = [$classic];
    }

    protected function saveConfig(array $configVars, string $action): void
    {
        checkSession();
        saveDBSettings($configVars);
        redirectexit(self::URL . ';sa=' . $action);
    }

    protected function setContext(string $action): void
    {
        global $context, $scripturl;

        $context['sub_action'] = $action;
        $context['page_title'] = $this->utils->text('admin_' . $action);
        $context['post_url'] = $scripturl . '?' . self::URL .';sa=' . $action . ';save';
        $context['sub_template'] = 'show_settings';
        $context['settings_title'] = $context['page_title'];
    }

    protected function loadRequiredFiles(): void
    {
        global $sourcedir;

        isAllowedTo('admin_forum');

        loadLanguage(PostLimit::NAME);
        loadtemplate(PostLimit::NAME);

        require_once($sourcedir . '/ManageSettings.php');
        require_once($sourcedir . '/ManageServer.php');
    }
}