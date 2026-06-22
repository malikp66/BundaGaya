# Implementasi Sistem Konsinyasi (Titip Produk) + Gudang BSD

## Migration Files (5 files)

### 1. `database/migrations/2026_06_19_060001_add_consignor_fields_to_products.php`
Add to `products`:
- `user_id` (FK → users, nullable) — consignor
- `suggested_price` (decimal 12,2, nullable) — harga usulan consignor
- `dp_percentage` (decimal 5,2, default 20.00)
- `weight`, `length`, `width`, `height` (decimal 8,2, nullable)

### 2. `database/migrations/2026_06_19_060002_add_shipping_and_dp_to_orders.php`
Add to `orders`:
- `dp_total` (decimal 12,2, default 0)
- `shipping_cost` (decimal 12,2, default 0)
- `grand_total` (decimal 12,2, default 0)
- `shipping_courier`, `shipping_service`, `tracking_number` (string, nullable)
- `shipping_address`, `city`, `province`, `postal_code`, `district` (string, nullable)
- `processed_at`, `shipped_at`, `returned_at` (timestamp, nullable)
- `dp_refunded`, `dp_deducted` (decimal 12,2, default 0)

### 3. `database/migrations/2026_06_19_060003_add_consignor_id_to_order_items.php`
Add to `order_items`:
- `consignor_id` (FK → users, nullable)
- `dp_amount` (decimal 12,2, default 0)
- `dp_percentage` (decimal 5,2, default 20.00)

### 4. `database/migrations/2026_06_19_060004_create_consignor_balances_table.php`
- `user_id` (FK, unique)
- `balance`, `total_earned`, `total_withdrawn` (decimal 15,2)

### 5. `database/migrations/2026_06_19_060005_create_consignor_payouts_table.php`
- `user_id` (FK), `payout_number` (unique), `amount`
- `bank_name`, `bank_account`, `account_holder`
- `status` (default: pending), `rejection_reason`
- `approved_by` (FK → users), `processed_at`

## Model Updates

### Product model
- Add `consignor()` → BelongsTo(User)
- Add casts: `dp_percentage`, `weight`, `length`, `width`, `height`
- `$fillable`: add `user_id`, `suggested_price`, `dp_percentage`, `weight`, `length`, `width`, `height`

### Order model
- Add casts: `dp_total`, `shipping_cost`, `grand_total`, `dp_refunded`, `dp_deducted`
- Add `$fillable`: all new shipping & DP fields, timestamp fields

### OrderItem model
- Add `consignor()` → BelongsTo(User)
- Add casts: `dp_amount`, `dp_percentage`
- Add `$fillable`: `consignor_id`, `dp_amount`, `dp_percentage`

### User model
- Add `consignedProducts()` → HasMany(Product, 'user_id')
- Add `consignorBalance()` → HasOne(ConsignorBalance)
- Add `consignorPayouts()` → HasMany(ConsignorPayout)

### New: ConsignorBalance model
- `$fillable`: `user_id`, `balance`, `total_earned`, `total_withdrawn`
- Relationships: `user()` → BelongsTo(User)
- Methods: `credit($amount)`, `debit($amount)`

### New: ConsignorPayout model
- `$fillable`: `user_id`, `amount`, `bank_name`, `bank_account`, `account_holder`, `status`
- Relationships: `user()` → BelongsTo(User), `approver()` → BelongsTo(User)
- Boot: auto-generate `payout_number` format: `PYT-YYYYMMDD-RANDOM4`

## Service Updates

### OrderService — new status flow
Remove old `confirmed_by_owner`/`picked_up`/`returned` flow.
New transitions:
- `pending_payment → paid → processing → shipped → in_use → returned`
- From `returned` → admin inspects → `completed` (refund DP) OR flag damage
- `completed`: refund DP, credit consignor balance
- `cancelled`: can cancel anytime before `shipped`

Methods to update/add:
- `createFromCart()` — include dp_total, grand_total, consignor_id in items
- `markAsPaid()` — existing, but also set grand_total
- `markAsProcessing($order)` — admin prepares item
- `markAsShipped($order, $trackingNumber, $courier, $service)` — set tracking
- `markAsReturned($order)` — item back at warehouse
- `completeOrder($order)` — refund DP, credit consignor, settle
- `processDamage($order, $deductedAmount)` — deduct from DP, refund remainder

### CommissionService — update
- `calculateWithDP($subtotal, $commissionRate, $dpPercentage)` — returns dp_amount
- `getDefaultDPPercentage()` — from config

### New: ShippingService
- Biteship API integration
- `getRates($origin, $destination, $weight, $courier?)` → array of courier options
- `createShipment($order)` → create shipment, get tracking number
- Origin: configurable (BSD City), set in config/shipping.php

### New: ConsignorService
- `getOrCreateBalance(User $user)` — get or create balance record
- `creditEarnings(Order $order)` — distribute net amounts to consignors
- `getAvailableBalance(User $user)` — balance
- `requestPayout(User $user, $amount, $bankData)` — create payout request
- `approvePayout(ConsignorPayout $payout)` — mark approved
- `rejectPayout(ConsignorPayout $payout, $reason)` — mark rejected

