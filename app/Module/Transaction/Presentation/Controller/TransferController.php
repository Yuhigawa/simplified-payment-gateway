<?php

declare(strict_types=1);

namespace App\Module\Transaction\Presentation\Controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;

use App\Module\Transaction\Application\Service\TransferService;
use App\Module\Transaction\Presentation\Request\TransferRequest;
use App\Module\Transaction\Presentation\Resource\TransferResource;

#[Controller]
class TransferController
{
    #[Inject]
    protected readonly TransferService $transferService;

    public function transfer(TransferRequest $request): TransferResource
    {
        $transaction = $this->transferService->transfer($request->validated());

        return TransferResource::make($transaction);
    }   
}
