# Tripay Laravel

Package ini digunakan untuk berinteraksi dengan API milik Tripay.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/nekoding/tripay.svg?style=flat-square)](https://packagist.org/packages/nekoding/tripay)
[![Total Downloads](https://img.shields.io/packagist/dt/nekoding/tripay.svg?style=flat-square)](https://packagist.org/packages/nekoding/tripay)

## Installation

You can install the package via composer:

```bash
composer require nekoding/tripay
```

## Usage

```php
use Nekoding\Tripay\Networks\HttpClient;
use Nekoding\Tripay\Tripay;
use Nekoding\Tripay\Signature;

$data = [
    'method'         => 'BRIVA',
    'merchant_ref'   => 'KODE INVOICE',
    'amount'         => 50000,
    'customer_name'  => 'Nama Pelanggan',
    'customer_email' => 'emailpelanggan@domain.com',
    'customer_phone' => '081234567890',
    'order_items'    => [
        [
            'sku'         => 'FB-06',
            'name'        => 'Nama Produk 1',
            'price'       => 50000,
            'quantity'    => 1,
            'product_url' => 'https://tokokamu.com/product/nama-produk-1',
            'image_url'   => 'https://tokokamu.com/product/nama-produk-1.jpg',
        ]
    ],
    'return_url'   => 'https://domainanda.com/redirect',
    'expired_time' => (time() + (24 * 60 * 60)), // 24 jam
    'signature'    => Signature::generate('KODE INVOICE' . 50000)
];

// dengan facade

$res = Tripay::createTransaction($data)
$res = Tripay::createTransaction($data, Tripay::CLOSE_TRANSACTION);

// tanpa facade

$tripay = new Tripay(new HttpClient('api_key_anda'));

$res = $tripay->createTransaction($data);
$res = $tripay->createTransaction($data, Tripay::CLOSE_TRANSACTION);

```

### Method Available

#### Tripay

| Method                   | Parameter                                                           | Return       | Deskripsi                                                                                                                                                              |
| ------------------------ | ------------------------------------------------------------------- | ------------ | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `getInstruksiPembayaran` | `string $code, string $payCode, string $amount, int $allowHtml = 1` | `Collection` | digunakan untuk mengambil instruksi pembayaran yang disediakan oleh pihak tripay                                                                                       |
| `getChannelPembayaran`   | `string $code`                                                      | `Collection` | digunakan untuk mendapatkan daftar channel pembayaran yang aktif pada akun Merchant Anda beserta informasi lengkap termasuk biaya transaksi dari masing-masing channel |
| `getBiayaTransaksi`      | `string $code, int $amount`                                         | `Collection` | digunakan untuk mendapatkan rincian perhitungan biaya transaksi untuk masing-masing channel berdasarkan nominal yang ditentukan                                        |
| `getDaftarTransaksi`     | `array $data = []`                                                  | `Collection` | digunakan untuk mendapatkan rincian perhitungan biaya transaksi untuk masing-masing channel berdasarkan nominal yang ditentukan                                        |
| `createTransaction`      | `array $data, string $transactionType = 'close'`                    | `Collection` | digunakan untuk membuat transaksi baru atau melakukan generate kode pembayaran                                                                                         |
| `getDetailTransaction`   | `string $id, string $transactionType = 'close'`                     | `Collection` | digunakan untuk mengambil detail transaksi yang pernah dibuat. Dapat juga digunakan untuk cek status pembayaran                                                        |

#### Signature

Cek pattern hash dari transaksi yang akan digunakan disini :  
[CLOSE TRANSACTION](https://tripay.co.id/developer?tab=transaction-signature-create)  
[OPEN TRANSACTION](https://tripay.co.id/developer?tab=open-payment-signature-create)

| Method     | Parameter                             | Return   | Deskripsi                                                                                           |
| ---------- | ------------------------------------- | -------- | --------------------------------------------------------------------------------------------------- |
| `validate` | `string $data, string $signatureHash` | `bool`   | digunakan untuk memvalidasi hash yang sudah dibuat apakah sesuai dengan ketentuan dari pihak tripay |
| `generate` | `string $data`                        | `string` | digunakan untuk membuat signature hash sesuai dengan ketentuan pihak tripay                         |

### Validasi Parameter

Package ini melakukan validasi data sebelum diteruskan ke API tripay untuk menghindari error pada parameter. berikut adalah list parameter yang perlu diperhatikan ketika membuat payload request.

#### Close Transaction Create

Referensi : [Tripay doc](https://tripay.co.id/developer?tab=transaction-create)

| Parameter                   | Validator                |
| --------------------------- | ------------------------ |
| `method`                    | `bail, required, string` |
| `merchant_ref`              | `bail, required, string` |
| `amount`                    | `bail, required, int`    |
| `customer_name`             | `bail, required, string` |
| `customer_email`            | `bail, required, string` |
| `customer_phone`            | `bail, required, string` |
| `order_items`               | `bail, required, array`  |
| `order_items.*.sku`         | `bail, nullable, string` |
| `order_items.*.price`       | `bail, required, int`    |
| `order_items.*.name`        | `bail, required, string` |
| `order_items.*.quantity`    | `bail, required, int`    |
| `order_items.*.product_url` | `bail, nullable, string` |
| `order_items.*.image_url`   | `bail, nullable, string` |
| `return_url`                | `bail, nullable, string` |
| `expired_time`              | `bail, nullable, int`    |
| `signature`                 | `bail, required, string` |

#### Close Transaction Detail

Referensi : [Tripay doc](https://tripay.co.id/developer?tab=transaction-detail)

| Parameter   | Validator          |
| ----------- | ------------------ |
| `reference` | `required, string` |

#### Open Transaction Create

Referensi : [Tripay doc](https://tripay.co.id/developer?tab=open-payment-create)

| Parameter       | Validator                |
| --------------- | ------------------------ |
| `method`        | `bail, required, string` |
| `merchant_ref`  | `bail, nullable, string` |
| `customer_name` | `bail, nullable, string` |
| `signature`     | `bail, required, string` |

#### Open Transaction Detail

Referensi : [Tripay doc](https://tripay.co.id/developer?tab=open-payment-detail)

| Parameter | Validator          |
| --------- | ------------------ |
| `uuid`    | `required, string` |

#### Daftar Transaksi

Referensi : [Tripay doc](https://tripay.co.id/developer?tab=merchant-transactions)

| Parameter      | Validator                     |
| -------------- | ----------------------------- |
| `page`         | `bail, nullable, int`         |
| `per_page`     | `bail, nullable, int`         |
| `sort`         | `bail, nullable, in:asc,desc` |
| `reference`    | `bail, nullable, string`      |
| `merchant_ref` | `bail, nullable, string`      |
| `method`       | `bail, nullable, string`      |
| `status`       | `bail, nullable, string`      |

### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [enggar tivandi](https://github.com/nekoding)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
