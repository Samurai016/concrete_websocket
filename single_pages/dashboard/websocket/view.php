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
                    <th width="20"></th>
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
                            <td class="error">
                                <?php if ($process->getErrors() && count($process->getErrors()) > 0) { ?>
                                    <i class="fa fa-exclamation-circle text-danger"></i>
                                    <div class="d-none">
                                        <div class="popup">
                                            <table class="table table-striped align-middle">
                                                <thead>
                                                    <tr>
                                                        <th><?= t("Date") ?></th>
                                                        <th><?= t("Error") ?></th>
                                                        <th width="50"></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($process->getErrors() as $date => $error) { ?>
                                                        <tr>
                                                            <td><?= $date; ?></td>
                                                            <td><?= str_replace("\r\n", "<br/>", $error); ?></td>
                                                            <td>
                                                                <a title="<?= t('Remove error'); ?>" onclick="javascript:deleteError(event, this)" href="<?= Url::to(sprintf('/dashboard/websocket/delete_error/%s/%s', $process->getId(), $date)) ?>">
                                                                    <i class="fa fa-trash text-danger"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                <?php } ?>
                            </td>
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
                        <td colspan="3" class="text-center"><?= t("No process available") ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function deleteError(e, el) {
        e.preventDefault();
        $.ajax({
            url: $(el).attr('href'),
            dataType: 'json',
            success: function() {
                $(el).closest('tr').remove();
            },
            error: function(res, status, err) {
                alert(`<?= t("Unable to delete error, reload page and retry.\\nError: "); ?>` + res.responseJSON?.error);
            }
        });
    }

    $('.error').click(function() {
        $.fn.dialog.open({
            title: `<?= t("Errors found") ?>`,
            width: 'auto',
            height: 'auto',
            element: $(this).find('.popup').clone()
        });
    });
</script>