# Laravel Request Query

## Query Format

### Sorting

```
{
    sort: FIELD,
    order: ASC|DESC
}
```

OR

```
{
    sort: {
        FIELD1: ASC|DESC,
        FIELD2: ASC|DESC,
        ...
    }
}
```


### Filtration

```
{
    filter: {
        FIELD1: VALUE1,
        FIELD2: VALUE2,
        FIELD3: {
            operator: '>',
            value: VALUE3
        }
    }
}
```

### Pagination

```
{
    limit: NUMBER,
    page: NUMBER,
}
```

* `limit` default to 10
* `page` default to 1

### Grouping

```
{
    group_by: FIELD,
    group_map: {
        1: "enabled",
        0: "disabled"
    }
}
```

* `group_map` is optional, but if provided, only mapped values will appear in the result.

## Usage

1\. Using `RequestQuery` class

```php
<?php

use ElMag\RQ\RequestQuery;

class SomeController
{
    public function index(Request $request)
    {
        $query = (new SomeModel)->newQuery();
        
        $rq = new RequestQuery($request, $query);
        
        $rq->setSortFields('*'); // This is the default
        // OR: ['*']
        // OR: ['age', 'salary', ...]
        
        $rq->setFilterFields(/* Same as `setSortFields` */);
        
        $rq->handleSort()               // If needed
            ->handleFilter()            // If needed
            ->handlePagination();       // If needed

        $results = $query->get();
        $rq->handleGroupBy($results);   // If Needed
    }
    ...
```

2\. Using `FormRequestWithQuery` pre-prepared form request

```php
<?php

use ElMag\RQ\FormRequestWithQuery;

class SomeController
{
    public function index(FormRequestWithQuery $request)
    {
        $query = (new SomeModel)->newQuery();
        
        $request->setSortFields('*'); // This is the default
        // OR: ['*']
        // OR: ['age', 'salary', ...]
        
        $request->setFilterFields(/* Same as `setSortFields` */);
        
        $request->setQuery($query)
            ->handleSort()                  // If needed
            ->handleFilter()                // If needed
            ->handlePagination();           // If needed

        $results = $query->get();
        $request->handleGroupBy($results);  // If Needed
    }
    ...
```

3\. Using `FormRequestQuery` trait in custom form request

```php
<?php

class SomeController
{
    public function index(CustomFormRequest $request)
    {
        $query = (new SomeModel)->newQuery();

        $request->setQuery($query)
            ->handleSort()                  // If needed
            ->handleFilter()                // If needed
            ->handlePagination();           // If needed

        $results = $query->get();
        $request->handleGroupBy($results);  // If Needed
    }
    ...
```

```php
<?php

use ElMag\RQ\FormRequestQuery;

class CustomFormRequest extends FormRequest
{
    use FormRequestQuery;
    
    protected $sortFields = '*';
    // OR: ['*']
    // OR: ['age', 'salary', ...]
    
    protected $filterFields = [/* Same as `sortFields` */];
}
```