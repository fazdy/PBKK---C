<?php if (!empty($errors)): ?>
<div class="notice error" role="alert"><strong>Periksa kembali data Anda</strong><ul class="mb-0 mt-2">
<?php foreach ($errors as $message): ?><li><?= esc($message) ?></li><?php endforeach; ?>
</ul></div>
<?php endif; ?>
