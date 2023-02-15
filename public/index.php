<!-- Le fichier public/index.php est le point d'entrée vers notre application et lorsqu'il est appelé (à chaque connexion), il lance toute l'application Symfony -->

<?php

use App\Kernel;

require_once dirname(__DIR__) . '/vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
