<?php
/**
 * This file is part of the eZ RepositoryForms package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */
namespace EzSystems\RepositoryFormsBundle\Controller;

use eZ\Bundle\EzPublishCoreBundle\Controller;
use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\LocationService;
use EzSystems\RepositoryForms\Data\Content\CreateContentDraftData;
use EzSystems\RepositoryForms\Data\Mapper\ContentCreateMapper;
use EzSystems\RepositoryForms\Form\ActionDispatcher\ActionDispatcherInterface;
use EzSystems\RepositoryForms\Form\Type\Content\ContentDraftCreateType;
use EzSystems\RepositoryForms\Form\Type\Content\ContentEditType;
use Symfony\Component\HttpFoundation\Request;

class ContentEditController extends Controller
{
    /**
     * @var ContentTypeService
     */
    private $contentTypeService;

    /**
     * @var ContentService
     */
    private $contentService;

    /**
     * @var LocationService
     */
    private $locationService;

    /**
     * @var ActionDispatcherInterface
     */
    private $contentActionDispatcher;

    /**
     * @var string
     */
    private $pagelayout;

    public function __construct(
        ContentTypeService $contentTypeService,
        ContentService $contentService,
        LocationService $locationService,
        ActionDispatcherInterface $contentActionDispatcher
    ) {
        $this->contentTypeService = $contentTypeService;
        $this->locationService = $locationService;
        $this->contentActionDispatcher = $contentActionDispatcher;
        $this->contentService = $contentService;
    }

    /**
     * Displays and processes a content creation form. Showing the form does not create a draft in the repository.
     *
     * @param int $contentTypeIdentifier ContentType id to create
     * @param string $language Language code to create the content in (eng-GB, ger-DE, ...))
     * @param int $parentLocationId Location the content should be a child of
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createWithoutDraftAction($contentTypeIdentifier, $language, $parentLocationId, Request $request)
    {
        $contentType = $this->contentTypeService->loadContentTypeByIdentifier($contentTypeIdentifier);
        $data = (new ContentCreateMapper())->mapToFormData($contentType, [
            'mainLanguageCode' => $language,
            'parentLocation' => $this->locationService->newLocationCreateStruct($parentLocationId),
        ]);
        $form = $this->createForm(ContentEditType::class, $data, ['languageCode' => $language]);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->contentActionDispatcher->dispatchFormAction($form, $data, $form->getClickedButton()->getName());
            if ($response = $this->contentActionDispatcher->getResponse()) {
                return $response;
            }
        }

        return $this->render('EzSystemsRepositoryFormsBundle:Content:content_edit.html.twig', [
            'form' => $form->createView(),
            'languageCode' => $language,
            'pagelayout' => $this->pagelayout,
        ]);
    }

    /**
     * Displays a draft creation form that creates a content draft from an existing content.
     *
     * @param mixed $contentId
     * @param int $fromVersionNo
     * @param string $fromLanguage
     * @param string $toLanguage
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createContentDraftAction($contentId, $fromVersionNo, $fromLanguage, $toLanguage, Request $request)
    {
        $createContentDraft = new CreateContentDraftData();

        if ($contentId !== null) {
            $createContentDraft->contentId = $contentId;

            $contentInfo = $this->contentService->loadContentInfo($contentId);
            $createContentDraft->fromVersionNo = $fromVersionNo ?: $contentInfo->currentVersionNo;
            $createContentDraft->fromLanguage = $fromLanguage ?: $contentInfo->mainLanguageCode;
        }

        $form = $this->createForm(
            ContentDraftCreateType::class,
            $createContentDraft
        );

        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->contentService->createContentDraft(
                $contentInfo,
                $this->contentService->loadVersionInfo($contentInfo, $fromVersionNo)
            );
        }

        return $this->render('@EzSystemsRepositoryForms/Content/content_create_draft.html.twig', [
            'form' => $form->createView(),
            //'languageCode' => $language,
            'pagelayout' => $this->pagelayout,
        ]);
    }

    /**
     * @param string $pagelayout
     * @return ContentEditController
     */
    public function setPagelayout($pagelayout)
    {
        $this->pagelayout = $pagelayout;

        return $this;
    }
}
