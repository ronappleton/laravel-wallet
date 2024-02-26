![laravel_wallet.jpg](laravel_wallet.jpg)

# Laravel Wallet

## Introduction

Laravel Wallet is a highly configurable wallet system for platform
transaction management, it has nothing to do with payment providers
or the crediting of funds.

If you provide a platform that uses micro transactions between users and
other entities then this package can help facilitate that whilst recording
those transactions.

The package is highly configurable to allow for adoption into existing
platforms and for good control of an implementation within a new platform.

### Feature List

- [ ] Multiple Currencies
- [ ] Multiple Wallets Per Entity
- [ ] Single Currency Wallet Locking
- [ ] Depositing of funds into Wallets
- [ ] Withdrawal of funds from Wallets
- [ ] Transferring of funds between Wallets
- [ ] Full Transaction Logging
- [ ] Transaction Events
- [ ] Use string, Model, BackedEnum for Currency

#### Configurable Options

- [ ] Table names
- [ ] Use of Uuids or Integers for record ids
- [ ] Locking of wallets to one per currency per entity