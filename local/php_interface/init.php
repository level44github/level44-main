<?php

\CModule::AddAutoloadClasses(
    "",
    [
        "\Level44\Base" => "/local/php_interface/lib/Base.php",
    ]
);

\Level44\Base::customRegistry();
\Level44\EventHandlers::register();
