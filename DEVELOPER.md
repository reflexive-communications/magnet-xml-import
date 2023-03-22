# Developer Notes

## How does it work

A successful form submission triggers the execution of the Magnet XML import service.
The Importer application uses the configurations described above and the uploaded XML file and returns detailed import statistics after the execution.

First it reads the file and loads it as XML object.
The all parameter of the stats is the sum of the `Tranzakcio` tags.
The import process iterates over the `Tranzakcio` tags and handles them one by one.

The Transformer class is responsible for the data transformation.
It extracts and transforms the necessary contact and the contribution data from the transaction XML object.
The data extraction was based on existing implementation and not on documentations.

When the `Only income` is set and the `total_amount` in the transformed contribution is not a positive number, it updates the skipped stat and continues the execution with the next transaction.
The execution also continues with the next transaction if the current one is a duplication. In this case the duplication stat is increased.
The transaction identifier is checked.

The Magnet provides only the bank account number as uniq identifier, so that the contact mapping is based on this.
First it tries to find the contact with the given value of the `Bank Account` config.
If the contact is not found and the given account number matches a pattern, then the number is transforms to IBAN like format and the previous contact get is tried again, but with the LIKE operator.
If the contact still not found, a new one is created.
If the contact is not found and we were not able to create a new contact, it continues the execution with the next transaction as a contact id is required for the contributions.

Finally it creates the contribution and increases the imported stat.
When the import process is finished an info alert with the statistics is displayed.
