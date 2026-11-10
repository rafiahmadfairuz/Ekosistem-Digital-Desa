<?php

namespace App\Console\Commands;

use App\Models\WifiAccessLog;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('wifi:logs:prune {--dry-run : Report expired rows without deleting them}')]
#[Description('Remove public Wi-Fi access logs after the configured retention period')]
class PruneWifiAccessLogs extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) config('wifi.access_log_retention_days', 90);
        if ($days < 30 || $days > 3650) {
            $this->error('WIFI_ACCESS_LOG_RETENTION_DAYS must be between 30 and 3650.');

            return self::FAILURE;
        }
        $query = WifiAccessLog::query()->where('connected_at', '<', now()->subDays($days));
        $count = (clone $query)->count();
        if ($this->option('dry-run')) {
            $this->info("{$count} Wi-Fi access log(s) are older than {$days} days; no rows were deleted.");

            return self::SUCCESS;
        }
        $deleted = $query->delete();
        $this->info("Deleted {$deleted} Wi-Fi access log(s) older than {$days} days.");

        return self::SUCCESS;
    }
}
