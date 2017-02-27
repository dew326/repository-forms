<?php
/**
 * This file is part of the eZ RepositoryForms package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */
namespace EzSystems\RepositoryFormsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use EzSystems\PlatformUIBundle\Components\Browse;
use EzSystems\PlatformUIBundle\Components\Search;
use EzSystems\PlatformUIBundle\Components\Trash;
use eZ\Publish\API\Repository\Values\Content\Location;
use EzSystems\PlatformUIBundle\Controller\Controller;

class ProtoController extends Controller
{

    public function createFolderAction()
    {

        return $this->render('EzSystemsRepositoryFormsBundle:Content:create_folder.html.twig');
    }
}
