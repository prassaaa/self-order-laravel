<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="Self Order Laravel API",
 *     version="1.0.0",
 *     description="A comprehensive self-ordering system API for restaurants and cafes",
 *     @OA\Contact(
 *         email="support@example.com",
 *         name="API Support"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="API Server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 *
 * @OA\Tag(
 *     name="Authentication",
 *     description="User authentication endpoints"
 * )
 *
 * @OA\Tag(
 *     name="Menus",
 *     description="Menu management endpoints"
 * )
 *
 * @OA\Tag(
 *     name="Categories",
 *     description="Category management endpoints"
 * )
 *
 * @OA\Tag(
 *     name="Orders",
 *     description="Order management endpoints"
 * )
 *
 * @OA\Tag(
 *     name="Payments",
 *     description="Payment processing endpoints"
 * )
 *
 * @OA\Tag(
 *     name="Dashboard",
 *     description="Dashboard and analytics endpoints"
 * )
 *
 * @OA\Tag(
 *     name="Reports",
 *     description="Reporting and export endpoints"
 * )
 *
 * @OA\Tag(
 *     name="Print",
 *     description="Printing and receipt endpoints"
 * )
 */
abstract class Controller
{
    //
}
