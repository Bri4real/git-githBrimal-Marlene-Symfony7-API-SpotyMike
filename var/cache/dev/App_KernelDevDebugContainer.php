<?php

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.

if (\class_exists(\Container3GlxcxF\App_KernelDevDebugContainer::class, false)) {
    // no-op
} elseif (!include __DIR__.'/Container3GlxcxF/App_KernelDevDebugContainer.php') {
    touch(__DIR__.'/Container3GlxcxF.legacy');

    return;
}

if (!\class_exists(App_KernelDevDebugContainer::class, false)) {
    \class_alias(\Container3GlxcxF\App_KernelDevDebugContainer::class, App_KernelDevDebugContainer::class, false);
}

return new \Container3GlxcxF\App_KernelDevDebugContainer([
    'container.build_hash' => '3GlxcxF',
    'container.build_id' => 'e55d9d1c',
    'container.build_time' => 1713530917,
    'container.runtime_mode' => \in_array(\PHP_SAPI, ['cli', 'phpdbg', 'embed'], true) ? 'web=0' : 'web=1',
], __DIR__.\DIRECTORY_SEPARATOR.'Container3GlxcxF');
