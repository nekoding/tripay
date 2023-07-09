# Changelog

All notable changes to `tripay` will be documented in this file

## 1.0.0

- initial release

## 2.0.0

- Bump PHP dependency
- Add support PHP8.2
- Add unit test
- Add custom exception `TripayValidationException` and custom method `TripayValidationException::getErrorBags()`
- Add `merchant_code` to signature hash generated // ref: https://tripay.co.id/developer?tab=transaction-signature-create
- Breaking Change API:

```
1. Transaction::setSignatureHash
2. Tripay::getInstruksiPembayaran
3. Tripay::getChannelPembayaran
4. Tripay::getBiayaTransaksi
```
