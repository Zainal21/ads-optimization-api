<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CampaignAnalysisService;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Analyses',
    description: 'API Endpoints for Campaign Analysis'
)]
class CampaignAnalysisController extends Controller
{
    public function __construct(
        private readonly CampaignAnalysisService $campaignAnalysisService,
    ) {}

    #[OA\Post(
        path: '/api/analyze',
        operationId: 'analysesAnalyze',
        tags: ['Analyses'],
        summary: 'Analyze campaigns using AI and store analysis',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['campaign_ids'],
                properties: [
                    new OA\Property(
                        property: 'campaign_ids',
                        type: 'array',
                        minItems: 1,
                        items: new OA\Items(type: 'integer', example: 1)
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Analysis completed successfully',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Analysis completed successfully'),
                        new OA\Property(property: 'analysis', type: 'object'),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'No campaigns found',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'No campaigns found'),
                    ]
                )
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
    public function analyze(Request $request)
    {
        $validated = $request->validate([
            'campaign_ids' => 'required|array|min:1',
            'campaign_ids.*' => 'integer|exists:campaigns,id',
        ]);

        $aiProvider = config('ai.default_provider', 'anthropic');

        $analysis = $this->campaignAnalysisService->createAnalysisForUser(
            $request->user()->id,
            $validated['campaign_ids'],
            $aiProvider
        );

        if ($analysis === null) {
            return response()->json([
                'message' => 'No campaigns found',
            ], 404);
        }

        return response()->json([
            'message' => 'Analysis completed successfully',
            'analysis' => $analysis,
        ], 201);
    }

    #[OA\Get(
        path: '/api/analyses',
        operationId: 'analysesIndex',
        tags: ['Analyses'],
        summary: 'List analyses (paginated)',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Analyses list',
                content: new OA\JsonContent(type: 'object')
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
        $analyses = $this->campaignAnalysisService->listAnalysesForUser($request->user()->id);

        return response()->json($analyses);
    }

    #[OA\Get(
        path: '/api/analyses/{id}',
        operationId: 'analysesShow',
        tags: ['Analyses'],
        summary: 'Get an analysis with its campaigns',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Analysis retrieved',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'analysis', type: 'object'),
                        new OA\Property(property: 'campaigns', type: 'array', items: new OA\Items(type: 'object')),
                    ]
                )
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
    public function show(Request $request, int $id)
    {
        $analysis = $this->campaignAnalysisService->getAnalysisForUserOrNull($request->user()->id, $id);

        if ($analysis === null) {
            return response()->json([
                'message' => 'Not found',
            ], 404);
        }

        return response()->json([
            'analysis' => $analysis,
            'campaigns' => $analysis->campaigns(),
        ]);
    }

    #[OA\Get(
        path: '/api/analyses/comparison',
        operationId: 'analysesCompare',
        tags: ['Analyses'],
        summary: 'Compare analyses by IDs',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'analysis_ids',
                in: 'query',
                required: true,
                schema: new OA\Schema(
                    type: 'array',
                    minItems: 2,
                    items: new OA\Items(type: 'integer', example: 1)
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Comparison generated',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'analyses', type: 'array', items: new OA\Items(type: 'object')),
                        new OA\Property(property: 'comparison', type: 'array', items: new OA\Items(type: 'object')),
                    ]
                )
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
    public function compare(Request $request)
    {
        $request->validate([
            'analysis_ids' => 'required|array|min:2',
            'analysis_ids.*' => 'integer|exists:analyses,id',
        ]);

        $analyses = $this->campaignAnalysisService->compareAnalysesForUser(
            $request->user()->id,
            $request->input('analysis_ids'),
        );

        return response()->json([
            'analyses' => $analyses,
            'comparison' => $this->campaignAnalysisService->generateComparisonMetrics($analyses),
        ]);
    }

    #[OA\Get(
        path: '/api/analyses/{id}/export-pdf',
        operationId: 'analysesExportPdf',
        tags: ['Analyses'],
        summary: 'Export analysis as PDF',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'PDF file',
                content: new OA\MediaType(
                    mediaType: 'application/pdf',
                    schema: new OA\Schema(type: 'string', format: 'binary')
                )
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
    public function exportPDF(Request $request, int $id)
    {
        $analysis = $this->campaignAnalysisService->getAnalysisForUserOrNull($request->user()->id, $id);

        if ($analysis === null) {
            return response()->json([
                'message' => 'Not found',
            ], 404);
        }

        $campaigns = $analysis->campaigns();

        $html = view('pdf.analysis-report', [
            'analysis' => $analysis,
            'campaigns' => $campaigns,
        ])->render();

        $options = new Options;
        $options->set('defaultFont', 'DejaVu Sans');
        $options->setIsRemoteEnabled(false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4');
        $dompdf->render();

        $pdfBinary = $dompdf->output();

        $filename = 'analysis-'.$analysis->id.'.pdf';

        return response($pdfBinary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Access-Control-Expose-Headers' => 'Content-Disposition',
        ]);
    }
}
