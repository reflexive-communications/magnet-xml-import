# magnet-xml-import

[![CI](https://github.com/reflexive-communications/magnet-xml-import/actions/workflows/main.yml/badge.svg)](https://github.com/reflexive-communications/magnet-xml-import/actions/workflows/main.yml)

This extension lets you import contributions from Magnet Bank statement XML file.
It provides an administration interface where you can set the parameters of the import.

The extension is licensed under [AGPL-3.0](LICENSE.txt).

## Requirements

-   PHP v7.3+
-   CiviCRM v5.76+
-   rc-base

## Installation

Sysadmins and developers may clone the [Git](https://en.wikipedia.org/wiki/Git) repo for this extension and install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
git clone https://github.com/reflexive-communications/magnet-xml-import.git
cv en magnet-xml-import
```

## Getting Started

The admin form can be reached from **Contribution > Magnet XML Import**.

**Parameters**

-   "Source": contribution source
-   "Financial Type": `financial_type_id` of contribution
-   "Payment method": `payment_instrument_id` of contribution
-   "Bank Account": this field stores the contact's bank account number
-   "Only income": if this is checked, the negative transactions will be skipped during import
-   "Magnet XML file": upload field for the XML

For details check the [Developer Notes](DEVELOPER.md).
