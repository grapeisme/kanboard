<div class="page-header">
    <h2><?= t('Edit external link') ?></h2>
</div>

<form class="popover-form" action="<?= $this->url->href('TaskExternalLink', 'update', array('task_id' => $task['id'], 'project_id' => $task['project_id'])) ?>" method="post" autocomplete="off">
    <?= $this->render('task_external_link/form', array('task' => $task, 'dependencies' => $dependencies, 'values' => $values, 'errors' => $errors)) ?>

    <div class="form-actions">
        <input type="submit" value="<?= t('Save') ?>" class="btn btn-blue">
        <?= t('or') ?>
        <?= $this->url->link(t('cancel'), 'TaskExternalLink', 'show', array('task_id' => $task['id'], 'project_id' => $task['project_id']), false, 'close-popover') ?>
    </div>
</form>