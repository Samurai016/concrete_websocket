<?php defined('C5_EXECUTE') or die("Access Denied.");

use Concrete\Core\Support\Facade\Url;
use Concrete\Core\View\View;

View::element('system_errors', [
    'format' => 'block',
    'error' => $errors,
    'message' => isset($message) ? $message : null,
]);

$app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
?>

<div class="ccm-dashboard-header-buttons">
    <div class="btn-group">
        <a href="https://github.com/Samurai016/concrete_websocket#readme" target="_blank" title="<?= t("GitHub Page"); ?>" class="btn btn-secondary">
            <i class="fab fa-github"></i> <?= t("GitHub Page"); ?>
        </a>
    </div>
</div>


<div class="row" id="processes">
    <div class="col-12">
        <h3><?= t("Available processes") ?></h3>
    </div>
    <div class="col-12">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th><?= t("Name") ?></th>
                    <th><?= t("Status") ?></th>
                    <th><?= t("Port") ?></th>
                    <th><?= t("PID") ?></th>
                    <th width="120"></th>
                </tr>
            </thead>
            <tbody>
                <?php if (is_array($processes) && count($processes) > 0) { ?>
                    <?php foreach ($processes as $process) { ?>
                        <tr process-id="<?= $process->getID(); ?>">
                            <td><strong title="<?= $process->getClass(); ?>"><?= $process->getName(); ?></strong></td>
                            <td>
                                <?php if ($process->getStatus() == 'off') { ?>
                                    <?= t("Turned off") ?>
                                <?php } else { ?>
                                    <?= sprintf(t("Running"), $process->getPort()) ?>
                                <?php } ?>
                            </td>
                            <td class="port-cell">
                                <div>
                                    <span><?= $process->getPort(); ?></span>
                                    <button class="btn btn-info">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                </div>
                                <form action="<?= $this->action('edit/' . $process->getID()); ?>" method="post">
                                    <?= $token->output('concrete_websocket_process_form_'.$process->getID()); ?>
                                    <input type="number" step="1" min="1024" max="65535" name="port" value="<?= $process->getPort(); ?>" class="form-control">

                                    <button type="reset" class="btn btn-danger">
                                        <i class="fa fa-times"></i>
                                    </button>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fa fa-save"></i>
                                    </button>
                                </form>
                            </td>
                            <td><?= implode(',', $process->getPids()); ?></td>
                            <td style="white-space:nowrap;">
                                <?php if ($process->getStatus() == 'off') { ?>
                                    <a class="btn btn-success text-white <?= $canExec ? '' : 'disabled'; ?>" title="<?= t("Start process") ?>" href="<?= Url::to('/dashboard/websocket/start/' . $process->getId()) ?>"><?= t("Start") ?></a>
                                <?php } else { ?>
                                    <a class="btn btn-danger text-white <?= $canExec ? '' : 'disabled'; ?>" title="<?= t("Stop process") ?>" href="<?= Url::to('/dashboard/websocket/stop/' . $process->getId()) ?>"><?= t("Stop") ?></a>
                                    <a class="btn btn-warning text-white <?= $canExec ? '' : 'disabled'; ?>" title="<?= t("Restart process") ?>" href="<?= Url::to('/dashboard/websocket/restart/' . $process->getId()) ?>"><?= t("Restart") ?></a>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else {  ?>
                    <tr>
                        <td colspan="5" class="text-center"><?= t("No process available") ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<hr>

<div class="row" id="settings">
    <div class="col-12">
        <h3><?= t("Settings") ?></h3>
    </div>
    <form class="col-12" action="<?= $this->action('settings'); ?>" method="post">
        <?= $token->output('concrete_websocket_settings_form'); ?>

        <div class="form-group row">
            <label for="<?= CONCRETEWEBSOCKET_SETTINGS_API_PASSWORD; ?>" class="control-label col-sm-3"><?= t("REST API Password") ?></label>
            <div class="col-sm-9">
                <div class="input-group">
                    <input type="text" id="<?= CONCRETEWEBSOCKET_SETTINGS_API_PASSWORD; ?>" name="<?= CONCRETEWEBSOCKET_SETTINGS_API_PASSWORD; ?>" value="<?= isset($settings[CONCRETEWEBSOCKET_SETTINGS_API_PASSWORD]) ? $settings[CONCRETEWEBSOCKET_SETTINGS_API_PASSWORD] : ''; ?>" class="form-control ccm-input-text" placeholder="<?= t("REST API Password") ?>" required />
                    <span class="input-group-addon"><i class="fa fa-asterisk"></i></span>
                </div>
                <div class="help-block small"><?= t("For security, Concrete WebSocket requests a password for the REST API so malicious users can't start/stop servers without be authorized.<br/>Any calls to Concrete WebSocket API <b>MUST</b> have the <code>%s</code> query param or the header <code>%s</code> set.", CONCRETEWEBSOCKET_PASSWORD_PARAM, CONCRETEWEBSOCKET_PASSWORD_HEADER) ?></div>
            </div>
        </div>

        <div class="form-group row">
            <label for="<?= CONCRETEWEBSOCKET_SETTINGS_PHP_PATH; ?>" class="control-label col-sm-3"><?= t("PHP Executable Path") ?></label>
            <div class="col-sm-9">
                <div class="input-group">
                    <input type="text" id="<?= CONCRETEWEBSOCKET_SETTINGS_PHP_PATH; ?>" name="<?= CONCRETEWEBSOCKET_SETTINGS_PHP_PATH; ?>" value="<?= isset($settings[CONCRETEWEBSOCKET_SETTINGS_PHP_PATH]) ? $settings[CONCRETEWEBSOCKET_SETTINGS_PHP_PATH] : ''; ?>" class="form-control ccm-input-text" placeholder="<?= t("PHP Executable Path") ?>" required />
                    <span class="input-group-addon"><i class="fa fa-asterisk"></i></span>
                </div>
                <div class="help-block small"><?= t("To start the servers, Concrete WebSocket needs to know the PHP executable path.<br/><b>By default you should find the correct path already set</b> here but if you notice errors in starting the servers or you are on Windows systems, make sure that the path set here is correct.") ?></div>
            </div>
        </div>

        <button class="btn btn-primary" type="submit"><?= t("Save settings") ?></button>
    </form>
</div>

<style>
    .fab {
        font-family: 'Font Awesome 5 Brands', 'FontAwesome';
        -moz-osx-font-smoothing: grayscale;
        -webkit-font-smoothing: antialiased;
        display: inline-block;
        font-style: normal;
        font-variant: normal;
        text-rendering: auto;
        line-height: 1;
    }

    .ccm-ui #ccm-dashboard-content table td {
        vertical-align: middle;
    }

    .ccm-ui .port-cell button {
        padding: 8px 12.6px;
        margin-left: 5px;
    }

    .ccm-ui .port-cell form,
    .ccm-ui .port-cell[active] div {
        display: none;
    }

    .ccm-ui .port-cell div,
    .ccm-ui .port-cell[active] form {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
</style>

<script>
    document.querySelectorAll('.port-cell button:not([type="submit"])').forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.target.closest('.port-cell').toggleAttribute('active');
        });
    });
</script>