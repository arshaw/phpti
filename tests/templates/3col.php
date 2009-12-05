<? include dirname(__FILE__) . '/base.php' ?>

<? startblock('content') ?>
<div id='left'>
<? block('left') ?>
</div>
<div id='center'>
<? block('center') ?>
</div>
<div id='right'>
<? block('right') ?>
</div>
<? endblock() ?>
