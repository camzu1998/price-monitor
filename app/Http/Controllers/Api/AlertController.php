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

    /**
     * @OA\Get(
     *     path="/api/alerts",
     *     operationId="getAlerts",
     *     tags={"Alerts"},
     *     summary="Get user alerts",
     *     description="Get all alerts for the authenticated user",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User alerts list",
     *         @OA\JsonContent(ref="#/components/schemas/AlertCollection")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $alerts = $this->alertService->getAlertsForUser($request->user()->email);

        return AlertResource::collection($alerts);
    }

    /**
     * @OA\Post(
     *     path="/api/alerts",
     *     operationId="createAlert",
     *     tags={"Alerts"},
     *     summary="Create price alert",
     *     description="Create a new price alert for a product",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CreateAlertRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Alert created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/AlertResource")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     )
     * )
     */
    public function store(CreateAlertRequest $request): AlertResource
    {
        $data = $request->validated();
        $data['email'] = $request->user()->email;

        $alertDTO = AlertDTO::fromRequest($data);
        $alert = $this->alertService->createAlert($alertDTO);

        return new AlertResource($alert);
    }

    /**
     * @OA\Get(
     *     path="/api/alerts/{alert}",
     *     operationId="getAlert",
     *     tags={"Alerts"},
     *     summary="Get alert details",
     *     description="Get details of a specific alert",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="alert",
     *         in="path",
     *         description="Alert ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Alert details",
     *         @OA\JsonContent(ref="#/components/schemas/AlertResource")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Not the owner",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Alert not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
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

    /**
     * @OA\Put(
     *     path="/api/alerts/{alert}",
     *     operationId="updateAlert",
     *     tags={"Alerts"},
     *     summary="Update alert",
     *     description="Update an existing price alert",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="alert",
     *         in="path",
     *         description="Alert ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateAlertRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Alert updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/AlertResource")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Not the owner",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     )
     * )
     */
    public function update(UpdateAlertRequest $request, PriceAlert $alert): AlertResource
    {
        $updatedAlert = $this->alertService->updateAlert($alert, $request->validated());

        return new AlertResource($updatedAlert);
    }

    /**
     * @OA\Delete(
     *     path="/api/alerts/{alert}",
     *     operationId="deleteAlert",
     *     tags={"Alerts"},
     *     summary="Delete alert",
     *     description="Delete a price alert",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="alert",
     *         in="path",
     *         description="Alert ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Alert deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Not the owner",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function destroy(PriceAlert $alert): JsonResponse
    {
        $this->alertService->deleteAlert($alert);

        return response()->json(null, 204);
    }

    /**
     * @OA\Get(
     *     path="/api/alerts/{alert}/should-trigger",
     *     operationId="checkAlertTrigger",
     *     tags={"Alerts"},
     *     summary="Check if alert should trigger",
     *     description="Check if the alert conditions are met",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="alert",
     *         in="path",
     *         description="Alert ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Alert trigger check result",
     *         @OA\JsonContent(ref="#/components/schemas/AlertTriggerCheckResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Not the owner",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function shouldTrigger(PriceAlert $alert): JsonResponse
    {
        $result = $this->alertService->shouldTrigger($alert);

        return response()->json($result);
    }

    /**
     * @OA\Post(
     *     path="/api/alerts/{alert}/trigger",
     *     operationId="triggerAlert",
     *     tags={"Alerts"},
     *     summary="Manually trigger alert",
     *     description="Manually trigger an alert",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="alert",
     *         in="path",
     *         description="Alert ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Alert triggered successfully",
     *         @OA\JsonContent(ref="#/components/schemas/AlertTriggerResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Not the owner",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/alerts/statistics",
     *     operationId="getAlertStatistics",
     *     tags={"Alerts"},
     *     summary="Get alert statistics",
     *     description="Get system-wide alert statistics",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Alert statistics",
     *         @OA\JsonContent(ref="#/components/schemas/AlertStatistics")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function statistics(): JsonResponse
    {
        $statistics = $this->alertService->getAlertStatistics();

        return response()->json($statistics);
    }
}
