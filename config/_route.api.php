<?php
### ───────────────────────────────────────────────
### @route Base
### ───────────────────────────────────────────────
$app->group('/api/v1', function ($group) {
    // Action test
    $group->get(
        '/items', [App\Controller\Api\V1\DocumentController::class, 'test']
    )->setName('items.index');
    // Action Docs
    $group->get(
        '/docs', [App\Controller\Api\V1\DocumentController::class, 'getDocAction']
    )->setName('doc.index');
    // Action Sessions
    $group->group('/token', function($group) {
        $group->post(
            '', [App\Controller\Api\V1\TokenController::class, "postTokenAction"]
        )->setName('token.post') 
        ->add(new App\Middleware\JsonMiddleware())
        ->add(new App\Middleware\JsonBodyParserMiddleware())
        ->add(new App\Middleware\JsonUserBodyMiddleware());
        $group->delete(
            '/{id:[0-9]+}', 
            [App\Controller\Api\V1\TokenController::class, 'deleteTokenAction']
        )->setName('token.delete')
        ->add(new App\Middleware\AuthMiddleware());
        $group->patch(
            '/{id:[0-9]+}', 
            [App\Controller\Api\V1\TokenController::class, 'patchTokenAction']
        )->setName('token.patch')
        ->add(new App\Middleware\JsonMiddleware())
        ->add(new App\Middleware\JsonBodyParserMiddleware())
        ->add(new App\Middleware\JsonTokenBodyMiddleware())
        ->add(new App\Middleware\AuthMiddleware());
    });
    // Action schools
    $group->group('/ecoles', function ($group) {
        $group->get(
            '', [App\Controller\Api\V1\SchoolController::class, 'getSchoolsAction']
        )->setName('ecoles.index'); 
        // Post Ecole
        $group->post(
            '', [App\Controller\Api\V1\SchoolController::class, 'postSchoolsAction']
        )->setName('ecoles.post')
        ->add(new App\Middleware\JsonMiddleware())
        ->add(new App\Middleware\JsonBodyParserMiddleware())
        ->add(new App\Middleware\JsonSchoolBodyMiddleware())
        ->add(new App\Middleware\AuthMiddleware());;
        //Action school by Page
        $group->get(
            '/page/{page:[0-9]+}', 
            [\App\Controller\Api\V1\SchoolController::class, 'getSchoolsByPageAction']
        )->setName('ecoles.get.page');
        // Action school by ID
        $group->group('/{id:[0-9]+}', function($group) {
            $group->get(
                '', [App\Controller\Api\V1\SchoolController::class, 'getSchoolAction']
            )->setName('ecole.get');
            $group->put(
                '', [App\Controller\Api\V1\SchoolController::class, 'putSchoolAction'], 
            )->setName('ecole.put')
            ->add(new App\Middleware\JsonMiddleware())
            ->add(new App\Middleware\JsonBodyParserMiddleware())
            ->add(new App\Middleware\JsonSchoolBodyMiddleware())
            ->add(new App\Middleware\AuthMiddleware());;
            $group->patch(
                '', [App\Controller\Api\V1\SchoolController::class, 'patchSchoolAction']
            )->setName('ecole.patch') 
            ->add(new App\Middleware\JsonMiddleware())
            ->add(new App\Middleware\JsonBodyParserMiddleware())
            ->add(new App\Middleware\JsonSchoolBodyPartialMiddleware())
            ->add(new App\Middleware\AuthMiddleware());
            $group->delete(
                '', 
                [App\Controller\Api\V1\SchoolController::class, 'deleteSchoolAction']
            )->setName('ecole.delete')
            ->add(new App\Middleware\AuthMiddleware());
            // Action Adressses (sous-groupe d’écoles)
            $group->group('/adresses', function($group){
                $group->get(
                    '', 
                    [App\Controller\Api\V1\AddressController::class, 'getAdressesAction']
                )->setName('adresses.index');
                // Adresse POST
                $group->post(
                    '', 
                    [App\Controller\Api\V1\AddressController::class, 'postAdressesAction']
                )->setName('adresses.post')
                ->add(new App\Middleware\JsonMiddleware())
                ->add(new App\Middleware\JsonBodyParserMiddleware())
                ->add(new App\Middleware\JsonAddressBodyMiddleware())
                ->add(new App\Middleware\AuthMiddleware());
                $group->group('/{adresseid:[0-9]+}', function($group) {
                    $group->get(
                        '', 
                        [App\Controller\Api\V1\AddressController::class, 'getAdresseAction'
                        ] 
                    )->setName('address.index');
                    $group->put(
                        '', 
                        [App\Controller\Api\V1\AddressController::class, 'putAdresseAction'],
                    )->setName('address.put')
                    ->add(new App\Middleware\JsonMiddleware())
                    ->add(new App\Middleware\JsonBodyParserMiddleware())
                    ->add(new App\Middleware\JsonAddressBodyMiddleware())
                    ->add(new App\Middleware\AuthMiddleware());;
                    $group->patch(
                        '', 
                        [
                            App\Controller\Api\V1\AddressController::class, 'patchAdresseAction'
                        ],
                    )->setName('address.patch')
                    ->add(new App\Middleware\JsonMiddleware())
                    ->add(new App\Middleware\JsonBodyParserMiddleware())
                    ->add(new App\Middleware\JsonAddressBodyPartialMiddleware())
                    ->add(new App\Middleware\AuthMiddleware());
                    $group->delete(
                        '', 
                        [
                            App\Controller\Api\V1\AddressController::class, 
                            'deleteAdresseAction'
                        ]
                    )->setName('address.delete')
                    ->add(new App\Middleware\AuthMiddleware());
                });
            });
            // Action Images
            $group->group('/images', function($group) {
                $group->get(
                    '', 
                    [App\Controller\Api\V1\ImageController::class, 'getImagesAction']
                )->setName('images.index');
                $group->post(
                    '', 
                    [App\Controller\Api\V1\ImageController::class, 'postImagesAction']
                )->setName('images.post')
                ->add(new App\Middleware\FormImageBodyMiddleware())
                ->add(new App\Middleware\AuthMiddleware());
                $group->group('/{imageid:[0-9]+}', function($group){
                    $group->get(
                        '', 
                        [App\Controller\Api\V1\ImageController::class, 'getImageAction']
                    )->setName('image.index');
                    $group->delete(
                        '', 
                        [App\Controller\Api\V1\ImageController::class, 'deleteImageAction']
                    )->setName('image.delete')
                    ->add(new App\Middleware\AuthMiddleware());
                    // Attributes
                    $group->group('/attributes', function($group){
                        $group->get('', 
                            [
                                App\Controller\Api\V1\ImageController::class, 
                                'getImageAttributesAction'
                            ]
                        )->setName('image.attributes.index');
                        $group->patch('', 
                            [
                                App\Controller\Api\V1\ImageController::class, 
                                'patchImageAttributesAction'
                            ]
                        )->setName('image.attributes.patch')
                        ->add(new App\Middleware\JsonMiddleware())
                        ->add(new App\Middleware\JsonBodyParserMiddleware())
                        ->add(new App\Middleware\AuthMiddleware());
                    });
                });
            });
        });
        $group->get(
            '/{nom:[a-zA-Z0-9_-]+}', 
            [App\Controller\Api\V1\SchoolController::class, 'getNameAction']
        )->setName('ecole.name');
        $group->get(
            '/{nom:[a-zA-Z0-9_-]+}/{limit:[0-9]+}', 
            [App\Controller\Api\V1\SchoolController::class, 'getNameLimitAction']
        )->setName('ecole.nomAndLimit');
    });
})->add(new App\Middleware\DatabaseConnectionMiddleware());

