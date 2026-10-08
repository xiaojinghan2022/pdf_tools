<?php

namespace OCA\PdfTools\Controller;

use OCA\PdfTools\Service\StirlingClient;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\Files\File;
use OCP\Files\IRootFolder;
use OCP\IRequest;
use OCP\IUserSession;
use OCP\IUserManager;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\ApiRoute;
use Throwable;
use OCP\IL10N;

class ApiController extends Controller
{
    public function __construct(
        string $appName,
        IRequest $request,
        private StirlingClient $stirlingClient,
        private IRootFolder $rootFolder,
        private IUserSession $userSession,
        private IL10N $l10n,
    ) {
        parent::__construct($appName, $request);
    }

    #[NoAdminRequired]
    #[ApiRoute(
        verb: 'POST',
        url: '/api/v1/pdf/compress/{fileId}'
    )]
    public function compress(
        int $fileId,
    ): DataResponse {
        try {
            $user = $this->userSession->getUser();

            if ($user === null) {
                return new DataResponse(
                    [
                        'version' => 0.1,
                        'tooltip' => $this->l10n->t(
                            'You must be logged in.'
                        ),
                    ],
                    401
                );
            }

            $userFolder = $this->rootFolder->getUserFolder(
                $user->getUID()
            );

            $nodes = $userFolder->getById($fileId);

            if ($nodes === []) {
                return new DataResponse(
                    [
                        'version' => 0.1,
                        'tooltip' => $this->l10n->t(
                            'File not found.'
                        ),
                    ],
                    404
                );
            }

            $node = $nodes[0];

            if (!$node instanceof File) {
                return new DataResponse(
                    [
                        'version' => 0.1,
                        'tooltip' => $this->l10n->t(
                            'The selected item is not a file.'
                        ),
                    ],
                    400
                );
            }

            if ($node->getMimeType() !== 'application/pdf') {
                return new DataResponse(
                    [
                        'version' => 0.1,
                        'tooltip' => $this->l10n->t(
                            'The selected file is not a PDF.'
                        ),
                    ],
                    400
                );
            }

            $result = $this->stirlingClient->compress(
                $node,
                5
            );

            $originalName = $node->getName();
            $pathInfo = pathinfo($originalName);

            $baseName = $pathInfo['filename'] ?? $originalName;

            $outputName = $baseName . '-compressed.pdf';

            $parent = $node->getParent();

            $outputFile = $parent->newFile(
                $outputName,
                $result
            );

            return new DataResponse([
                'version' => 0.1,
                'tooltip' => $this->l10n->t(
                    'Compressed PDF created: %s',
                    [$outputFile->getName()]
                ),
            ]);

        } catch (Throwable $e) {
            return new DataResponse(
                [
                    'version' => 0.1,
                    'tooltip' => $this->l10n->t(
                        'PDF compression failed: %s',
                        [$e->getMessage()]
                    ),
                ],
                500
            );
        }
    }
}