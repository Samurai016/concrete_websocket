<?php

namespace Concrete\Package\ConcreteWebsocket;

use Concrete\Core\Backup\ContentImporter;
use Concrete\Core\Package\Package;
use Concrete\Core\Asset\AssetList;
use Concrete\Core\Support\Facade\Url;
use Concrete\Core\Support\Facade\Application;
use ConcreteWebsocket\Websocket\Constants;
use ConcreteWebsocket\Websocket\Manager\SettingsManager;
use ConcreteWebsocket\Websocket\Routes\RouteList;

class Controller extends Package
{
    protected $pkgHandle = 'concrete_websocket';
    protected $appVersionRequired = '8.0';
    protected $pkgVersion = '1.1.1';

    protected $pkgAutoloaderRegistries = [
        'bootstrapper/src' => '\ConcreteWebsocket\Websocket',
        'bootstrapper/src/middleware' => '\ConcreteWebsocket\Websocket\Middleware',
        'bootstrapper/src/manager' => '\ConcreteWebsocket\Websocket\Manager',
        'bootstrapper/src/routes' => '\ConcreteWebsocket\Websocket\Routes',
        'bootstrapper/src/routes/middleware' => '\ConcreteWebsocket\Websocket\Routes\Middleware',
        'bootstrapper/src/utils' => '\ConcreteWebsocket\Websocket\Utils',
    ];

    public function getPackageName()
    {
        return t('Concrete Websocket');
    }

    public function getPackageDescription()
    {
        return t('Add websocket support.');
    }

    public function install()
    {
        // Check PHP version
        if (version_compare(phpversion(), '7.0.0', '>') < 0) {
            throw new \Exception(t('This package requires at least version 7 of PHP'), 1);
        }

        $pkg = parent::install();
        $ci = new ContentImporter();
        $ci->importContentFile($pkg->getPackagePath() . '/config/dashboard.xml');

        // Default settings
        SettingsManager::set(CONCRETEWEBSOCKET_SETTINGS_API_PASSWORD, substr(str_shuffle(md5(microtime())), 0, 10));

        // Bugfix: Seems that sometimes concrete5 do a rollback on the database after package installation, this fix it
        $app = Application::getFacadeApplication();
        $db = $app->make('database')->connection();
        if ($db->isTransactionActive()) {
            $db->commit();
        }
    }

    public function on_start() {
        // Register constants
        Constants::registerConstants();

        // Register assets
        $al = AssetList::getInstance();
        $al->register('css', 'concrete_websocket_css', 'css/concrete_websocket.css', array(), $this);

        // Register routes
        SettingsManager::set(CONCRETEWEBSOCKET_SETTINGS_WEBHOOK, URL::to(CONCRETEWEBSOCKET_CONCRETE_AUTH_ENDPOINT));
        $router = $this->app->make('router');
        $list = new RouteList();
        $list->loadRoutes($router);
    }
}