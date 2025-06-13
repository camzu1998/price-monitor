<?php

namespace App\OpenApi;

use OpenApi\Annotations as OA;

/**
 * @OA\OpenApi(
 *     @OA\Info(
 *         version="1.0.0",
 *         title="Price Monitor API",
 *         description="API for monitoring product prices across different e-commerce platforms",
 *         @OA\Contact(
 *             name="Price Monitor Support",
 *             email="support@pricemonitor.com"
 *         ),
 *         @OA\License(
 *             name="MIT",
 *             url="https://opensource.org/licenses/MIT"
 *         )
 *     ),
 *     @OA\Server(
 *         url=L5_SWAGGER_CONST_HOST,
 *         description="API Server"
 *     ),
 *     @OA\Tag(
 *         name="Authentication",
 *         description="User authentication endpoints"
 *     ),
 *     @OA\Tag(
 *         name="Products",
 *         description="Product management endpoints"
 *     ),
 *     @OA\Tag(
 *         name="Price History",
 *         description="Price history tracking endpoints"
 *     ),
 *     @OA\Tag(
 *         name="Alerts",
 *         description="Price alert management endpoints"
 *     )
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="apiKey",
 *     description="Enter token in format (Bearer <token>)",
 *     name="Authorization",
 *     in="header"
 * )
 */
class OpenApiInfo
{
    // This class is only for OpenAPI documentation
}
