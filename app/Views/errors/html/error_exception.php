<?php
/**
 * @var string $title
 * @var string $exception
 */

$errorId = uniqid('error', true);
?>
<!doctype html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="robots" content="noindex">

    <title><?= esc($title) ?></title>
    <style>
        <?= preg_replace('#[\r\n\t ]+#', ' ', file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'debug.css')) ?>
    </style>

    <script>
        <?= file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'debug.js') ?>
    </script>
</head>

<body onload="init()">

    <!-- Header -->
    <div class="header">
        <div class="environment">
            Displayed at <?= esc(date('H:i:s')) ?> &mdash;
            PHP: <?= esc(PHP_VERSION) ?> &mdash;
            CodeIgniter: <?= esc(\CodeIgniter\CodeIgniter::CI_VERSION) ?> --
            Environment: <?= ENVIRONMENT ?>
        </div>
        <div class="container">
            <h1><?= esc($title), esc($exception->getCode() ? ' #' . $exception->getCode() : '') ?></h1>
            <p>
                <?= nl2br(esc($exception->getMessage())) ?>
                <a href="https://www.google.com/?q=<?= urlencode($title . ' ' . preg_replace('#\'.*\'|".*"#Us', '', $exception->getMessage())) ?>"
                    rel="noreferrer" target="_blank">search &rarr;</a>
            </p>
        </div>
    </div>

    <!-- Source -->
    <div class="container">
        <p><b><?= esc(clean_path($file)) ?></b> at line <b><?= esc($line) ?></b></p>

        <?php if (is_file($file)): ?>
            <div class="source">
                <?= static::highlightFile($file, $line, 15); ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="container">
        <?php
        $last = $exception;

        while ($prevException = $last->getPrevious()) {
            $last = $prevException;
        ?>
            <pre>
Caused by:
<?= esc($prevException::class), esc($prevException->getCode() ? ' #' . $prevException->getCode() : '') ?>

<?= nl2br(esc($prevException->getMessage())) ?>
<a href="https://www.google.com/?q=<?= urlencode($prevException::class . ' ' . preg_replace('#\'.*\'|".*"#Us', '', $prevException->getMessage())) ?>"
   rel="noreferrer" target="_blank">search &rarr;</a>
<?= esc(clean_path($prevException->getFile()) . ':' . $prevException->getLine()) ?>
            </pre>
        <?php
        }
        ?>
    </div>

</body>
</html>