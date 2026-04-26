<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Ads  Optimization API',
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: 'Enter your Bearer token below'
)]
#[OA\Schema(
    schema: 'UnauthorizedResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: false),
        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.'),
        new OA\Property(property: 'timestamp', type: 'string', format: 'date-time', example: '2026-03-10T12:00:00+00:00'),
    ]
)]
#[OA\Schema(
    schema: 'ValidationErrorResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: false),
        new OA\Property(property: 'message', type: 'string', example: 'Invalid Parameter'),
        new OA\Property(property: 'timestamp', type: 'string', format: 'date-time', example: '2026-03-10T12:00:00+00:00'),
        new OA\Property(
            property: 'errors',
            type: 'object',
            description: 'Detail error per field',
            example: ['email' => ['The email field is required.'], 'password' => ['The password must be at least 8 characters.']]
        ),
    ]
)]
#[OA\Schema(
    schema: 'SuccessResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'message', type: 'string', example: 'Operation successful'),
        new OA\Property(property: 'redirect', type: 'string', nullable: true, example: null),
        new OA\Property(property: 'timestamp', type: 'string', format: 'date-time', example: '2026-03-10T12:00:00+00:00'),
        new OA\Property(property: 'data', type: 'object', nullable: true),
    ]
)]
#[OA\Schema(
    schema: 'NotFoundResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: false),
        new OA\Property(property: 'message', type: 'string', example: 'Resource not found.'),
        new OA\Property(property: 'timestamp', type: 'string', format: 'date-time', example: '2026-03-10T12:00:00+00:00'),
    ]
)]
abstract class Controller
{
    //
}
