<?php

namespace App\Http\Controllers\Api;

use App\DTOs\AlertDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateAlertRequest;
use App\Http\Requests\UpdateAlertRequest;
use App\Http\Resources\AlertResource;
use App\Models\PriceAlert;
use App\Services\AlertService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AlertController extends Controller
{
    public function __construct(
        private readonly AlertService $alertService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $alerts = $this->alertService->getAlertsForUser($request->user()->email);

        return AlertResource::collection($alerts);
    }

    public function store(CreateAlertRequest $request): AlertResource
    {
        $data = $request->validated();
        $data['email'] = $request->user()->email;

        $alertDTO = AlertDTO::fromRequest($data);
        $alert = $this->alertService->createAlert($alertDTO);

        return new AlertResource($alert);
    }

    public function show(int $id): AlertResource|JsonResponse
    {
        $alert = PriceAlert::find($id);

        if (!$alert) {
            return response()->json([
                'message' => 'Alert not found'
            ], 404);
        }

        $alert->load('product');
        return new AlertResource($alert);
    }

    public function update(UpdateAlertRequest $request, PriceAlert $alert): AlertResource
    {
        $updatedAlert = $this->alertService->updateAlert($alert, $request->validated());

        return new AlertResource($updatedAlert);
    }

    public function destroy(PriceAlert $alert): JsonResponse
    {
        $this->alertService->deleteAlert($alert);

        return response()->json(null, 204);
    }

    public function shouldTrigger(PriceAlert $alert): JsonResponse
    {
        $result = $this->alertService->shouldTrigger($alert);

        return response()->json($result);
    }

    public function trigger(PriceAlert $alert): JsonResponse
    {
        $result = $this->alertService->triggerAlert($alert);

        return response()->json([
            'message' => $result['message'],
            'data' => [
                'trigger_count' => $result['trigger_count']
            ]
        ]);
    }

    public function statistics(): JsonResponse
    {
        $statistics = $this->alertService->getAlertStatistics();

        return response()->json($statistics);
    }
}
