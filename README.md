# Garlic GraphQL bundle

This bundle allow to communicate microservices via graphql to each other.
It should be installed on both endpoints for proper message encode/decode flow.

This bundle based on [youshido-php/GraphQLBundle](https://github.com/youshido-php/GraphQLBundle), so special thanks to this guys for the excellent work! We've just made a couple updates ;)

## Configuration

There are necessary things make this bundle works:

### Add bundle to the Symfony project

```bash
composer require garlic/graphql
```

### Initialize GraphQL schema(create schema, query and mutation fields)

```bash
bin/console maker:graphql:init
```

### Create graphql type (command able to get fields from existing Entity)

```bash
bin/console maker:graphql:type
```

The command suggest you to create full CRUD mutations and queries, just type "y" to do so. 
There would be created CRUD-classes with related functionality.
Last thing you need to make this bundle working is to update your service.yaml
```yaml
# Make graphql services public
App\Service\GraphQL\:
    resource: '../src/Service/GraphQL/*'
    public: true
```

### Make your first graphql query or mutation

```bash
bin/console maker:graphql:query
```

Now you can review and update newly created files! 

It's time to run your first query! Try to send your query to **mydomain.com/graphql**

## Usage
### Example steps to use bundle after init
1. Create Entity (for example Apartments)
2. Create GraphQL type by using command above (for example name it Apartment), type "y" to make CRUD mutations automatically.
3. Try to execute a query
```
{
  ApartmentFind(id:1){
    id
  }
}

```

### Using related types
1. Let's create new Entity (for example Address) and connect it to Apartments by using many to one relation.
2. Create GraphQL type "Address" similar to step one
3. Add newly created type to Apartment type as `new Address()`
4. Try to find Apartment with address fields (for example id)
```
{
  ApartmentFind(id:1){
    id
    address {
        id
    }
  }
}
``` 

or directly by Address "where" query (for example id)

```
{
  ApartmentFind(address:{id:1}){
    id
    address {
        id
    }
  }
}
```
## GraphiQL extension
You can write queries in interactive editor with integrated documentation about schemas, queries and mutation. 
To run this editor just run graphiql extension. Type to access to extension - **mydomain.com/graphql/explorer** 
Extension is accessible only in development mode of Symfony application.

## Enjoy!