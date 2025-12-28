<?php

declare(strict_types=1);

namespace App\Module\Transaction\Presentation\Controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use App\Module\Transaction\Application\Service\TransferService;
use App\Module\Transaction\Presentation\Request\TransferRequest;

#[Controller]
class TransferController
{
    #[Inject]
    protected TransferService $transferService;

    public function transfer(TransferRequest $request): array
    {
        $this->transferService->transfer($request->validated());

        return [
            'message' => 'Transfer request queued successfully',
            'status' => 'queued',
        ];
    }

    public function sse()
    {
        // TODO: implement sse to return transations status
        return 200;
    }
}
