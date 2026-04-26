<?php

namespace App\Services;

use App\Models\Analys;
use App\Models\Campaign;
use App\Services\AI\OpenRouterService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CampaignAnalysisService
{
    public function __construct(
        private readonly OpenRouterService $openRouterService,
    ) {}

    /**
     * createAnalysisForUser
     *
     * @param  mixed  $userId
     * @param  mixed  $campaignIds
     */
    public function createAnalysisForUser(int $userId, array $campaignIds): ?Analys
    {
        $campaigns = Campaign::whereIn('id', $campaignIds)
            ->where('user_id', $userId)
            ->get();

        if ($campaigns->isEmpty()) {
            return null;
        }

        $campaignData = $this->buildCampaignData($campaigns);
        $prompt = $this->buildPrompt($campaignData);

        $content = $this->openRouterService->sendMessage($prompt);

        $aiAnalysis = $this->parseAIResponse($content);

        $analysis = Analys::create([
            'user_id' => $userId,
            'campaign_ids' => $campaignIds,
            'summary' => $aiAnalysis['summary'],
            'performance_analysis' => $aiAnalysis['performance'],
            'underperforming_campaigns' => $aiAnalysis['underperforming'] ?? null,
            'optimization_suggestions' => $aiAnalysis['suggestions'],
            'action_items' => $aiAnalysis['actionItems'],
            'metrics' => $this->aggregateMetrics($campaigns),
        ]);

        return $analysis;
    }

    /**
     * listAnalysesForUser
     *
     * @param  mixed  $userId
     * @param  mixed  $perPage
     */
    public function listAnalysesForUser(int $userId, int $perPage = 20): LengthAwarePaginator
    {
        return Analys::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * getAnalysisForUserOrNull
     *
     * @param  mixed  $userId
     * @param  mixed  $analysisId
     */
    public function getAnalysisForUserOrNull(int $userId, int $analysisId): ?Analys
    {
        return Analys::where('user_id', $userId)->where('id', $analysisId)->first();
    }

    /**
     * compareAnalysesForUser
     *
     * @param  mixed  $userId
     * @param  mixed  $analysisIds
     */
    public function compareAnalysesForUser(int $userId, array $analysisIds): Collection
    {
        return Analys::whereIn('id', $analysisIds)
            ->where('user_id', $userId)
            ->get();
    }

    /**
     * generateComparisonMetrics
     *
     * @param  mixed  $analyses
     */
    public function generateComparisonMetrics(Collection $analyses): Collection
    {
        return $analyses->map(function (Analys $analysis) {
            return $analysis->metrics;
        });
    }

    /**
     * buildCampaignData
     *
     * @param  mixed  $campaigns
     */
    private function buildCampaignData(Collection $campaigns): array
    {
        return $campaigns->map(function (Campaign $campaign) {
            return [
                'id' => $campaign->id,
                'name' => $campaign->name,
                'platform' => $campaign->platform,
                'metrics' => $campaign->getMetrics(),
                'impressions' => $campaign->impressions,
                'clicks' => $campaign->clicks,
                'conversions' => $campaign->conversions,
                'spend' => $campaign->spend,
                'revenue' => $campaign->revenue,
                'period' => $campaign->start_date.' to '.$campaign->end_date,
            ];
        })->toArray();
    }

    /**
     * aggregateMetrics
     *
     * @param  mixed  $campaigns
     */
    private function aggregateMetrics(Collection $campaigns): array
    {
        return [
            'total_impressions' => $campaigns->sum('impressions'),
            'total_clicks' => $campaigns->sum('clicks'),
            'total_conversions' => $campaigns->sum('conversions'),
            'total_spend' => $campaigns->sum('spend'),
            'total_revenue' => $campaigns->sum('revenue'),
            'avg_ctr' => round($campaigns->average('ctr') ?? 0, 2),
            'avg_cpc' => round($campaigns->average('cpc') ?? 0, 2),
            'avg_roas' => round($campaigns->average('roas') ?? 0, 2),
        ];
    }

    /**
     * buildPrompt
     *
     * @param  mixed  $campaignData
     */
    private function buildPrompt(array $campaignData): string
    {
        $campaignText = json_encode($campaignData, JSON_PRETTY_PRINT);

        return <<<PROMPT
                    You are an expert digital marketing analyst. Analyze the following advertising campaign data and provide:

                    1. A brief performance summary (2-3 sentences)
                    2. Performance analysis of each campaign
                    3. Identification of underperforming campaigns
                    4. Specific optimization suggestions for each campaign
                    5. 5-7 prioritized action items

                    Campaign Data:
                    $campaignText

                    Respond in the following JSON format:
                    {
                    "summary": "Brief overview of overall performance",
                    "performance": "Detailed analysis of each campaign's performance",
                    "underperforming": "List of underperforming campaigns and why",
                    "suggestions": "Specific optimization recommendations",
                    "actionItems": [
                        {"priority": 1, "action": "Action description", "expectedImpact": "Potential improvement"},
                        ...
                    ]
                    }
                    PROMPT;
    }

    /**
     * parseAIResponse
     *
     * @param  mixed  $response
     */
    private function parseAIResponse(string $response): array
    {
        preg_match('/\{[\s\S]*\}/', $response, $matches);

        if (! empty($matches)) {
            return json_decode($matches[0], true) ?? $this->defaultResponse();
        }

        return $this->defaultResponse();
    }

    /**
     * defaultResponse
     */
    private function defaultResponse(): array
    {
        return [
            'summary' => 'Analysis completed',
            'performance' => 'Campaign performance analysis',
            'suggestions' => 'Optimization suggestions provided',
            'actionItems' => [],
        ];
    }
}
