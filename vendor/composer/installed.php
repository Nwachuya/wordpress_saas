<?php return array(
    'root' => array(
        'name' => 'wp-saas/stripe-plugin',
        'pretty_version' => 'dev-main',
        'version' => 'dev-main',
        'reference' => 'c30f859893499205b4a7a4b4ea1c28d7a48f52b5',
        'type' => 'wordpress-plugin',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => true,
    ),
    'versions' => array(
        'stripe/stripe-php' => array(
            'pretty_version' => 'v14.10.0',
            'version' => '14.10.0.0',
            'reference' => '7e1c4b5d2beadeaeddc42fd1f8a50fdb18b37f30',
            'type' => 'library',
            'install_path' => __DIR__ . '/../stripe/stripe-php',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'wp-saas/stripe-plugin' => array(
            'pretty_version' => 'dev-main',
            'version' => 'dev-main',
            'reference' => 'c30f859893499205b4a7a4b4ea1c28d7a48f52b5',
            'type' => 'wordpress-plugin',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
    ),
);
