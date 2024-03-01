![laravel_wallet.jpg](laravel_wallet.jpg)

# Laravel Wallet

## Introduction

Laravel Wallet is a configurable wallet system for platform
transaction management, it has nothing to do with payment providers
or the crediting of funds.

If you provide a platform that uses micro transactions between users and
other entities then this package can help facilitate that whilst recording
those transactions.

Potential Uses:

- Points System
- Store Credit
- Currency Trading
- Gamification
- Gift Balances
- Subscriptions
- Reward Systems
- ...

The package is configurable to allow for adoption into existing
platforms and for good control of an implementation within a new platform.

### Feature List

- [x] Multiple Currencies
- [x] Multiple Wallets Per Entity
- [x] Single Currency Wallet Locking
- [x] Depositing of funds into Wallets
- [x] Withdrawal of funds from Wallets
- [x] Transferring of funds between Wallets
- [x] Full Transaction Logging
- [x] Transaction Events
- [x] Wallet Metadata

### Configurable Options

- [x] Set models for Wallet and Wallet Transactions
- [x] Use of Uuids or Integers for record ids (Uuids recommended)
- [x] Allow negative balances (not recommended)
- [x] Locking of wallets to one per currency per entity
- [x] Set wallet and wallet transactions table names

## Installation

```bash
composer require ronappleton/laravel-wallet
```

The package is automatically registered and discovered.

## Usage

A trait is provided `Appleton\LaravelWallet\Models\Concerns\HasWallets`

Use the trait in any models like `User` that you want to have wallets.

You can then use `User::createWallet(string|BackedEnum $currency)` to create a new
wallet for the user. If the user already has a wallet for the currency, and the
setting `one_wallet_per_currency` is true, a `WalletExists` exception will be thrown.

You can use the currency column in a couple of ways:

- To record the wallet type, for example: Points (for loyalty points etc.)
- To record the currency, for example: USD, GBP, EUR
- To record cryptocurrency, for example: BTC, ETH, LTC

This ensures you can use the system for multiple purposes within the same installation.

Wallets cannot be either updated, nor deleted. This ensures consistency in data recording
i.e. transactions.

Transactions cannot be update, nor deleted. Again this is for consistency in data recording.

You can as you would imagine call `balance` on a wallet model, the balance will always be calculated
from the transactions table. 

This means you may find performance degradation over time and is why you can configure the wallet and
wallet transaction models via config, so you can tune your approach.

### Moving funds

There is 3 methods on the Wallet Model related to moving funds about:

* deposit(float $amount, array $meta = [])
* withdrawal(float $amount, array $meta = [])
* transfer(Wallet $wallet, float $amount, array $meta = [], ?CurrencyConverter $converter = null)

You will notice the array `$meta` parameter to each method.

This is provided for you to add any extra information you may need to record.

```php
@param array<string, mixed> $meta
```

It should be noted that the authenticated user will automatically be recorded.

You will also notice the `CurrencyConverter` parameter of the transfer method.

Understandably you may need to convert currencies/points etc.

`CurrencyConverter` is an interface within the package that allows you to pass a
converter to the method to allow for conversion. The contract sets out the methods
required for the package to manage the conversions automatically. The conversion metadata
is also recorded automatically, recording the currencies converted between, the rate used
and the converter used.

### Events

There are two events within the package that you can listen for:

- `Appleton\LaravelWallet\Events\TransactionStartEvent`
- `Appleton\LaravelWallet\Events\TransactionCompletedEvent`

The first event is dispatched when a transaction is started.

This event could be used to run fraud checks etc prior to the transaction being completed.

The second event is dispatched when a transaction is completed.

This event could be used to email a user details of the transaction for example, or
maybe you charge commission on transfers, or for currency conversion and so on,
a listener could be used to then create those charges.

Both events are fired during every transaction, deposit, withdrawal and transfer.

