# Using Methods and Cursors

## configuration

Add to bootstrap.php
```php
	Plugin::load('CakeDC/OracleDriver', ['bootstrap' => true]);
```

Add to app.php to Cache section
```php
        '_cake_method_' => [
            'className' => 'File',
            'prefix' => 'myapp_cake_method_',
            'path' => CACHE . 'models/',
            'serialize' => true,
            'duration' => '+2 minutes',
        ],
```

Provides access to:
- Stored procedures or functions
- Stored procedures or function in packages

Stored procedures could return different types: we currently support basic
types and cursors.

**Cursors** are references to prepared statements in the database. Once created you could use them to execute them
later on. It also affects performance because while you work on the cursor the data is being populated in the
database.

## New MethodRegistry and Request classes
Plugin provides an abstraction to access Oracle database methods similar to the Table abstraction:
- MethodRegistry is similar to TableRegistry
- Method is similar to Table (allow to parse parameters types from all_parameters view and prepare binding automatically)
  - `initializeSchema` allows to extend parameters
  - `newRequest` returns request
  - `execute` executes request.
- Request - similar to Entity with additional features
  - `set` or [$name]= used to set IN params values
  - `get` or [$name] used to read OUT params value after call.
  - `result` returns function result
  - `fetchCursor` returns cursors (accept Entity class and ResultSet fields schema)

## New DebugKit tab
We added a new tab to track method requests sent to the Oracle Database.

Add to bootstrap.php into debug kit initialization section.

```php
    Configure::write('DebugKit.panels', ['CakeDC/OracleDriver.MethodLog']);
```

### Schema, Package and name

The syntax to access function names is:

- `function_name`
- `procedure_name`
- `package.function_name`
- `package.procedure_name`
- `schema.package.function_name`
- `schema.package.procedure_name`

## Examples
### Run a stored procedure

Let's say we have the following "calc" package with a sum function defined in Oracle Database:

```sql
create or replace package calc is
    function sum(a number, b number) return number;
end calc;
```

We could call this function from CakePHP using the following code:

```php
$method = \Portal89\OracleDriver\ORM\MethodRegistry::get('CalcSum', ['method' => 'CALC.SUM']);
$request = $method->newRequest(['A' => 5, 'B' => 10]);
// $request->isNew() would return true at this point
$method->execute($request);
// $request->isNew() would return false at this point, as it's executed already
echo $request->result(); // 15
```
