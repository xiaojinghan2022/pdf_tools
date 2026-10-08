<?php

namespace OCA\PdfTools\Controller;

use OCA\PdfTools\Service\StirlingService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\Files\IRootFolder;
use OCP\IRequest;

class ToolController extends Controller
{
    public function __construct(
        string $appName,
        IRequest $request,
        private IRootFolder $rootFolder,
        private StirlingService $stirling,
    ) {
        parent::__construct($appName, $request);
    }

    public function compress(
        int $fileId,
        string $stirlingUrl,
    ): DataResponse {
        $userId = $this->userId();

        if ($userId === null) {
            return new DataResponse(
                ['error' => 'Not authenticated'],
                401
            );
        }

        $files = $this->rootFolder->getUserFolder($userId);

        $nodes = $files->getById($fileId);

        if (count($nodes) !== 1) {
            return new DataResponse(
                ['error' => 'File not found'],
                404
            );
        }

        $file = $nodes[0];

        if ($file->getMimeType() !== 'application/pdf') {
            return new DataResponse(
                ['error' => 'Only PDF files are supported'],
                400
            );
        }

        $pdf = $this->stirling->compress(
            $file,
            $stirlingUrl
        );

        $name = pathinfo(
            $file->getName(),
            PATHINFO_FILENAME
        ) . '_compressed.pdf';

        $parent = $file->getParent();

        $newFile = $parent->newFile($name, $pdf);

        return new DataResponse([
            'success' => true,
            'fileId' => $newFile->getId(),
            'name' => $newFile->getName(),
        ]);
    }
}