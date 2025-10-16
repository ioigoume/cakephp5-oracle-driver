# Implementation details

## Conventions used
### Table names
Following CakePHP conventions all tables and columns should be created as lowercase, underscore.

```sql
CREATE TABLE "articles" ...
```

Default Driver configuration is

```
'case' => 'lower'
```

We recommend to use quoted identifiers, in other case CakePHP conventions for views and controllers could be broken. For example, table
`USERS` would become `U_s_e_r_s` which is odd. Check parameter details here: [QuoteIdentifiers](http://book.cakephp.org/3.0/en/orm/database-basics.html#configuration)

in your app.php configuration, under your database Driver config:
```
'quoteIdentifiers' => true,
```

The column names are transformed to lowercase to prevent issues with casing when queries are sent to the Oracle Database.

### Tables with autoincrement fields
- sequence convention: `SEQ_{$tableName}`
- trigger (trigger body displayed in trigger ticket)

## Helper functions
You'll often need this functions, for example
 if you want to use the parameter to sort with.

Provided utility functions:

`Portal89\OracleDriver\Database\FunctionsBuilder`
`FunctionsBuilder::toChar(string)`
`FunctionsBuilder::toCharWithFormat(string)`
`FunctionsBuilder::toDate(string, string)`

## Current plugin limitations
### Building join conditions
All fields should be escaped while building join conditions.
You can use IdentiferExpression or the
[Cake\Database\Expression\QueryExpression::equalFields](http://api.cakephp.org/3.2/class-Cake.Database.Expression.QueryExpression.html#_equalFields)
method to escape them. Example:
```php
$q = $this->Posts->find();
$q->join([
    'table' => 'authors',
    'conditions' => $q->newExpr()->equalFields('Post.author_id', 'authors.id')
    ]);
```

## Notes on performance
We disabled the lazy loading at the Driver level because Oracle rowCount method in
Statement class does not return count of the records returned by the statement. Sometimes 0 is
returned or prefetch amount of rows, in either case this was not usable for the lazy loading.

We didn't find a workaround yet for lazy fetching in Oracle Database 11g, so it's disabled.

## CLOBs at the end of tables
If CLOB fields present, you should:
- Recommended: create the CLOB fields at the end of the table
- Not recommended: manually fix the schema of the table.  It's not the best approach
because you'll need to maintain the schema on changes done to the table
in migrations, etc.

