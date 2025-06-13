<?php

namespace App\OpenApi;

/**
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     type="object",
 *     @OA\Property(property="message", type="string", example="Resource not found"),
 *     @OA\Property(property="code", type="integer", example=404)
 * )
 *
 * @OA\Schema(
 *     schema="ValidationErrorResponse",
 *     type="object",
 *     @OA\Property(property="message", type="string", example="The given data was invalid."),
 *     @OA\Property(
 *         property="errors",
 *         type="object",
 *         @OA\AdditionalProperties(
 *             type="array",
 *             @OA\Items(type="string")
 *         ),
 *         example={"email": {"The email field is required."}}
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="PaginationMeta",
 *     type="object",
 *     @OA\Property(property="current_page", type="integer", example=1),
 *     @OA\Property(property="from", type="integer", example=1),
 *     @OA\Property(property="last_page", type="integer", example=5),
 *     @OA\Property(property="per_page", type="integer", example=15),
 *     @OA\Property(property="to", type="integer", example=15),
 *     @OA\Property(property="total", type="integer", example=75)
 * )
 *
 * @OA\Schema(
 *     schema="PaginationLinks",
 *     type="object",
 *     @OA\Property(property="first", type="string", nullable=true, example="http://localhost:8080/api/products?page=1"),
 *     @OA\Property(property="last", type="string", nullable=true, example="http://localhost:8080/api/products?page=5"),
 *     @OA\Property(property="prev", type="string", nullable=true, example=null),
 *     @OA\Property(property="next", type="string", nullable=true, example="http://localhost:8080/api/products?page=2")
 * )
 *
 * @OA\Schema(
 *     schema="Product",
 *     type="object",
 *     required={"id", "name", "sku", "category", "is_active"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="iPhone 15 Pro"),
 *     @OA\Property(property="sku", type="string", example="APL-IPH15P"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Latest iPhone with titanium design"),
 *     @OA\Property(property="category", type="string", example="Electronics"),
 *     @OA\Property(property="image_url", type="string", nullable=true, example="https://example.com/iphone15.jpg"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-06-12T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-06-12T10:00:00Z")
 * )
 *
 * @OA\Schema(
 *     schema="ProductWithPrice",
 *     allOf={@OA\Schema(ref="#/components/schemas/Product")},
 *     @OA\Property(
 *         property="current_price",
 *         type="object",
 *         @OA\Property(property="price", type="number", format="float", example=1299.99),
 *         @OA\Property(property="currency", type="string", example="PLN"),
 *         @OA\Property(property="source_name", type="string", example="amazon"),
 *         @OA\Property(property="scraped_at", type="string", format="date-time", example="2025-06-12T10:00:00Z")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="Alert",
 *     type="object",
 *     required={"id", "product_id", "email", "condition", "is_active"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="product_id", type="integer", example=1),
 *     @OA\Property(property="email", type="string", format="email", example="user@example.com"),
 *     @OA\Property(property="target_price", type="number", format="float", nullable=true, example=999.99),
 *     @OA\Property(
 *         property="condition",
 *         type="string",
 *         enum={"below", "above", "equals", "percent_drop", "percent_increase"},
 *         example="below"
 *     ),
 *     @OA\Property(property="percent_threshold", type="number", format="float", nullable=true, example=15.0),
 *     @OA\Property(
 *         property="notification_channel",
 *         type="string",
 *         enum={"email", "webhook", "sms", "slack", "discord", "telegram"},
 *         example="email"
 *     ),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="last_triggered_at", type="string", format="date-time", nullable=true, example=null),
 *     @OA\Property(property="trigger_count", type="integer", example=0),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-06-12T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-06-12T10:00:00Z")
 * )
 *
 * @OA\Schema(
 *     schema="AlertWithProduct",
 *     allOf={@OA\Schema(ref="#/components/schemas/Alert")},
 *     @OA\Property(
 *         property="product",
 *         type="object",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="iPhone 15 Pro"),
 *         @OA\Property(property="sku", type="string", example="APL-IPH15P")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="PriceHistory",
 *     type="object",
 *     required={"id", "price", "currency", "is_available", "source_name", "scraped_at"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="price", type="number", format="float", example=1299.99),
 *     @OA\Property(property="previous_price", type="number", format="float", nullable=true, example=1399.99),
 *     @OA\Property(property="currency", type="string", example="PLN"),
 *     @OA\Property(property="is_available", type="boolean", example=true),
 *     @OA\Property(property="price_change", type="number", format="float", nullable=true, example=-100.00),
 *     @OA\Property(property="price_change_percent", type="number", format="float", nullable=true, example=-7.14),
 *     @OA\Property(property="source_name", type="string", example="amazon"),
 *     @OA\Property(property="scraped_at", type="string", format="date-time", example="2025-06-12T10:00:00Z")
 * )
 *
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     required={"id", "name", "email"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-06-12T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-06-12T10:00:00Z")
 * )
 *
 * @OA\Schema(
 *     schema="AlertStatistics",
 *     type="object",
 *     @OA\Property(property="total_alerts", type="integer", example=100),
 *     @OA\Property(property="active_alerts", type="integer", example=85),
 *     @OA\Property(property="triggered_alerts", type="integer", example=45),
 *     @OA\Property(property="trigger_rate", type="number", format="float", example=45.0),
 *     @OA\Property(property="average_triggers_per_alert", type="number", format="float", example=3.5)
 * )
 *
 * @OA\Schema(
 *     schema="RegisterRequest",
 *     type="object",
 *     required={"name", "email", "password", "password_confirmation"},
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *     @OA\Property(property="password", type="string", format="password", example="Password123!"),
 *     @OA\Property(property="password_confirmation", type="string", format="password", example="Password123!")
 * )
 *
 * @OA\Schema(
 *     schema="LoginRequest",
 *     type="object",
 *     required={"email", "password"},
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *     @OA\Property(property="password", type="string", format="password", example="Password123!")
 * )
 *
 * @OA\Schema(
 *     schema="CreateAlertRequest",
 *     type="object",
 *     required={"product_id", "condition", "notification_channel"},
 *     @OA\Property(property="product_id", type="integer", example=1),
 *     @OA\Property(
 *         property="condition",
 *         type="string",
 *         enum={"below", "above", "equals", "percent_drop", "percent_increase"},
 *         example="below"
 *     ),
 *     @OA\Property(
 *         property="target_price",
 *         type="number",
 *         format="float",
 *         description="Required for price-based conditions (below, above, equals)",
 *         example=999.99
 *     ),
 *     @OA\Property(
 *         property="percent_threshold",
 *         type="number",
 *         format="float",
 *         description="Required for percentage-based conditions (percent_drop, percent_increase)",
 *         example=15.0
 *     ),
 *     @OA\Property(
 *         property="notification_channel",
 *         type="string",
 *         enum={"email", "webhook", "sms", "slack", "discord", "telegram"},
 *         example="email"
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="UpdateAlertRequest",
 *     type="object",
 *     @OA\Property(property="target_price", type="number", format="float", example=1100.00),
 *     @OA\Property(property="percent_threshold", type="number", format="float", example=20.0),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(
 *         property="notification_channel",
 *         type="string",
 *         enum={"email", "webhook", "sms", "slack", "discord", "telegram"},
 *         example="email"
 *     ),
 *     @OA\Property(
 *         property="condition",
 *         type="string",
 *         enum={"below", "above", "equals", "percent_drop", "percent_increase"},
 *         example="below"
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="AuthResponse",
 *     type="object",
 *     @OA\Property(
 *         property="data",
 *         type="object",
 *         @OA\Property(property="user", ref="#/components/schemas/User"),
 *         @OA\Property(property="token", type="string", example="1|laravel_sanctum_token_example")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="ProductResource",
 *     type="object",
 *     @OA\Property(property="data", ref="#/components/schemas/Product")
 * )
 *
 * @OA\Schema(
 *     schema="ProductWithPriceResource",
 *     type="object",
 *     @OA\Property(property="data", ref="#/components/schemas/ProductWithPrice")
 * )
 *
 * @OA\Schema(
 *     schema="ProductCollection",
 *     type="object",
 *     @OA\Property(
 *         property="data",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Product")
 *     ),
 *     @OA\Property(property="links", ref="#/components/schemas/PaginationLinks"),
 *     @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta")
 * )
 *
 * @OA\Schema(
 *     schema="AlertResource",
 *     type="object",
 *     @OA\Property(property="data", ref="#/components/schemas/AlertWithProduct")
 * )
 *
 * @OA\Schema(
 *     schema="AlertCollection",
 *     type="object",
 *     @OA\Property(
 *         property="data",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/AlertWithProduct")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="PriceHistoryResource",
 *     type="object",
 *     @OA\Property(property="data", ref="#/components/schemas/PriceHistory")
 * )
 *
 * @OA\Schema(
 *     schema="PriceHistoryCollection",
 *     type="object",
 *     @OA\Property(
 *         property="data",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/PriceHistory")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="UserResource",
 *     type="object",
 *     @OA\Property(property="data", ref="#/components/schemas/User")
 * )
 *
 * @OA\Schema(
 *     schema="AlertTriggerCheckResponse",
 *     type="object",
 *     @OA\Property(property="should_trigger", type="boolean", example=true),
 *     @OA\Property(property="current_price", type="number", format="float", example=899.99),
 *     @OA\Property(property="target_price", type="number", format="float", nullable=true, example=1000.00),
 *     @OA\Property(property="previous_price", type="number", format="float", nullable=true, example=1100.00),
 *     @OA\Property(property="condition", type="string", example="below"),
 *     @OA\Property(property="message", type="string", example="Alert conditions met")
 * )
 *
 * @OA\Schema(
 *     schema="AlertTriggerResponse",
 *     type="object",
 *     @OA\Property(property="message", type="string", example="Alert triggered successfully"),
 *     @OA\Property(
 *         property="data",
 *         type="object",
 *         @OA\Property(property="trigger_count", type="integer", example=1)
 *     )
 * )
 * @OA\Schema(
 *     schema="ProductSearchRequest",
 *     type="object",
 *     @OA\Property(property="search", type="string", example="iPhone"),
 *     @OA\Property(property="category", type="string", example="Electronics"),
 *     @OA\Property(property="per_page", type="integer", minimum=1, maximum=100, example=15),
 *     @OA\Property(property="page", type="integer", minimum=1, example=1),
 *     @OA\Property(property="include", type="string", example="current_price,alerts"),
 *     @OA\Property(property="sort_by", type="string", enum={"name", "created_at", "updated_at", "price"}, example="created_at"),
 *     @OA\Property(property="sort_direction", type="string", enum={"asc", "desc"}, example="desc"),
 *     @OA\Property(property="is_active", type="boolean", example=true)
 * )
 *
 * @OA\Schema(
 *     schema="PriceHistoryFilterRequest",
 *     type="object",
 *     @OA\Property(property="from", type="string", format="date", example="2025-06-01"),
 *     @OA\Property(property="to", type="string", format="date", example="2025-06-12"),
 *     @OA\Property(property="source", type="string", example="amazon"),
 *     @OA\Property(property="limit", type="integer", minimum=1, maximum=1000, example=100)
 * )
 *
 * @OA\Schema(
 *     schema="EmptyResponse",
 *     type="object",
 *     description="No content response"
 * )
 */
class Schemas
{
    // This class is only for OpenAPI documentation schemas
}
