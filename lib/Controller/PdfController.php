<?php

namespace OCA\PdfTools\Controller;

use OCA\PdfTools\Service\StirlingClient;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\Files\File;
use OCP\Files\IRootFolder;
use OCP\IUserSession;
use OCP\IRequest;
use Throwable;

class PdfController extends Controller
{
    public function __construct(
        string $appName,
        IRequest $request,
        private StirlingClient $stirlingClient,
        private IRootFolder $rootFolder,
        private IUserSession $userSession,
    ) {
        parent::__construct($appName, $request);
    }

    /**
     * @NoCSRFRequired
     */
    public function compress(
        int $fileId,
        int $optimizeLevel = 5,
    ): JSONResponse {
        try {
            $user = $this->userSession->getUser();

            if ($user === null) {
                return new JSONResponse([
                    'success' => false,
                    'error' => 'User is not logged in.',
                ], 401);
            }

            $userFolder = $this->rootFolder->getUserFolder(
                $user->getUID()
            );

            $node = $userFolder->getById($fileId);

            if (!$node instanceof File) {
                return new JSONResponse([
                    'success' => false,
                    'error' => 'File not found.',
                ], 404);
            }

            if ($node->getMimeType() !== 'application/pdf') {
                return new JSONResponse([
                    'success' => false,
                    'error' => 'The selected file is not a PDF.',
                ], 400);
            }

            $result = $this->stirlingClient->compress(
                $node,
                $optimizeLevel
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

            return new JSONResponse([
                'success' => true,
                'fileId' => $outputFile->getId(),
                'fileName' => $outputFile->getName(),
            ]);

        } catch (Throwable $e) {
            return new JSONResponse([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}