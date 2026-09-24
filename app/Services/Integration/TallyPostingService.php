<?php

namespace App\Services\Integration;

use App\Models\TallyConnection;
use App\Models\TallySyncQueue;

class TallyPostingService
{
    private const TIMEOUT_SECONDS = 15;
    private const MAX_ATTEMPTS = 5;

    public function __construct(
        private TallyVoucherXmlBuilder $builder,
        private TallyClient $client,
    ) {
    }

    /**
     * Attempt to post one queue row to Tally. Safe to call even when no
     * connection is configured or the connection isn't "connected" yet -
     * the row is simply left as-is in that case, no error recorded.
     */
    public function attempt(TallySyncQueue $queue): void
    {
        if ($queue->status === 'synced') {
            return;
        }

        if ($queue->status === 'failed' && $queue->attempts >= self::MAX_ATTEMPTS) {
            return;
        }

        $connection = TallyConnection::where('status', 'connected')->first();

        if (! $connection) {
            return;
        }

        $xml = $this->builder->buildSalesVoucher($queue->payload, $connection->company_name);
        $response = $this->client->post($connection, $xml, self::TIMEOUT_SECONDS);

        if ($response->accepted) {
            $queue->update([
                'status' => 'synced',
                'tally_voucher_id' => $response->tallyVoucherId,
                'last_error' => null,
                'attempts' => $queue->attempts + 1,
            ]);

            return;
        }

        // rawBody is null only for TallyResponse::networkFailure() (Tally
        // unreachable, timeout, or non-2xx HTTP). That's an outage, not a
        // data problem, so keep the row pending for the sweep and don't
        // count it against the attempt cap. A real Tally rejection (parsed
        // response body) still counts, since it needs a human to fix.
        if ($response->rawBody === null) {
            $queue->update(['last_error' => $response->errorMessage]);

            return;
        }

        $queue->update([
            'status' => 'failed',
            'last_error' => $response->errorMessage,
            'attempts' => $queue->attempts + 1,
        ]);
    }
}
