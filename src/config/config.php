<?php

return array(
    /*
     * config for commands workbench:* added by Artistan
     */
    'packages'=>[
        'vendor/package'=>[
            'git'=>'git@github.com:vendor/devPackage.git',
            'remotes'=>[
                'upstream'=>'git@github.com:vendor/masterPackage.git',
                'upstream2'=>'git@github.com:vendor/masterPackage.git',
            ]
        ],
    ]
);