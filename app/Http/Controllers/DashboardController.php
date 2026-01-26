<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return view('index');
    }

    public function calculate()
    {
        return view('calculate');
    }

    public function stats(\Illuminate\Http\Request $request)
    {
        // default: last 7 days
        $finish = $request->query('finish_date', now()->toDateString());
        $start = $request->query('start_date', now()->subDays(6)->toDateString());

        $params = [
            'start_date' => $start,
            'finish_date' => $finish,
        ];

        // allow grouping by date (default), domain or placement based on UI filter
        $filterType = $request->query('filter_type', '');
        if ($filterType === 'domain') {
            $params['group_by[]'] = 'domain';
            $groupBy = 'domain';
        } elseif ($filterType === 'placement') {
            $params['group_by[]'] = 'placement';
            $groupBy = 'placement';
        } else {
            $params['group_by[]'] = 'date';
            $groupBy = 'date';
        }

        // Forward known filter params from the request (these should match Adstera API query keys)
        $allowed = ['country', 'country_id', 'domain', 'placement', 'placement_id', 'placement_ids', 'site', 'zone'];
        foreach ($allowed as $k) {
            if ($request->query($k) !== null && $request->query($k) !== '') {
                $params[$k] = $request->query($k);
            }
        }

        try {
            $service = app(\App\Services\AdsteraAuthService::class);
            $res = $service->request('GET', 'stats.json', ['query' => $params]);

            // preserve raw response for debug when needed
            $raw = $res;

            // normalize possible response shapes from Adstera API
            $items = $res['items'] ?? $res['data'] ?? $res['rows'] ?? [];
            // if data is an associative list under data (e.g. data => [ ...items... ])
            if (empty($items) && isset($res['data']) && is_array($res['data'])) {
                // if data contains items key
                if (isset($res['data']['items']) && is_array($res['data']['items'])) {
                    $items = $res['data']['items'];
                } elseif (array_values($res['data']) === $res['data']) {
                    $items = $res['data'];
                }
            }

            $labels = [];
            $impressions = [];
            $revenue = [];

            foreach ($items as $it) {
                if (!is_array($it)) {
                    continue;
                }
                $labels[] = $it['date'] ?? $it['day'] ?? null;
                if (isset($it['impression'])) {
                    $impressions[] = (int)$it['impression'];
                } elseif (isset($it['impressions'])) {
                    $impressions[] = (int)$it['impressions'];
                } else {
                    $impressions[] = 0;
                }

                if (isset($it['revenue'])) {
                    $revenue[] = (float)$it['revenue'];
                } elseif (isset($it['revenue_usd'])) {
                    $revenue[] = (float)$it['revenue_usd'];
                } else {
                    $revenue[] = 0.0;
                }
            }

            // If grouped by domain or placement, try to resolve numeric ids to titles
            if (in_array($groupBy, ['domain', 'placement'])) {
                try {
                    $service = app(\App\Services\AdsteraAuthService::class);
                    if ($groupBy === 'domain') {
                        $domainsRes = $service->request('GET', 'domains.json');
                        $domainItems = $domainsRes['items'] ?? $domainsRes['data'] ?? [];
                        $map = [];
                        foreach ($domainItems as $d) {
                            if (isset($d['id'])) $map[(string)$d['id']] = $d['title'] ?? ($d['name'] ?? null);
                        }
                        foreach ($items as &$it) {
                            if (isset($it['domain']) && isset($map[(string)$it['domain']])) {
                                $it['domain'] = $map[(string)$it['domain']];
                            }
                        }
                        unset($it);
                    } else {
                        // If a domain filter is provided, prefer domain-scoped placements endpoint
                        $domainFilter = $request->query('domain', '') ?: $request->query('domain_id', '');
                        try {
                            if ($domainFilter) {
                                $placementsRes = $service->request('GET', "domain/{$domainFilter}/placements.json", ['query' => ['format' => 'json']]);
                            } else {
                                $placementsRes = $service->request('GET', 'placements.json');
                            }
                        } catch (\Exception $e) {
                            // fallback to global placements list if domain-scoped fails
                            $placementsRes = $service->request('GET', 'placements.json');
                        }
                        $placementItems = $placementsRes['items'] ?? $placementsRes['data'] ?? [];
                        $map = [];
                        foreach ($placementItems as $p) {
                            if (isset($p['id'])) {
                                // prefer alias when available
                                $map[(string)$p['id']] = $p['alias'] ?? $p['title'] ?? null;
                            }
                        }
                        foreach ($items as &$it) {
                            // stats item may use 'placement' or 'placement_id'
                            $pid = null;
                            if (isset($it['placement'])) $pid = (string)$it['placement'];
                            elseif (isset($it['placement_id'])) $pid = (string)$it['placement_id'];
                            if ($pid && isset($map[$pid]) && $map[$pid] !== null) {
                                // add alias field for frontend convenience and replace displayed placement
                                $it['alias'] = $map[$pid];
                                $it['placement'] = $map[$pid];
                            }
                        }
                        unset($it);
                    }
                } catch (\Exception $e) {
                    // ignore mapping errors; frontend will show numeric ids
                }
            }

            // Prefer an explicit balance field from the Adstera response when present.
            $balance = null;
            $possibleBalanceKeys = ['balance', 'account_balance', 'wallet_balance'];
            foreach ($possibleBalanceKeys as $k) {
                if (isset($res[$k]) && is_numeric($res[$k])) {
                    $balance = (float)$res[$k];
                    break;
                }
            }
            // Common nested locations
            if ($balance === null) {
                if (isset($res['account']['balance']) && is_numeric($res['account']['balance'])) {
                    $balance = (float)$res['account']['balance'];
                } elseif (isset($res['data']['balance']) && is_numeric($res['data']['balance'])) {
                    $balance = (float)$res['data']['balance'];
                }
            }

            // Fallback: sum reported revenue values
            if ($balance === null) {
                $balance = array_sum($revenue);
            }

            $out = ['labels' => $labels, 'impressions' => $impressions, 'revenue' => $revenue, 'items' => $items, 'balance' => $balance];
            if (config('app.debug')) {
                $out['raw'] = $raw;
            }

            return response()->json($out);
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            if (config('app.debug')) {
                return response()->json(['error' => $msg, 'exception' => (string)$e], 500);
            }
            return response()->json(['error' => 'Failed to fetch stats'], 500);
        }
    }
}
