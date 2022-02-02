<?php

namespace Concrete\Package\ConcreteWebsocket;

use Concrete\Core\Backup\ContentImporter;
use Concrete\Core\Package\Package;
use Concrete\Core\Asset\AssetList;
use Concrete\Core\Support\Facade\Route;
use Concrete\Core\User\User;
use ConcreteWebsocket\Websocket\Manager\SettingsManager;
use URL;

class Controller extends Package
{
    protected $pkgHandle = 'concrete_websocket';
    protected $appVersionRequired = '8.0';
    protected $pkgVersion = '1.0.1';

    protected $pkgAutoloaderRegistries = [
        'websocket/src' => '\ConcreteWebsocket\Websocket',
        'websocket/src/middleware' => '\ConcreteWebsocket\Websocket\Middleware',
        'websocket/src/manager' => '\ConcreteWebsocket\Websocket\Manager',
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
    }

    public function on_start() {
        // Register constants
        require_once join(DIRECTORY_SEPARATOR, [$this->getPackagePath(), 'websocket', 'configure.php']);

        // Register assets
        $al = AssetList::getInstance();
        $al->register('css', 'concrete_websocket_css', 'css/concrete_websocket.css', array(), $this);

        // Register routes
        SettingsManager::set('ConcreteCheckWebhook', URL::to(CONCRETE_WS_CONCRETE_CHECK_ENDPOINT));
        Route::register(CONCRETE_WS_CONCRETE_CHECK_ENDPOINT, function () {
            $user = new User();

            if ($user->isRegistered()) {
                echo $user->getUserID();
            } else {
                http_response_code(401);
                echo t('Not logged');
            }
        });
    }
}