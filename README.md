# Datatables that supports pagination and recursive searching in relations

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
The package supports 2 ways to process the data. The firts one is the collect method. YOu send the collection the model returns to the package. 
``` php
$users = DataTables::collect(User::all())->get();
```
The second method (recommended) is the model method. Create a new model instance. This will it is easier to manipulate the collection
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
    <tbody>
        <!--You dont have to put any data in here. The datatable will fill it-->   
    </tbody>
</table>

<script type="text/javascript" src="https://cdn.datatables.net/v/bs/dt-1.10.16/datatables.min.js"></script>
```

### Important!!!
The datatable always expects strict data. If you have 2 table heads (th) in your table. Don't return an collection with 5 keys! Or just use the selecter down below.

When calling model like `User::all()` it returns at least a few keys like id,name,email,password etc...
Datatable needs strict data to work properly. 

``` php
$model = User::select(['id', 'name'])->get(); // you ahve to selecvt them before passing them to the datatable
$users = DataTables::collect($model)->get(); // will return only the keys id and name
```
Recommended
``` php
$users = DataTables::model(new User)->select(['id', 'name'])->get(); // Using the model method you can use the selecter
```

## Options

- where
    * Just the regular where method. Use it to filter the model
```php
 DataTables::model(new User)->where('name', 'John Snow')->where('email', 'knows@nothing.com')->get();
```
- encrypt
    * Sometimes you want to encrypt a specif value. Like the ID of a model.
``` php
DataTables::model(new User)->encrypt(['id'])->get(); // will return all items with an encrypted value
```

- noSelect
    * The noSelect method return everything except the given keys
``` php
 DataTables::model(new User)->noSelect(['id'])->get(); //removes the id key from the collection
```
- withKeys
    * By default the package returns the collection without it's keys. 
``` php
DataTables::model(new User)->withKeys(false)->get();
```
above will return `["foo", "bar@mail.com"]`

```php
DataTables::model(new User)->withKeys(true)->get();
```
above will return `above will return [name => "foo", email => "bar@mail.com"]`

- datatable options
    * when using withKeys set to true. You have to define the keys returned to the datatable.
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
