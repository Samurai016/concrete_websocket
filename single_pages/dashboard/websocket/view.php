<?php defined('C5_EXECUTE') or die("Access Denied.");

use Concrete\Core\Support\Facade\Url;
use Concrete\Core\View\View;

$errors = [];
if (isset($websocketError)) $errors[] = $websocketError;
if (!$execAvailable) $errors[] = t("exec is disabled, this prevents websocket servers from starting.\nContact your server administrator and ask them to change this setting.\nConcrete Websocket is safe and open-source, we use exec only and exclusively to start, shut down and control websocket servers.");

View::element('system_errors', [
    'format' => 'block',
    'error' => $errors,
    'success' => isset($success) ? $success : null,
    'message' => isset($message) ? $message : null,
]);
?>
<div class="row">
    <div class="col-10">
        <h2><?= t("Available processes") ?></h2>
    </div>
    <div class="col-12">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th><?= t("Name") ?></th>
                    <th><?= t("Status") ?></th>
                    <th><?= t("PID") ?></th>
                    <th width="120"></th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($processes)) { ?>
                    <?php foreach ($processes as $process) { ?>
                        <tr process-id="<?= $process->getID(); ?>">
                            <td><strong><?= $process->getName(); ?></strong></td>
                            <td>
                                <?php if ($process->getStatus() == 'off') { ?>
                                    <?= t("Turned off") ?>
                                <?php } else { ?>
                                    <?= sprintf(t("Running at port %s"), $process->getPort()) ?>
                                <?php } ?>
                            </td>
                            <td><?= implode(',', $process->getPids()); ?></td>
                            <td>
                                <?php if ($process->getStatus() == 'off') { ?>
                                    <a class="btn btn-success text-white <?= $execAvailable ? '' : 'disabled'; ?>" title="<?= t("Start process") ?>" href="<?= Url::to('/dashboard/websocket/start/' . $process->getId()) ?>"><?= t("Start") ?></a>
                                <?php } else { ?>
                                    <a class="btn btn-danger text-white <?= $execAvailable ? '' : 'disabled'; ?>" title="<?= t("Stop process") ?>" href="<?= Url::to('/dashboard/websocket/stop/' . $process->getId()) ?>"><?= t("Stop") ?></a>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else {  ?>
                    <tr>
                        <td colspan="4" class="text-center"><?= t("No process available") ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>