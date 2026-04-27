<?php

namespace App\Http\Controllers\Api;

use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Campaigns',
    description: 'API Endpoints for Campaign Management'
)]
class CampaignManagementController extends Controller
{
    #[OA\Get(
        path: '/api/campaigns',
        operationId: 'campaignsIndex',
        tags: ['Campaigns'],
        summary: 'List campaigns (paginated)',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Campaigns retrieved successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated',
                content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedResponse')
            ),
        ]
    )]
    public function index(Request $request)
    {
        $campaigns = Campaign::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return JsonResponseHelper::success('Campaigns retrieved successfully', [
            'campaigns' => $campaigns,
        ]);
    }

    #[OA\Post(
        path: '/api/campaigns',
        operationId: 'campaignsStore',
        tags: ['Campaigns'],
        summary: 'Create a campaign',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: [
                    'name',
                    'platform',
                    'impressions',
                    'clicks',
                    'conversions',
                    'spend',
                    'revenue',
                    'start_date',
                    'end_date',
                ],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Spring Sale'),
                    new OA\Property(
                        property: 'platform',
                        type: 'string',
                        enum: ['Facebook', 'Google', 'TikTok', 'LinkedIn', 'Instagram'],
                        example: 'Google'
                    ),
                    new OA\Property(property: 'impressions', type: 'integer', minimum: 0, example: 10000),
                    new OA\Property(property: 'clicks', type: 'integer', minimum: 0, example: 450),
                    new OA\Property(property: 'conversions', type: 'integer', minimum: 0, example: 25),
                    new OA\Property(property: 'spend', type: 'number', format: 'float', minimum: 0, example: 1250.5),
                    new OA\Property(property: 'revenue', type: 'number', format: 'float', minimum: 0, example: 4100.0),
                    new OA\Property(property: 'start_date', type: 'string', format: 'date', example: '2026-03-01'),
                    new OA\Property(property: 'end_date', type: 'string', format: 'date', example: '2026-03-31'),
                    new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'Targeting broad audience'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Campaign created successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated',
                content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedResponse')
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')
            ),
        ]
    )]
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'platform' => 'required|in:Facebook,Google,TikTok,LinkedIn,Instagram',
            'impressions' => 'required|integer|min:0',
            'clicks' => 'required|integer|min:0',
            'conversions' => 'required|integer|min:0',
            'spend' => 'required|numeric|min:0',
            'revenue' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'notes' => 'nullable|string',
        ]);

        $campaign = Campaign::create([
            ...$validated,
            'user_id' => $request->user()->id,
        ]);

        return JsonResponseHelper::success('Campaign created successfully', [
            'campaign' => $campaign->load('user'),
        ], 201);
    }

    #[OA\Get(
        path: '/api/campaigns/{campaign}',
        operationId: 'campaignsShow',
        tags: ['Campaigns'],
        summary: 'Get a campaign',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'campaign', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Campaign retrieved successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated',
                content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedResponse')
            ),
            new OA\Response(
                response: 404,
                description: 'Not found',
                content: new OA\JsonContent(ref: '#/components/schemas/NotFoundResponse')
            ),
        ]
    )]
    public function show(Request $request, Campaign $campaign)
    {
        return JsonResponseHelper::success('Campaign retrieved successfully', [
            'campaign' => $campaign,
        ]);
    }

    #[OA\Put(
        path: '/api/campaigns/{campaign}',
        operationId: 'campaignsUpdate',
        tags: ['Campaigns'],
        summary: 'Update a campaign',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'campaign', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Spring Sale - Updated'),
                    new OA\Property(
                        property: 'platform',
                        type: 'string',
                        enum: ['Facebook', 'Google', 'TikTok', 'LinkedIn', 'Instagram'],
                        example: 'Facebook'
                    ),
                    new OA\Property(property: 'impressions', type: 'integer', minimum: 0, example: 12000),
                    new OA\Property(property: 'clicks', type: 'integer', minimum: 0, example: 500),
                    new OA\Property(property: 'conversions', type: 'integer', minimum: 0, example: 30),
                    new OA\Property(property: 'spend', type: 'number', format: 'float', minimum: 0, example: 1500.0),
                    new OA\Property(property: 'revenue', type: 'number', format: 'float', minimum: 0, example: 4600.0),
                    new OA\Property(property: 'start_date', type: 'string', format: 'date', example: '2026-03-01'),
                    new OA\Property(property: 'end_date', type: 'string', format: 'date', example: '2026-03-31'),
                    new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'Adjusted targeting'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Campaign updated successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated',
                content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedResponse')
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')
            ),
            new OA\Response(
                response: 404,
                description: 'Not found',
                content: new OA\JsonContent(ref: '#/components/schemas/NotFoundResponse')
            ),
        ]
    )]
    public function update(Request $request, Campaign $campaign)
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'platform' => 'in:Facebook,Google,TikTok,LinkedIn,Instagram',
            'impressions' => 'integer|min:0',
            'clicks' => 'integer|min:0',
            'conversions' => 'integer|min:0',
            'spend' => 'numeric|min:0',
            'revenue' => 'numeric|min:0',
            'start_date' => 'date',
            'end_date' => 'date',
            'notes' => 'nullable|string',
        ]);

        $campaign->update($validated);

        return JsonResponseHelper::success('Campaign updated successfully', [
            'campaign' => $campaign,
        ]);
    }

    #[OA\Delete(
        path: '/api/campaigns/{campaign}',
        operationId: 'campaignsDestroy',
        tags: ['Campaigns'],
        summary: 'Delete a campaign',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'campaign', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Campaign deleted successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated',
                content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedResponse')
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden'
            ),
            new OA\Response(
                response: 404,
                description: 'Not found',
                content: new OA\JsonContent(ref: '#/components/schemas/NotFoundResponse')
            ),
        ]
    )]
    public function destroy(Request $request, Campaign $campaign)
    {
        $this->authorize('delete', $campaign);
        $campaign->delete();

        return JsonResponseHelper::success('Campaign deleted successfully');
    }

    #[OA\Get(
        path: '/api/campaigns/bulk-upload/template',
        operationId: 'campaignsBulkUploadTemplate',
        tags: ['Campaigns'],
        summary: 'Download CSV template for bulk upload',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'CSV file',
                content: new OA\MediaType(
                    mediaType: 'text/csv',
                    schema: new OA\Schema(type: 'string', format: 'binary')
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated',
                content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedResponse')
            ),
        ]
    )]
    public function bulkUploadTemplate(Request $request)
    {
        $filename = 'campaigns-bulk-upload-template.csv';

        return response()->streamDownload(function () {
            $output = fopen('php://output', 'w');

            fputcsv($output, [
                'name',
                'platform',
                'impressions',
                'clicks',
                'conversions',
                'spend',
                'revenue',
                'start_date',
                'end_date',
            ]);

            fputcsv($output, [
                'Spring Sale',
                'Google',
                10000,
                450,
                25,
                1250.50,
                4600.00,
                '2026-01-01',
                '2026-01-31',
            ]);

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Access-Control-Expose-Headers' => 'Content-Disposition',
        ]);
    }

    #[OA\Post(
        path: '/api/campaigns/bulk-upload',
        operationId: 'campaignsBulkUpload',
        tags: ['Campaigns'],
        summary: 'Bulk upload campaigns (CSV)',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    type: 'object',
                    required: ['file'],
                    properties: [
                        new OA\Property(property: 'file', type: 'string', format: 'binary'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Campaigns uploaded successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated',
                content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedResponse')
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')
            ),
        ]
    )]
    public function bulkUpload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('file');
        $rows = array_map('str_getcsv', file($file->path()));
        $header = array_shift($rows);

        $campaigns = [];
        foreach ($rows as $row) {
            if (count($row) >= 8) {
                $campaigns[] = [
                    'user_id' => $request->user()->id,
                    'name' => $row[0],
                    'platform' => $row[1],
                    'impressions' => (int) $row[2],
                    'clicks' => (int) $row[3],
                    'conversions' => (int) $row[4],
                    'spend' => (float) $row[5],
                    'revenue' => (float) $row[6],
                    'start_date' => $row[7],
                    'end_date' => $row[8] ?? $row[7],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        Campaign::insert($campaigns);

        return JsonResponseHelper::success('Campaigns uploaded successfully', [
            'count' => count($campaigns),
        ], 201);
    }

    #[OA\Get(
        path: '/api/campaigns/summary/{campaign}',
        operationId: 'campaignsSummary',
        tags: ['Campaigns'],
        summary: 'Get campaign summary and metrics',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'campaign', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Campaign summary retrieved successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated',
                content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedResponse')
            ),
            new OA\Response(
                response: 404,
                description: 'Not found',
                content: new OA\JsonContent(ref: '#/components/schemas/NotFoundResponse')
            ),
        ]
    )]
    public function summary(Request $request, Campaign $campaign)
    {
        return JsonResponseHelper::success('Campaign summary retrieved successfully', [
            'campaign' => $campaign,
            'metrics' => $campaign->getMetrics(),
        ]);
    }
}
