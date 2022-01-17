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
    // TODO: Traduci in italiano
    protected $pkgHandle = 'concrete_websocket';
    protected $appVersionRequired = '8.0';
    protected $pkgVersion = '0.0.10';

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
        SettingsManager::set('ConcreteCheckWebhook', URL::to('/concrete_websocket/handshake'));
        Route::register(CONCRETE_CHECK_ENDPOINT, function () {
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