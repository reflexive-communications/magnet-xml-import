# magnet-xml-import

This extension lets you import contribution transactions from Magnet XML. It provides an administration interface where you can set the parameters of the contributions.

The extension is licensed under [AGPL-3.0](LICENSE.txt).

## Administration interface

The admin form can be reached from the `Contribution > Magnet XML Import` menu.

![admin form](./assets/docs/admin-form.png)

### The parameters

The `Source` parameter will be used as the value of the constribution source. You can add any text you want. The default value is `Magnet Bank`. Required.

The selected `Financial Type` will be set as the financial\_type\_id of the contribution. You can choose from the financial types provided by the system. The default value is `Donation`. Required.

The selected `Payment method` will be set as the payment\_instrument\_id of the contribution. You can choose from the payment methods provided by the system. The default value is `EFT`. Required.

The selected `Bank Account` will be used for the contact identification. This contact parameter has to store the bank account id of the contact. You can choose from the contact parameters provided bz the system. The default value is `custom_1` custom parameter. Required.

When the `Only income` checkbox is checked, the negative transactions will be skipped during the import process. The number of skipped transactions will be shown in the info popup after the import execution. By default it is `checked`.

The `Magnet XML file` is the upload field for the XML. It does not have default value. Required.

## How does it work

- Data mapping
- Parameter based behaviour
- Contact mapping is based on the bank account number

## Requirements

* PHP v7.3+
* CiviCRM 5.37+

## Installation (CLI, Git)

Sysadmins and developers may clone the [Git](https://en.wikipedia.org/wiki/Git) repo for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
git clone https://github.com/FIXME/magnet-xml-import.git
cv en magnet_xml_import
```