### CartService — update
- `getCartSummary()` — include `dp_total` per item and per cart
- `getDPTotal()` — total of all items' DP

## Routes

### New consignor routes (middleware: auth):
```
GET  /consignor/dashboard   → Consignor\DashboardController@index
GET  /consignor/products    → Consignor\DashboardController@products
GET  /consignor/payouts     → Consignor\PayoutController@index
POST /consignor/payouts     → Consignor\PayoutController@store
```

### Updated customer routes:
Checkout needs shipping data:
```
POST /customer/checkout     — add shipping fields validation
POST /customer/shipping/rates — get shipping rates from Biteship
```

## Frontend — New Pages

### `resources/js/Pages/Consignor/Layout.jsx`
Consignor sidebar layout: Dashboard, Produk Saya, Penarikan, Kembali ke Beranda, Keluar

### `resources/js/Pages/Consignor/Dashboard.jsx`
Overview: total produk, total pendapatan, saldo tersedia, daftar produk terbaru

### `resources/js/Pages/Consignor/Products.jsx`
List all consigned products with stats per item:
- Foto, nama, harga sewa
- Berapa kali disewa (rental_count)
- Total pendapatan dari produk ini
- Status (active/inactive)

### `resources/js/Pages/Consignor/Payouts.jsx`
- Form ajukan penarikan (jumlah, bank)
- Riwayat penarikan dengan status

## Frontend — Updates

### `CustomerLayout.jsx`
- Add "Dashboard Titipan" link in mobile menu for users who have consigned products (or all users — just show the link, route checks if user has products)

### `Cart/Index.jsx`
- Add shipping address form: provinsi, kota, kecamatan, detail alamat, kode pos
- Add courier selection: after entering address, fetch rates from Biteship, show options
- Display DP amount per item
- Display grand total breakdown: subtotal + admin_fee + dp_total + shipping_cost

### Checkout flow
- Frontend sends shipping data + courier choice with checkout
- Backend validates and creates order with shipping fields
- Midtrans charges grand_total

## Filament Updates

### ProductResource
- Add fields: user_id (select consignor), suggested_price, dp_percentage, weight, dimensions
- Readonly: suggested_price (admin reference)

### OrderResource
- Add columns: shipping_courier, tracking_number, shipping_cost, grand_total
- Add status badges for new statuses
- Action "Process" → markAsProcessing
- Action "Ship" → form: courier, service, tracking_number → markAsShipped
- Action "Mark Returned" → markAsReturned
- Action "Complete (DP Refund)" → completeOrder
- Action "Damage (DP Deduct)" → form: deduction amount → processDamage

### New: ConsignorBalanceResource
- List: user, balance, total_earned, total_withdrawn
- View-only (admin manages via payouts)

### New: ConsignorPayoutResource
- List: user, amount, bank, status
- Actions: Approve, Reject

## Config

### `config/shipping.php`
```php
return [
    'biteship' => [
        'api_key' => env('BITESHIP_API_KEY'),
        'base_url' => env('BITESHIP_BASE_URL', 'https://api.biteship.com/v1'),
    ],
    'origin' => [
        'name' => 'BundaGaya Warehouse',
        'address' => 'BSD City',
        'city' => 'Tangerang Selatan',
        'province' => 'Banten',
        'postal_code' => '15310',
        'latitude' => -6.301,
        'longitude' => 106.652,
    ],
];
```

### `.env` additions
```
BITESHIP_API_KEY=
BITESHIP_BASE_URL=https://api.biteship.com/v1
```

## Grand Total Calculation

```
cart_item.subtotal = price_per_day * days * quantity
cart_item.dp_amount = cart_item.subtotal * (product.dp_percentage / 100)

order.subtotal       = SUM(cart_item.subtotal)
order.admin_fee      = Setting::getAdminFee() (5000)
order.dp_total       = SUM(cart_item.dp_amount)
order.shipping_cost  = from Biteship (user selected)
order.grand_total    = subtotal + admin_fee + dp_total + shipping_cost
order.total          = subtotal + admin_fee (legacy, for backward compat)

order_item.dp_amount = cart_item.dp_amount (snapshot at order creation)
order_item.dp_percentage = product.dp_percentage

When completed:
  dp_refunded = dp_total (if no damage)
  dp_refunded = dp_total - dp_deducted (if damaged)
  Consignor credited: net_amount (subtotal - commission_fee)
```

## Execution Order
1. ✅ Create all 5 migration files
2. Run `php artisan migrate`
3. Update models (Product, Order, OrderItem, User, new ConsignorBalance, ConsignorPayout)
4. Update services (CommissionService, OrderService, CartService)
5. Create new services (ShippingService, ConsignorService)
6. Create consignor controllers
7. Update routes
8. Create consignor frontend pages
9. Update CustomerLayout
10. Update Cart/Index.jsx (shipping flow)
11. Update Filament resources
12. Create Filament consignor resources
13. `npm run build` & verify
