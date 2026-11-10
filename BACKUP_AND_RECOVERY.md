# Backup and recovery plan

**Status:** Procedures proposed; no production backup or restore was performed during Phase 8.

## What to protect

- MySQL database, including migration history, encrypted resident fields, approval/payment/audit history, and application records.
- Private files from `storage/app/private` or the configured private object-storage bucket, including correspondence documents, attachments, and seller supporting documents.
- Independent application and identity encryption keys (`APP_KEY`, `DATA_ENCRYPTION_KEY`, `IDENTITY_BLIND_INDEX_KEY`) stored separately in an access-controlled secrets manager. A database backup without these keys is not a usable identity-data recovery; keys must not be bundled into the database dump.
- Release identifier, migration version, environment/configuration inventory without secret values, and restore runbook.

## Protection and retention policy to approve

- Use a dedicated backup identity with minimum read/export rights; restrict restore privileges to named operators and require MFA where the platform supports it.
- Encrypt backups before transfer and at rest using a managed KMS or organization-approved strong encryption. Keep encryption-key recovery separate from backup storage.
- Transfer over TLS to a separate account/region or offline protected medium. Do not keep the only backup on the application host.
- Suggested initial retention pending legal/business approval: daily 35 days, monthly 12 months, annual retention only if required by records policy. Document deletion/hold rules before enabling automated expiry.
- Log backup start/end, size, checksum, encryption result, storage destination, and failure without logging credentials or personal data.

## Example database backup

Use an operator-owned MySQL option file with mode `0600`, not a password on the command line. Substitute approved paths and destination; protect the resulting file immediately.

```sh
umask 077
mysqldump --defaults-extra-file=/secure/path/backup.cnf \
  --single-transaction --routines --triggers --events \
  --hex-blob --set-gtid-purged=OFF desa_digital \
  | age -r '<approved-backup-recipient>' > /secure/backup/desa-$(date +%F).sql.age
sha256sum /secure/backup/desa-$(date +%F).sql.age
```

Check options against the exact MySQL client/server release. `--single-transaction` gives a consistent snapshot for transactional tables but does not replace operational verification of non-transactional objects.

## Private files and keys

Back up private storage with a tool that preserves file contents and metadata, encrypt before leaving the host, and record a manifest/checksum. Use a consistent snapshot or briefly quiesce writes so database references and files correspond. Back up secret-manager material through its documented key recovery process; never put keys in the same archive as data.

## Isolated restore drill

1. Provision a separate, disposable non-production MySQL instance of the exact target version and an isolated private storage directory.
2. Retrieve a recent encrypted backup and the approved key-recovery material through separate authorized channels. Verify the backup checksum before decryption.
3. Restore only into the isolated database. Do not use production host names or credentials in the drill.
4. Restore private files to the isolated storage path. Verify representative file checksums and that database references resolve.
5. Run migration-status inspection, application health checks, login with synthetic accounts, authorized document retrieval, encrypted-field decryption with the recovered keys, and critical read-only reports. Never send test email or payment callbacks to production.
6. Record timestamps, versions, checksum, failures, recovery duration, and any data gap. Remove the isolated test copy under the approved retention process.

**Recovery evidence:** Not tested in this assessment. Define an approved RPO/RTO, owner/on-call contact, retention/legal policy, and recovery sign-off before production use.
