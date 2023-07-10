<?php

namespace Concrete\Package\ConcreteWebSocket;

use Concrete\Core\Package\Package;
use Concrete\Core\Support\Facade\Url;
use Concrete\Core\Page\Single as SinglePage;
use ConcreteWebSocket\WebSocket\Constants;
use ConcreteWebSocket\WebSocket\Manager\SettingsManager;
use ConcreteWebSocket\WebSocket\Routes\RouteList;

class Controller extends Package {
    protected $pkgHandle = 'concrete_websocket';
    protected $appVersionRequired = '8.0';
    protected $pkgVersion = '1.1.3';

    protected $pkgAutoloaderRegistries = [
        'bootstrapper/src' => '\ConcreteWebSocket\WebSocket',
        'bootstrapper/src/middleware' => '\ConcreteWebSocket\WebSocket\Middleware',
        'bootstrapper/src/manager' => '\ConcreteWebSocket\WebSocket\Manager',
        'bootstrapper/src/routes' => '\ConcreteWebSocket\WebSocket\Routes',
        'bootstrapper/src/routes/middleware' => '\ConcreteWebSocket\WebSocket\Routes\Middleware',
        'bootstrapper/src/utils' => '\ConcreteWebSocket\WebSocket\Utils',
    ];

    public function getPackageName() {
        return t('Concrete WebSocket');
    }

    public function getPackageDescription() {
        return t('Add WebSocket support.');
    }

    public function install() {
        // Check PHP version
        if (version_compare(phpversion(), '7.0.0', '>') < 0) {
            throw new \Exception(t('This package requires at least version 7 of PHP'), 1);
        }

        $pkg = parent::install();

        // Install single pages
        $page = SinglePage::add('/dashboard/websocket', $pkg);
        $page->updateCollectionName(t('WebSocket Dashboard'));

        // Default settings
        Constants::registerConstants();
        SettingsManager::set(CONCRETEWEBSOCKET_SETTINGS_API_PASSWORD, substr(str_shuffle(md5(microtime())), 0, 10));
        SettingsManager::set(CONCRETEWEBSOCKET_SETTINGS_PHP_PATH, Console::getPhpExecutable());

        // Bugfix: Seems that sometimes concrete5 do a rollback on the database after package installation, this fix it
        try {
            $db = $this->app->make('database')->connection();
            if ($db->isTransactionActive()) {
                $db->commit();
            }
        } catch (\PDOException $th) {
            // Ignore PDOException
        }
    }

    public function on_start() {
        // Register constants
        Constants::registerConstants();

        // Register routes
        SettingsManager::set(CONCRETEWEBSOCKET_SETTINGS_WEBHOOK, URL::to(CONCRETEWEBSOCKET_CONCRETE_AUTH_ENDPOINT));
        $router = $this->app->make('router');
        $list = new RouteList();
        $list->loadRoutes($router);
    }
}
