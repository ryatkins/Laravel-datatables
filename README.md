# Datatables that supports pagination and searching in relations

[![Latest Version on Packagist](https://img.shields.io/packagist/v/acfbentveld/laravel-datatables.svg?style=flat-square)](https://packagist.org/packages/acfbentveld/laravel-datatables)
[![Total Downloads](https://img.shields.io/packagist/dt/acfbentveld/laravel-datatables.svg?style=flat-square)](https://packagist.org/packages/acfbentveld/laravel-datatables)

This repo contains a Datatable that can render a filterable and sortable table. It aims to be very lightweight and easy to use. It has support for retrieving data asynchronously, pagination and recursive searching in relations.

## Installation

You can install the package via composer:

```bash
composer require acfbentveld/laravel-datatables
```

## Usage
### php
This package supports 2 methods for building the json data, the first one (not recommended!) is the `collect` method. For this method you pass the retrieved data as a collection to the method. This works great for low amounts of records. 
``` php
$users = DataTables::collect(User::all())->get(); //please don't use this anymore when installing v2.*
```
The second method (**Recommended**) is the `model` method. This is just great at everything. The datatables class creates a new model instance runs a query on it and done. It also has a lot more options and makes only one request to your database. This is fast for a lot and a low amount of records. Just use this one!
``` php
$users = DataTables::model(new User)->get();
```
### javascript | jquery
You don't have to specify a different url. The package will detect if the datatable makes connection
``` javascript
 $(document).ready(function() {
    //thats all
    $('#datatable').DataTable({
        "processing": true, //process it
        "serverSide": true, //make it server side
        "ajax": location.href //Just get the data from the same url. The package will handle it all
    });

} );
```

### html
At last make a html table. No need to tell you how that works.
``` html
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs/dt-1.10.16/datatables.min.css"/>

<table id="datatable" class="table">
    <thead>
        <tr>
            <th>#</th>
            <th>name</th>
        </tr>
    </thead>
</table>

<script type="text/javascript" src="https://cdn.datatables.net/v/bs/dt-1.10.16/datatables.min.js"></script>
```

### Important!!!
The datatable always expects strict data. If you have 2 table heads (th) in your table. Don't return an collection with 5 keys! Or just use the selecter down below.

When calling model like `User::all()` it returns at least a few keys like id,name,email,password etc...
Datatable needs strict data to work properly. 

``` php
$model = User::select('id', 'name')->get(); // you ahve to selecvt them before passing them to the datatable
$users = DataTables::collect($model)->get(); // will return only the keys id and name
```
Recommended
``` php
$users = DataTables::model(new User)->select('id', 'name')->get(); // Using the model method you can use the selecter
```

## Options

##### toSql
```php
$users = DataTables::model(new User)->select('id', 'name')->toSql(); // dump the sql query line
//select * from `users` select `id`, `name` .... 
```

##### where
Just the regular where method. Use it to filter the model
```php
 DataTables::model(new User)->whereName('John Snow')->whereEmail('knows@nothing.com')->get();

 DataTables::model(new User)->where('name', 'John Snow')->where('email', 'knows@nothing.com')->get();
```
##### whereHas
Just the regular whereHas method. Use it to filter the model
```php
 DataTables::model(new User)->whereHas('roles')->get();
 DataTables::model(new User)->whereHas('roles', function($query){
   $query->whereName('admin');
 })->get();
```

##### orWhereHas
Just the regular orWhereHas method. Use it to filter the model
```php
 DataTables::model(new User)->whereHas('roles')->orWhereHas('permissions')->get();
```
##### whereIn
Just the regular whereIn method. Use it to filter the model
```php
 DataTables::model(new Role)->whereIn('guard', ['admin', 'employee'])->get();
```

##### whereYear
Just the regular whereYear method. Use it to filter the model
```php
 DataTables::model(new User)->whereYear('created_at', '2018')->get();
```
##### with
Just the regular with method. Selects the relations with it
```php
 DataTables::model(new User)->with('roles', 'permissions')->get();
```
##### encrypt
Sometimes you want to encrypt a specific value. Like the ID in a model collection
``` php
DataTables::model(new User)->encrypt('id')->get(); // will encrypt every ID in the model using the default encrypt() method
```

##### select
THe select method selects only the given keys
``` php
 DataTables::model(new User)->select('name', 'email')->get(); //returns name and email
```

##### exclude
The exclude method excludes keys from the response data
``` php
 DataTables::model(new User)->exclude('id', 'email')->get(); //Selects all exept the given keys
```

##### Scopes
When trying to access scopes from your model, you can use the addScope method to add scopes to your collection.
```php
    DataTables::model(new User)->addScope('active')->get(); //Access the scopeActive on the users model
```
Adding data to the scope
```php
    DataTables::model(new User)->addScope('formatDate', 'd-m-Y')->get(); //Access the scopeFormatDate with data
```

##### with trashed
To retrieve the soft delete items from your database, you can use the default `withTrashed` method.
This will only work on models that have included the soft delete trait.
```php
    DataTables::model(new User)->withTrashed()->get(); //Retrieve the soft deleted items.
```

##### datatable options
``` javascript
 $(document).ready(function() {
    //thats all
    $('#datatable').DataTable({
        "processing": true, //process it
        "serverSide": true, //make it server side
        "ajax": location.href, //Just get the data from the same url. The package will handle it all
        "columns": [ //define the keys
                { "data": "id" },
                { "data": "name" },
            ],
        //if you want to use relations or chage the behavior of a cell
        "columnDefs": [
                {
                    "render": function ( data, type, row ) {
                        //for relations just return the relation key
                        return data.name;
                    },
                    "targets": [0] //the targets, starts at 0
                },
            ],
    });

} );
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email wim@acfbentveld.nl instead of using the issue tracker.

## Postcardware

You're free to use this package, but if it makes it to your production environment we highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using.

Our address is: ACF Bentveld, Ecu 2 8305 BA, Emmeloord, Netherlands.

## Credits

- [Wim Pruiksma](https://github.com/wimurk)
- [Amando Vledder](https://github.com/AmandoVledder)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
