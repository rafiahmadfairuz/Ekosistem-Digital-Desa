# Xendit marketplace checkout

## Implemented flow

New marketplace checkouts use one Xendit Payment Session for the entire checkout group. The buyer completes payment on Xendit's hosted checkout page. The application releases seller orders only after an authenticated `payment_session.completed` webhook matches the session reference, checkout reference, IDR currency and total amount. Duplicate webhook events are idempotent. COD remains available; manual bank transfer is not offered.

The checkout does not create split rules, send a `with-split-rule` header, or require seller XenPlatform Business IDs. A standard Payment Session is created through `POST /sessions`; Xendit's API documents `with-split-rule` separately as a XenPlatform header. See [Create Session API](https://docs.xendit.co/apidocs/create-session) and [Payment Session overview](https://docs.xendit.co/docs/payment-sessions-overview/).

With this flow, the Xendit merchant account associated with the server-side API key collects the checkout payment. Checkout confirmation alone does not create withdrawable seller balance. An immutable ledger credits the seller only when an order is completed and a paid payment exists for that order or its checkout group. This includes COD only after the collection confirmation is recorded. The balance is calculated from ledger entries; no request parameter can set or increase a seller balance. The buyer can receive an order while seller earnings remain unavailable until completion.

Sellers see completed earnings, completed paid item counts, available balance, reserved withdrawals, ledger history, and withdrawal status on the balance page. A withdrawal request locks the seller row, recomputes available funds from the ledger, requires a verified payout destination, inserts a negative reservation entry, and queues a Xendit Payouts v3 request in the same database transaction. A unique request key prevents repeated form submissions from creating duplicate withdrawal requests. Failed, rejected, or reversed payouts append a compensating ledger entry only once; an uncertain network result keeps the balance reserved until the webhook or operator reconciliation confirms its outcome. Payout callbacks are authenticated, validated against the withdrawal reference, payout ID, currency, and amount, and idempotently recorded.

This is a later money-out transfer from the merchant's Xendit balance, not a split at checkout. Xendit requires a secret API key with Money Out permission and supports idempotency keys. See [Create Payout API](https://docs.xendit.co/apidocs/create-payout-v3), [Payout webhook](https://docs.xendit.co/apidocs/payout-v3-webhook), [Indonesia payout coverage](https://docs.xendit.co/docs/payout-coverage-indonesia), and [Payout integration guide](https://docs.xendit.co/docs/integration-payouts/).

Sellers submit bank SWIFT/BIC, account number, account-holder name, recipient address, and recipient type on the balance page. Sensitive account fields and payout snapshots are encrypted at rest. A BUMDes Manager verifies the destination before it can receive payout. Xendit does not validate account ownership by default; the review process must confirm destination details. Payouts remain subject to merchant enablement, sufficient Xendit balance, bank channel support, and compliance review.

## Configuration

The local example keeps `MARKETPLACE_PAYMENT_MODE=simulated`, so it does not expose Xendit at checkout. To use Xendit in test mode, store these values in the private environment or secret store:

```dotenv
MARKETPLACE_PAYMENT_MODE=test
MARKETPLACE_XENDIT_ENABLED=true
XENDIT_SECRET_KEY=xnd_test_replace_with_test_secret
XENDIT_WEBHOOK_TOKEN=replace_with_test_webhook_verification_token
XENDIT_PAYOUTS_ENABLED=true
XENDIT_PAYOUT_WEBHOOK_TOKEN=replace_with_payout_webhook_verification_token
XENDIT_PAYOUT_SOURCE_OF_FUND=BUSINESS_REVENUE
XENDIT_PAYOUT_PURPOSE_CODE=TRADES
XENDIT_BASE_URL=https://api.xendit.co
XENDIT_TIMEOUT=10
```

The checkout option appears only when marketplace checkout is enabled, mode is `test` or `live`, and payment credentials are configured. Withdrawal requests additionally require Payouts to be enabled, a payout webhook token, a verified seller bank account, and a running database queue worker. The payout webhook URL is `https://your-public-host/api/marketplace/xendit/payout-webhook`; configure its verification token as `XENDIT_PAYOUT_WEBHOOK_TOKEN`. `XENDIT_PAYOUT_SOURCE_OF_FUND` and `XENDIT_PAYOUT_PURPOSE_CODE` must reflect Xendit's approval for the merchant; the defaults are examples, not a compliance determination. Do not put credentials in Vite variables, Blade, source control, or the database. After changing environment values, clear cached configuration with `php artisan config:clear`.

Configure the Payment Session webhook destination as `https://your-public-host/api/marketplace/xendit/webhook` and set the same callback verification token in `XENDIT_WEBHOOK_TOKEN`. Xendit cannot call a localhost URL; use a secured HTTPS staging host for sandbox callbacks. Never mark a payment paid from a return URL, browser input, screenshot, or uploaded receipt.

## Before live mode

Confirm with the account owner and Xendit that the merchant entity, Payment Session, and Payouts flow are approved for this marketplace and the actual goods. Decide who receives buyer funds, who owes each seller, how fees, payout timing, insufficient-balance failures, refunds, disputes, chargebacks and reconciliation are handled, and how seller settlement is recorded. Configure and test genuine production callbacks before enabling `MARKETPLACE_PAYMENT_MODE=live`.

The repository does not contain the account's rejection notice or prove Xendit approval, Money Out permission, live credentials, merchant eligibility, settlement terms, production callbacks, or legal authority to collect funds for independent sellers. Test-mode success verifies application behavior only.

Existing records that already have split allocations retain their historical allocation and webhook data. New checkouts do not create split allocations or require XenPlatform.
