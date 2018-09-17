# Garlic GraphQL bundle

This bundle allow microservices send to each other graphql queries 
For correct usage the Bundle must be installed on both services (current and target)

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

The command suggest you to create full CRUD mutations and queries, just type "y" to do so when command interact this question to you. After the command will 
create some classes and CRUD service with all the functionality. The one thing you have to this functionality will work, just add next rows to your service.yaml
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

Now you can review and update just created files! 

It's time to run your first query! Try to send your query to **mydomain.com/graphql**

## Usage
### Example steps to use bundle after init
1. Create Entity (for example Apartments)
2. Create GrapphQL type by using command above (for example name it Apartment). Just try to make CRUD mutations it is very useful command.
3. Try to make a query
```
{
  ApartmentFind(id:1){
    id
  }
}

```

### Using included types
1. Let's create new Entity (for example Address) and connect it to Apartments by using many to one relation.
2. Than create GraphQL type by steps enplaned in step one
3. Add just created type to Apartment type as new Address()
4. Try to find with Apartment field (for example id)
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

or by Address field (for example id)

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
