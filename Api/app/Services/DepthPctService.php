<?php


namespace App\Services;


class DepthPctService
{

    /**
     * 深度图数据 set pub
     * @param string $code
     */
    public static function MakePct(string $code)
    {

        $depth_data = MarketService::getDepthData($code, 'depth');
        $depth_data = json_decode($depth_data, true);

        if (!is_array($depth_data)) {
            return;
        }

        if (is_array($depth_data['asks']) && is_array($depth_data['bids'])) {
//            $new_asks = static::sum_pct(array_reverse($depth_data['asks']));
            $new_asks = static::sum_pct($depth_data['asks']);
            $new_bids = static::sum_pct($depth_data['bids']);
            unset($depth_data['asks'], $depth_data['bids']);
            $depth_data['asks'] = $new_asks;
            $depth_data['bids'] = $new_bids;
            $depth_data['type'] = 'pct';
            $pct_json = json_encode($depth_data);
//            echo $pct_json,PHP_EOL;
            MarketService::setDepthData($code, 'pct', $pct_json);
            MarketService::pubData(MarketService::getChannel('pct'), $pct_json);
        }
    }

    /**
     * 盘口数据合并出深度图
     * @param array $depth
     * @return mixed
     */
    protected static function sum_pct(array $depth): array
    {
        foreach ($depth ?? [] as $ak => $av) {
            if ($ak > 0) {
                $depth[$ak]['totalSize'] += $depth[$ak - 1]['totalSize'];
            }
        }
        return $depth;
    }
}
