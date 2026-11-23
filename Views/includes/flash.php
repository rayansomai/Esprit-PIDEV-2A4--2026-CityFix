<?php
/**
 * Views/includes/flash.php — messages flash (succès, erreur, information),
 * partagé par le Front Office et le Back Office.
 */
$flashClasses = ['success' => 'cf-alert--success', 'error' => 'cf-alert--error', 'info' => 'cf-alert--info', 'warning' => 'cf-alert--warning'];
?>
<?php if (!empty($flash)): ?>
    <div class="cf-flash">
        <?php foreach ($flash as $msg): ?>
            <div class="cf-alert <?= e($flashClasses[$msg['type']] ?? 'cf-alert--info') ?>"
                 role="<?= $msg['type'] === 'error' ? 'alert' : 'status' ?>">
                <?= e($msg['message']) ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
