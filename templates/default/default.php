<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="robots" content="none">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $error->type ?>&hellip;</title>
    <link rel="shortcut icon" href="data:image/png;base64,<?= $this->read(__DIR__ . '/favicon.png', 'base64') ?>">

    <?php foreach ($this->getTheme() as $theme): ?>
        <style><?= $this->read($theme) ?></style>
    <?php endforeach; ?>
</head>
<body class="<?= $classes($ife($error->hasFrames, 'has-frames')) ?>">
    <header class="header-panel"><?= $header ?></header>

    <main class="main-panel">
        <aside class="side-panel">
            <section class="error-details" data-error>
                <div class="type">
                    <i class="icon icon-warning"></i>
                    <span class="text"><?= $error->type ?></span>
                </div>
                <h1 class="message"><?= $error->message ?></h1>
                <pre class="preview" data-preview>
                <div class="line-numbers" data-line-numbers data-start="<?= $this->getExcerptStart($error->line) ?>"></div>
                <code class="code php" data-code><?= $this->getFileContents($error->file, $error->line) ?></code>
            </pre>
                <small class="file" data-file-path><?= $error->file ?></small>
            </section>

            <?php if ($error->hasFrames): ?>
                <ul class="call-stack">
                    <?php foreach ($error->frames as $frame): ?>
                        <?php $file = $frame->getFile(); ?>
                        <?php $line = $frame->getLine(); ?>

                        <li class="<?= $classes('stack-frame', $ife($file && $showCode, '-file')) ?>" data-frame>
                            <code class="caller php" data-highlight><?= $this->getCaller($frame); ?></code>

                            <?php if ($file && $showCode): ?>
                                <small class="file file-path -plain">
                                    <span class="path" data-file-path><?= $file ?></span>
                                    <span class="line"><?= $line ?></span>
                                </small>
                                <pre class="preview" data-preview>
                            <div class="line-numbers" data-line-numbers data-start="<?= $this->getExcerptStart($line) ?>"></div>
                            <code class="code php" data-code><?= $this->getFileContents($file, $line) ?></code>
                        </pre>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </aside>

        <?php if ($showCode): ?>
            <section class="frame-details" data-current-frame>
                <small class="file-path" data-path></small>
                <pre class="<?= $classes('preview', $ife($this->isExcerptOnly(), '-excerpt')) ?>" data-preview></pre>
            </section>
        <?php endif; ?>
    </main>

    <footer class="footer-panel"><?= $footer ?></footer>

    <script><?= $this->read(__DIR__ . '/scripts.js') ?></script>
</body>
</html>
